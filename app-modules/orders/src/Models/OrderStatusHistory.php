<?php

declare(strict_types=1);

namespace Vevey\Orders\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $order_id
 * @property string $field
 * @property string|null $from_status
 * @property string $to_status
 * @property string|null $user_id
 * @property string|null $note
 * @property Carbon $created_at
 * @property-read Order $order
 * @property-read User|null $user
 */
#[Fillable(['order_id', 'field', 'from_status', 'to_status', 'user_id', 'note', 'created_at'])]
#[Table(name: 'order_status_histories')]
final class OrderStatusHistory extends Model
{
    use HasUuids;

    public const null UPDATED_AT = null;

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}
