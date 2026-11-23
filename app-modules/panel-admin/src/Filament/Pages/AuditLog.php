<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Pages;

use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Override;
use Vevey\Inventory\Models\StockMovement;
use Vevey\Orders\Enums\FulfillmentStatus;
use Vevey\Orders\Enums\PaymentStatus;
use Vevey\Orders\Models\OrderStatusHistory;
use Vevey\PanelAdmin\Filament\Resources\Orders\OrderResource;
use Vevey\PanelAdmin\Filament\Resources\StockLevels\StockLevelResource;
use Vevey\Permissions\Enums\Roles;

final class AuditLog extends Page
{
    protected static ?string $slug = 'audit-log';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMagnifyingGlass;

    protected static ?int $navigationSort = 90;

    protected string $view = 'panel-admin::filament.pages.audit-log';

    public string $module = 'all';

    public string $userId = '';

    public string $search = '';

    public int $limit = 60;

    #[Override]
    public static function getNavigationLabel(): string
    {
        return 'Logs de auditoria';
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return 'Auditoria';
    }

    #[Override]
    public function getTitle(): string
    {
        return 'Logs de auditoria — Estoque e Pedidos';
    }

    #[Override]
    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user instanceof User
            && $user->hasAnyRole([Roles::SuperAdmin->value, Roles::Admin->value]);
    }

    /**
     * @return list<array{id: string, name: string, email: string}>
     */
    public function getStaffUsersProperty(): array
    {
        return User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(static fn (User $u): array => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
            ])
            ->all();
    }

    /**
     * @return list<array{
     *     id: string,
     *     timestamp: int,
     *     when: string,
     *     module: string,
     *     module_label: string,
     *     badge_color: string,
     *     actor: string,
     *     actor_email: string|null,
     *     summary: string,
     *     detail: string|null,
     *     url: string|null,
     *     url_label: string|null,
     * }>
     */
    public function getEventsProperty(): array
    {
        $events = [];
        $term = mb_strtolower(mb_trim($this->search));

        if ($this->module === 'all' || $this->module === 'inventory') {
            $stockQuery = StockMovement::query()
                ->with(['product', 'user'])
                ->latest('created_at')
                ->limit($this->limit);

            if ($this->userId === 'system') {
                $stockQuery->whereNull('user_id');
            } elseif ($this->userId !== '') {
                $stockQuery->where('user_id', $this->userId);
            }

            foreach ($stockQuery->get() as $movement) {
                $productName = $movement->product?->name ?? 'Produto';
                $sku = $movement->product?->sku ?? '—';
                $signedQty = $movement->qty > 0 ? "+{$movement->qty}" : (string) $movement->qty;
                $summary = "{$movement->reason->getLabel()} ({$signedQty} un.) em {$productName} [{$sku}] · saldo após: {$movement->balance_after}";

                if ($term !== '' && ! str_contains(mb_strtolower($summary . ' ' . ($movement->note ?? '')), $term)) {
                    continue;
                }

                $events[] = [
                    'id' => 'inv-' . $movement->id,
                    'timestamp' => $movement->created_at->getTimestamp(),
                    'when' => $movement->created_at->format('d/m/Y H:i:s'),
                    'module' => 'inventory',
                    'module_label' => 'Estoque',
                    'badge_color' => $movement->qty > 0 ? 'success' : 'warning',
                    'actor' => $movement->user?->name ?? 'Sistema / Checkout',
                    'actor_email' => $movement->user?->email,
                    'summary' => $summary,
                    'detail' => $movement->note,
                    'url' => StockLevelResource::getUrl('index'),
                    'url_label' => 'Ver estoque',
                ];
            }
        }

        if ($this->module === 'all' || $this->module === 'orders') {
            $orderQuery = OrderStatusHistory::query()
                ->with(['order', 'user'])
                ->latest('created_at')
                ->limit($this->limit);

            if ($this->userId === 'system') {
                $orderQuery->whereNull('user_id');
            } elseif ($this->userId !== '') {
                $orderQuery->where('user_id', $this->userId);
            }

            foreach ($orderQuery->get() as $history) {
                $orderNumber = $history->order?->number ?? 'Pedido';
                $fieldLabel = $history->field === 'payment_status' ? 'pagamento' : 'entrega';
                $fromLabel = self::translateStatus($history->field, $history->from_status);
                $toLabel = self::translateStatus($history->field, $history->to_status);

                $transition = $fromLabel !== null
                    ? "{$fromLabel} → {$toLabel}"
                    : $toLabel;

                $summary = "Pedido {$orderNumber} ({$fieldLabel}): {$transition}";

                if ($term !== '' && ! str_contains(mb_strtolower($summary . ' ' . ($history->note ?? '')), $term)) {
                    continue;
                }

                $events[] = [
                    'id' => 'ord-' . $history->id,
                    'timestamp' => $history->created_at->getTimestamp(),
                    'when' => $history->created_at->format('d/m/Y H:i:s'),
                    'module' => 'orders',
                    'module_label' => 'Pedidos',
                    'badge_color' => 'info',
                    'actor' => $history->user?->name ?? 'Cliente / Site',
                    'actor_email' => $history->user?->email,
                    'summary' => $summary,
                    'detail' => $history->note,
                    'url' => $history->order ? OrderResource::getUrl('view', ['record' => $history->order->id]) : null,
                    'url_label' => $history->order ? 'Abrir pedido' : null,
                ];
            }
        }

        usort($events, static fn (array $a, array $b): int => $b['timestamp'] <=> $a['timestamp']);

        return array_slice($events, 0, $this->limit);
    }

    public function loadMore(): void
    {
        $this->limit += 50;
    }

    public function resetFilters(): void
    {
        $this->module = 'all';
        $this->userId = '';
        $this->search = '';
        $this->limit = 60;
    }

    private static function translateStatus(string $field, ?string $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        if ($field === 'payment_status') {
            return PaymentStatus::tryFrom($raw)?->getLabel() ?? $raw;
        }

        return FulfillmentStatus::tryFrom($raw)?->getLabel() ?? $raw;
    }
}
