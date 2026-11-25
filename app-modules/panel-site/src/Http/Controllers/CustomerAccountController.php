<?php

declare(strict_types=1);

namespace Vevey\PanelSite\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\BrasilApiService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Vevey\Catalog\Models\Category;
use Vevey\Customers\Models\Customer;
use Vevey\Customers\Models\CustomerAddress;
use Vevey\Orders\Actions\UpdateOrderStatus;
use Vevey\Orders\Enums\FulfillmentStatus;
use Vevey\Orders\Enums\PaymentStatus;
use Vevey\Orders\Models\Order;

final class CustomerAccountController extends Controller
{
    /** @var list<string> */
    private const array BRAZILIAN_STATES = [
        'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO',
        'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI',
        'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO',
    ];
    public function index(): View
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customers')->user();
        $customer->load(['activeIdentities', 'addresses']);

        // Política de dono: exibe estritamente os pedidos do cliente autenticado
        $orders = Order::query()
            ->where('customer_id', $customer->id)
            ->with(['items', 'adjustments'])
            ->latest()
            ->get();

        $navCategories = Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('position')])
            ->orderBy('position')
            ->get();

        return view('store.account', [
            'navCategories' => $navCategories,
            'customer' => $customer,
            'orders' => $orders,
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customers')->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'cpf' => ['nullable', 'string', 'max:14'],
            'password' => ['nullable', 'string', 'min:8', 'max:128', 'confirmed'],
        ]);

        $formattedCpf = $customer->cpf;
        if (! empty($validated['cpf'])) {
            if (! BrasilApiService::isValidCpf((string) $validated['cpf'])) {
                throw ValidationException::withMessages([
                    'cpf' => 'CPF inválido. Verifique os dígitos informados.',
                ]);
            }
            $formattedCpf = BrasilApiService::formatCpf((string) $validated['cpf']);
        } elseif ($request->has('cpf') && mb_trim((string) $request->input('cpf')) === '') {
            $formattedCpf = null;
        }

        $updates = [
            'name' => mb_trim((string) $validated['name']),
            'cpf' => $formattedCpf,
        ];

        if (! empty($validated['password'])) {
            $updates['password'] = (string) $validated['password'];
        }

        $customer->update($updates);

        return redirect()->route('store.account')->with('status', 'Dados da conta atualizados com sucesso.');
    }

    public function cancelOrder(string $orderId, UpdateOrderStatus $updater): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customers')->user();

        $order = Order::query()
            ->where('id', $orderId)
            ->where('customer_id', $customer->id)
            ->firstOrFail();

        if (
            $order->payment_status !== PaymentStatus::Pending
            || $order->fulfillment_status !== FulfillmentStatus::Pending
        ) {
            return redirect()->route('store.account')
                ->withErrors(['order' => 'Apenas pedidos com pagamento pendente podem ser cancelados pelo site.']);
        }

        $updater->handle(
            order: $order,
            paymentStatus: PaymentStatus::Failed,
            fulfillmentStatus: FulfillmentStatus::Cancelled,
            note: 'Pedido cancelado pelo cliente na Área do Cliente (estoque liberado).',
        );

        return redirect()->route('store.account')
            ->with('status', "Pedido {$order->number} cancelado e reserva de estoque liberada.");
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customers')->user();

        if ($request->filled('state')) {
            $request->merge(['state' => mb_strtoupper(mb_trim((string) $request->input('state')))]);
        }

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:60'],
            'cep' => ['required', 'string', 'regex:/^\d{5}-?\d{3}$/'],
            'street' => ['required', 'string', 'max:160'],
            'number' => ['required', 'string', 'max:30'],
            'complement' => ['nullable', 'string', 'max:120'],
            'neighborhood' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', Rule::in(self::BRAZILIAN_STATES)],
        ]);

        $isFirst = ! $customer->addresses()->exists();

        CustomerAddress::query()->create([
            'customer_id' => $customer->id,
            'label' => (string) $validated['label'],
            'cep' => (string) $validated['cep'],
            'street' => (string) $validated['street'],
            'number' => (string) $validated['number'],
            'complement' => isset($validated['complement']) ? (string) $validated['complement'] : null,
            'neighborhood' => (string) $validated['neighborhood'],
            'city' => (string) $validated['city'],
            'state' => mb_strtoupper((string) $validated['state']),
            'is_default' => $isFirst,
        ]);

        return redirect()->route('store.account')->with('status', 'Endereço salvo com sucesso.');
    }

    public function destroyAddress(string $addressId): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customers')->user();

        CustomerAddress::query()
            ->where('id', $addressId)
            ->where('customer_id', $customer->id)
            ->delete();

        return redirect()->route('store.account')->with('status', 'Endereço removido.');
    }
}
