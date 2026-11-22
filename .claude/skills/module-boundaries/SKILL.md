---
name: module-boundaries
description: How Vevey modules talk to each other. Use whenever code in one `app-modules` module imports, calls, listens to or relates to another module; when creating an Action, a DTO (`…Data`), a domain event or a listener; when adding a module to the layer map; or when deciding between a direct call and an event.
---

# Module boundaries

The database is relational; the PHP code has a direction. Cross-module foreign keys are normal. Imports only point **down** the layer map. Rationale lives in `docs/MODELAGEM_LARAVEL_ADR.md`, ADR 8.

## Layer map

Once it exists, `$layers` in `tests/Arch/ModuleDependenciesTest.php` is the source of truth; keep this table and that array in sync.

| Layer | Modules | May import |
|---|---|---|
| Interface | `panel-admin`, `panel-site` | any module, including models |
| Integration | `integration-*` | the domain module whose contract it implements |
| Orchestrator | `orders` | `catalog`, `customers`, `inventory`, `payments` |
| Base | `catalog`, `customers`, `inventory`, `payments` | no other domain module |
| Shared | `permissions`, `app/` | used by everyone; `app/` imports no module |

A new module gets a row here and an entry in `$layers` in the same change.

## Choosing the channel

Work through these for every cross-module touch:

1. **Place both modules on the map.** The higher one is the caller; the lower one owns the Action, the DTO and the event.
2. **Pick the channel:**
   - The caller needs the answer or the failure *now* (reserve stock, create a charge and get the Pix QR code, read products for the order snapshot) → **command**: inject the lower module's Action, pass a `…Data`, receive a `…Data` or a scalar.
   - The lower module announces something already true (`PaymentConfirmed`, `StockBelowReorderPoint`) → **domain event**: the lower module dispatches it; the higher module owns the listener.
   - An external provider (Pix, WhatsApp) → **contract**: interface in the domain module, implementation and `bind()` in the `integration-*` module's ServiceProvider.
3. **Keep each pair one-way.** Commands go down, events go up. A listener imports its event class, so listening is also a dependency: a lower module never listens to a higher module's event. When both directions seem needed, the higher module reacts to the event and makes the follow-up command itself (orders hears `PaymentConfirmed`, then calls `FulfillReservation` on inventory).
4. **Cross with IDs and values.** What crosses a domain boundary is a `…Data`, an ID (`string` UUID), an enum, `Money` or `CarbonImmutable`. Eloquent models stay inside their own module.

## Foreign keys and relations

- Migrations reference other modules' tables freely: `foreignIdFor(Order::class)` or `foreignUuid('order_id')->constrained('orders')`. The referenced table's migration needs an earlier timestamp.
- Domain models declare Eloquent relations only to models of their own module. The column (`order_id`) is enough for the domain.
- Panels need cross-module relations for display. The panel registers them in its own ServiceProvider:

```php
Order::resolveRelationUsing('payments', fn (Order $order) => $order->hasMany(Payment::class));
```

## DTO pattern

- Folder `src/DTOs/`, namespace `Vevey\{Module}\DTOs`, owned by the module that exposes the Action.
- Suffix `Data`, always. Action input `{Verb}{Thing}Data` (`ReserveStockData`); read output `{Thing}Data` (`ProductData`); list item `{Thing}ItemData`.
- `final readonly class`, constructor promotion, built with named arguments.
- Fields: scalars, enums, `Money`, `CarbonImmutable`, other `…Data`. Lists typed in PHPDoc (`@param list<StockItemData> $items`).
- Data only: no queries, no business rules. A static `fromModel()` is allowed for a model of the same module.

```php
namespace Vevey\Inventory\DTOs;

final readonly class ReserveStockData
{
    /**
     * @param  list<StockItemData>  $items
     */
    public function __construct(
        public string $orderId,
        public array $items,
        public CarbonImmutable $expiresAt,
    ) {}
}
```

## Domain event pattern

- Folder `src/Events/` of the module where the fact happens. Name is a past-tense fact with no suffix: `PaymentConfirmed`, `OrderCancelled`.
- `final readonly class`, `use Dispatchable`, `implements ShouldDispatchAfterCommit` so a rolled-back transaction never announces anything.
- Payload: IDs and values (or one `…Data`), so the event can be queued.
- Listener in `src/Listeners/` of the reacting module, named after what it does (`MarkOrderAsPaid`), with the event type-hinted on `handle()` so discovery registers it.

```php
namespace Vevey\Payments\Events;

final readonly class PaymentConfirmed implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public string $paymentId,
        public string $orderId,
        public Money $amount,
    ) {}
}
```

## Panels

`panel-admin` and `panel-site` sit on top: they use models, Actions and events of any module, and may listen to domain events (e.g. notify the owner on `StockBelowReorderPoint`). Business rules stay in the domain Action the panel calls; the checkout in `panel-site` and the POS page in `panel-admin` both call `orders`' `PlaceOrder`.

## Done when

- Every new `use Vevey\…` statement points down the layer map, or sits in a panel.
- Every new class in `src/DTOs/` ends in `Data` and is `final readonly`; every new class in `src/Events/` implements `ShouldDispatchAfterCommit`.
- `php artisan event:list` shows each new listener against its event.
- `make check` passes, including `tests/Arch/ModuleDependenciesTest.php` once it exists.
