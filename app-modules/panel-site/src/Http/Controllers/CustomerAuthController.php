<?php

declare(strict_types=1);

namespace Vevey\PanelSite\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;
use Vevey\Catalog\Models\Category;
use Vevey\Customers\Actions\FindOrCreateCustomerByContact;
use Vevey\Customers\Enums\IdentitySource;
use Vevey\Customers\Models\Customer;

final class CustomerAuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('customers')->check()) {
            return redirect()->route('store.account');
        }

        return view('store.auth.login', [
            'navCategories' => $this->navCategories(),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // RNF007: rate limit de 5 tentativas por e-mail + IP
        $throttleKey = 'customer-login:' . Str::lower((string) $credentials['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Muitas tentativas de login. Tente novamente em {$seconds} segundos.",
            ]);
        }

        $email = mb_strtolower(mb_trim((string) $credentials['email']));

        if (! Auth::guard('customers')->attempt(['email' => $email, 'password' => $credentials['password']], $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 300);

            throw ValidationException::withMessages([
                'email' => 'E-mail ou senha inválidos.',
            ]);
        }

        /** @var Customer $customer */
        $customer = Auth::guard('customers')->user();

        // Se a conta ainda não concluiu a verificação em 2 etapas, redireciona para confirmar o código
        if ($customer->email_verified_at === null) {
            Auth::guard('customers')->logout();
            $this->issueVerificationCode($customer, $request);

            return redirect()->route('store.verify');
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect()->intended(route('store.account'));
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::guard('customers')->check()) {
            return redirect()->route('store.account');
        }

        return view('store.auth.register', [
            'navCategories' => $this->navCategories(),
        ]);
    }

    public function register(Request $request, FindOrCreateCustomerByContact $findOrCreate): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['required', 'email', 'max:120'],
            'phone' => ['required', 'string', 'min:10', 'max:20'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $email = mb_strtolower(mb_trim((string) $validated['email']));

        $existingVerified = Customer::query()
            ->whereRaw('lower(email) = ?', [$email])
            ->whereNotNull('password')
            ->whereNotNull('email_verified_at')
            ->exists();

        if ($existingVerified) {
            throw ValidationException::withMessages([
                'email' => 'Já existe uma conta cadastrada com este e-mail. Faça login para continuar.',
            ]);
        }

        // ADR 7: ativa conta de cliente já existente (visitante/WhatsApp) ou cria novo
        $customerData = $findOrCreate->handle(
            name: (string) $validated['name'],
            email: $email,
            phone: (string) $validated['phone'],
            source: IdentitySource::Web,
        );

        /** @var Customer $customer */
        $customer = Customer::query()->findOrFail($customerData->id);
        $customer->update([
            'name' => mb_trim((string) $validated['name']),
            'email' => $email,
            'password' => (string) $validated['password'],
        ]);

        $this->issueVerificationCode($customer, $request);

        return redirect()->route('store.verify')
            ->with('status', 'Enviamos um código de 6 dígitos para confirmar sua conta.');
    }

    public function showVerify(Request $request): View|RedirectResponse
    {
        $customerId = $request->session()->get('pending_verification_customer_id');
        if (! is_string($customerId)) {
            return redirect()->route('store.login');
        }

        $customer = Customer::query()->find($customerId);
        if (! $customer instanceof Customer) {
            return redirect()->route('store.login');
        }

        $isDemoMailer = in_array(config('mail.default'), ['log', 'array'], true);

        return view('store.auth.verify', [
            'navCategories' => $this->navCategories(),
            'email' => $customer->email,
            'demoCode' => $isDemoMailer ? $customer->verification_code : null,
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $customerId = $request->session()->get('pending_verification_customer_id');
        if (! is_string($customerId)) {
            return redirect()->route('store.login');
        }

        $customer = Customer::query()->find($customerId);
        if (! $customer instanceof Customer) {
            return redirect()->route('store.login');
        }

        if (
            $customer->verification_code !== (string) $validated['code']
            || $customer->verification_expires_at === null
            || now()->gt($customer->verification_expires_at)
        ) {
            throw ValidationException::withMessages([
                'code' => 'Código inválido ou expirado. Solicite um novo código abaixo.',
            ]);
        }

        $customer->update([
            'email_verified_at' => now(),
            'verification_code' => null,
            'verification_expires_at' => null,
        ]);

        $request->session()->forget('pending_verification_customer_id');
        Auth::guard('customers')->login($customer);
        $request->session()->regenerate();

        return redirect()->route('store.account')
            ->with('status', 'Conta verificada com sucesso! Bem-vindo(a) à Vevey Doces.');
    }

    public function resendVerification(Request $request): RedirectResponse
    {
        $customerId = $request->session()->get('pending_verification_customer_id');
        if (! is_string($customerId)) {
            return redirect()->route('store.login');
        }

        $customer = Customer::query()->find($customerId);
        if ($customer instanceof Customer) {
            $this->issueVerificationCode($customer, $request);
        }

        return redirect()->route('store.verify')
            ->with('status', 'Novo código de verificação gerado.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customers')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('store.home');
    }

    private function issueVerificationCode(Customer $customer, Request $request): void
    {
        $code = (string) random_int(100000, 999999);

        $customer->update([
            'verification_code' => $code,
            'verification_expires_at' => now()->addMinutes(15),
        ]);

        $request->session()->put('pending_verification_customer_id', $customer->id);

        if ($customer->email !== null) {
            try {
                Mail::raw(
                    "Olá, {$customer->name}!\n\nSeu código de verificação Vevey Doces é: {$code}\n\nEste código expira em 15 minutos.",
                    static function ($message) use ($customer): void {
                        $message->to((string) $customer->email)
                            ->subject('Vevey Doces — Código de verificação da sua conta');
                    },
                );
            } catch (Throwable $e) {
                Log::warning('Falha ao enviar e-mail de verificação 2FA: ' . $e->getMessage());
            }
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, Category>
     */
    private function navCategories(): \Illuminate\Support\Collection
    {
        return Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('position')])
            ->orderBy('position')
            ->get();
    }
}
