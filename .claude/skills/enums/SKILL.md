---
name: enums
description: Rules for PHP enums in this project. Use whenever creating or modifying an enum (backed or pure), adding a case, casting a string column to an enum, or when a call site compares a value against an enum case. Covers the Filament contracts (HasLabel, HasColor, HasDescription, HasIcon), the light-to-red color ramp for ordered enums, and the one is<Case>() predicate per case rule.
---

# Enums — Contracts

Two rules for enums, both applied in the change that creates the enum: backed enums
implement the Filament contracts, and every enum — backed or pure — declares one
`is<Case>()` predicate per case.

Context for this project: enums are never native database types. Columns are `string`
and the Eloquent model casts them to the PHP enum (see `docs/MODELAGEM_LARAVEL_ADR.md`).
The enum is therefore the only place where labels, colors and rules about a case live.

## Filament contracts — implement them immediately

**Priority: HIGH.** Every time you create a **new** backed enum, implement the Filament
"enum trick" contracts in the **same change** — never as a follow-up pass. Retrofitting
later means touching every enum (and every call site that assumed a bare `->value`) twice.

The panel leans on these contracts for badges, table/infolist columns, `SelectFilter`
options, select descriptions and state-gated UI.

## Mandatory contracts

| Contract | Method | Always? |
|----------|--------|---------|
| `Filament\Support\Contracts\HasLabel` | `getLabel(): string` | Yes |
| `Filament\Support\Contracts\HasColor` | `getColor()` | Yes |
| `Filament\Support\Contracts\HasDescription` | `getDescription(): ?string` | Yes |
| `Filament\Support\Contracts\HasIcon` | `getIcon()` | Only when an icon is genuinely meaningful |

Every getter is a `match ($this)` over **all** cases — no `default` arm, so adding a case
is a compile-time reminder to fill in every trick.

## HasColor — ordered enums ramp light → red

When the enum encodes an **ordered scale** (access level, seniority, severity, risk, SLA
health), `getColor()` MUST form a monotonic light→red heat ramp: the least/lowest case gets
the lightest color, the most/highest gets `danger` (red). The six semantic strings
(`gray`, `info`, `primary`, `success`, `warning`, `danger`) cover short scales; for a longer
monotonic gradient use the `Filament\Support\Colors\Color` palette.

For an **unordered** enum (e.g. document kinds), pick distinct semantic colors per case —
the ramp rule does not apply.

## The pattern

```php
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum AccessTier: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case None = 'none';
    case Analyst = 'analyst';
    case Admin = 'admin';

    public function getLabel(): string
    {
        return match ($this) {
            self::None => 'No access',
            self::Analyst => 'Analyst',
            self::Admin => 'Administration',
        };
    }

    // Ordered scale → light (no access) ramps to red (apex authority).
    public function getColor(): string|array
    {
        return match ($this) {
            self::None => 'gray',
            self::Analyst => Color::Blue,
            self::Admin => Color::Red,
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::None => 'Notification audience — no panel access',
            self::Analyst => 'KYC workhorse: screening, risk, EDD',
            self::Admin => 'Final authority: decides dossiers, ratifies',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::None => Heroicon::OutlinedNoSymbol,
            self::Analyst => Heroicon::OutlinedMagnifyingGlass,
            self::Admin => Heroicon::OutlinedScale,
        };
    }
}
```

## Case predicates — one `is<Case>()` per case

Every enum — backed or pure — declares a public `is<Case>(): bool` for each of its cases,
and a case added later ships with its predicate. Callers ask the enum instead of comparing
against a case: the call site reads as a sentence, and a file that only compared against
the case no longer imports the enum.

```php
// Avoid
if ($post->status === PostStatus::Draft) {
    // ...
}

// Prefer
if ($post->status->isDraft()) {
    // ...
}
```

1. **Name** — `is` followed by the case name exactly as declared: `case SuperAdmin` →
   `isSuperAdmin()`, `case NaturalPerson` → `isNaturalPerson()`.
2. **Body** — a single identity check: `return $this === self::Draft;`.
3. **Placement** — in case order, before the Filament getters.
4. **A rule over several cases keeps its own name** — `canLogIn()`, `isTerminal()`,
   `isBlocked()`. Look for one on the enum before chaining predicates at the call site
   (`isActive() || isPending()`).
5. **`mixed` values keep `===`** — a value PHPStan types as `mixed`, such as the result of
   `Builder::value('status')`, is `null` when no row matches. A predicate call on it fails
   at runtime on that `null`, and PHPStan reads its result as `mixed`. Compare that value
   with `===`.

```php
enum AccessTier: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case None = 'none';
    case Analyst = 'analyst';
    case Admin = 'admin';

    public function isNone(): bool
    {
        return $this === self::None;
    }

    public function isAnalyst(): bool
    {
        return $this === self::Analyst;
    }

    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }

    // getLabel(), getColor(), getDescription() and getIcon() as in the pattern above.
}
```

## Verification

Before marking an enum task done, confirm:

1. The enum `implements HasColor, HasDescription, HasLabel` (and `HasIcon` when meaningful).
2. Every getter `match`es **all** cases with no `default`.
3. Ordered enums ramp light → `danger`; the apex case is red.
4. Every case has its `is<Case>()` predicate, and no call site compares a non-`mixed` value
   against a case of the enum with `===`.
5. `vendor/bin/pint` and `vendor/bin/phpstan analyse` pass.
