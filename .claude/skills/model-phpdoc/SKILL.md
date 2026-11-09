---
name: model-phpdoc
description: Mandatory rules for Eloquent models in this project. Use whenever creating a model, writing or editing a migration that adds, removes, renames or retypes a column, changing casts(), or reviewing a model. Covers the @property PHPDoc block that must mirror the schema, the column-to-PHPDoc type mapping, and the required class-level #[Table] and #[UseFactory] attributes.
---

# Model PHPDoc Sync — Mandatory on Schema Changes

**Priority: HIGH** — This rule is non-negotiable. Every schema change MUST update the corresponding model PHPDoc.

## Rule

When you **add, remove, rename, or change the type** of any database column (via migration, manual SQL, or schema dump), the `@property` PHPDoc block on the affected Model class **MUST be updated in the same commit**.

## What triggers this rule

- `php artisan make:migration` that adds/removes/alters columns
- Any edit to an existing migration file
- Any raw SQL that changes table structure
- Renaming a column
- Changing a column type (e.g., `string` → `text`, `timestamp` → `timestampTz`)
- Adding/removing nullable
- Adding/removing a default value that changes the PHPDoc type (e.g., nullable → non-nullable)

## PHPDoc format

```php
/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property bool $active
 * @property Carbon|null $starts_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Table(name: 'example_table')]
final class Example extends Model
```

## Type mapping

| Column type | PHPDoc type |
|-------------|-------------|
| `id`, `bigIncrements` | `int` |
| `uuid` | `string` |
| `string`, `text` | `string` |
| `integer`, `bigInteger` | `int` |
| `boolean` | `bool` |
| `timestamp`, `datetime`, `timestampTz` | `Carbon\|null` |
| `json`, `jsonb` | `array<string, mixed>\|null` |
| `decimal`, `float` | `float` |
| string column cast to a backed enum | `EnumClass` |
| money column (integer cents) with a Money cast | the cast's value object (e.g. `Brick\Money\Money`) |

- Add `|null` when the column is nullable.
- Use the cast type for enums and custom casts, not the raw DB type.
- `created_at` and `updated_at` are always `Carbon|null`.
- In this project money is stored as integer cents and enums as `string` columns with a PHP enum cast (see `docs/MODELAGEM_LARAVEL_ADR.md`): the PHPDoc type follows the cast, never the raw column.

## Explicit class-level attributes — mandatory

Every Eloquent model MUST declare these attributes explicitly, even when values match Laravel's convention:

- `#[Table(name: '...')]` — explicit table name, always required.
- `#[UseFactory(XxxFactory::class)]` — explicit factory binding, replaces `newFactory()` overrides. The `HasFactory` trait is still required (provides `factory()`).

```php
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;

// GOOD — always explicit:
#[UseFactory(UserFactory::class)]
#[Table(name: 'users')]
final class User extends Model
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
}

// BAD — implicit table name or missing factory attribute:
final class User extends Model
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
```

## Verification

Before marking a migration task as done, confirm:

1. The model file has a `/** @property ... */` block above the class
2. Every column in the table has a corresponding `@property` line
3. Types match the column definition and any explicit `casts()`
4. The model has `#[Table(name: '...')]` with the explicit table name
5. The model has `#[UseFactory(XxxFactory::class)]` if it has a factory
6. PHPStan passes (`vendor/bin/phpstan analyse`)
