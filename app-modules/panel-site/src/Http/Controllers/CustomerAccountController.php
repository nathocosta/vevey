<?php

declare(strict_types=1);

namespace Vevey\PanelSite\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Vevey\Catalog\Models\Category;
use Vevey\Customers\Models\Customer;
use Vevey\Customers\Models\CustomerAddress;
use Vevey\Orders\Models\Order;

final class CustomerAccountController extends Controller
{
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

    public function storeAddress(Request $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customers')->user();

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:60'],
            'cep' => ['required', 'string', 'regex:/^\d{5}-?\d{3}$/'],
            'street' => ['required', 'string', 'max:160'],
            'number' => ['required', 'string', 'max:30'],
            'complement' => ['nullable', 'string', 'max:120'],
            'neighborhood' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'size:2'],
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
