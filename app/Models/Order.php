<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'order_code', 'delivery_type',
        'address', 'status', 'total', 'notes'
    ];

    protected $casts = ['total' => 'decimal:2'];

    // ── Status labels displayed to users ──────────────
    public const STATUS_LABELS = [
        'wait'       => 'Waiting',
        'processing' => 'Processing',
        'ready'      => 'Ready to Take / Delivered',
        'done'       => 'Done',
    ];

    // Bootstrap badge colours per status
    public const STATUS_COLORS = [
        'wait'       => 'secondary',
        'processing' => 'warning',
        'ready'      => 'info',
        'done'       => 'success',
    ];

    // ── Relationships ──────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // ── Helpers ────────────────────────────────────────
    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    public function formattedTotal(): string
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }
}
