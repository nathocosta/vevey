<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

        $existingWithPassword = Customer::query()
            ->whereRaw('lower(email) = ?', [$email])
            ->whereNotNull('password')
            ->exists();

        if ($existingWithPassword) {
            throw ValidationException::withMessages([
                'email' => 'Já existe uma conta cadastrada com este e-mail. Faça login para continuar.',
            ]);
        }

        // ADR 7: ativa conta de cliente já existente (ex.: compra anterior como visitante/WhatsApp) ou cria novo
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

        Auth::guard('customers')->login($customer);
        $request->session()->regenerate();

        return redirect()->route('store.account');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customers')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('store.home');
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
