<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'order_code', 'delivery_type',
        'address', 'status', 'payment_method', 'total', 'delivery_fee',
        'lat_from', 'lng_from', 'lat_to', 'lng_to',
        'distance_km', 'reservation_at', 'notes',
    ];

    protected $casts = [
        'total'          => 'decimal:2',
        'delivery_fee'   => 'decimal:2',
        'lat_from'       => 'decimal:7',
        'lng_from'       => 'decimal:7',
        'lat_to'         => 'decimal:7',
        'lng_to'         => 'decimal:7',
        'distance_km'    => 'decimal:2',
        'reservation_at' => 'datetime',
    ];

    public const STATUS_LABELS = [
        'wait'       => 'Waiting',
        'processing' => 'Processing',
        'ready'      => 'Ready to Take / Delivered',
        'done'       => 'Done',
    ];

    public const STATUS_COLORS = [
        'wait'       => 'secondary',
        'processing' => 'warning',
        'ready'      => 'info',
        'done'       => 'success',
    ];

    public const TYPE_LABELS = [
        'takeaway'    => 'Take Away',
        'delivery'    => 'Delivery',
        'reservation' => 'Reservation',
    ];

    public const TYPE_ICONS = [
        'takeaway'    => 'bi-bag-check',
        'delivery'    => 'bi-truck',
        'reservation' => 'bi-calendar-check',
    ];

    public function user()  { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(OrderItem::class); }

    public function statusLabel(): string { return self::STATUS_LABELS[$this->status] ?? $this->status; }
    public function statusColor(): string { return self::STATUS_COLORS[$this->status] ?? 'secondary'; }
    public function typeLabel(): string   { return self::TYPE_LABELS[$this->delivery_type] ?? $this->delivery_type; }
    public function typeIcon(): string    { return self::TYPE_ICONS[$this->delivery_type] ?? 'bi-box'; }

    public function formattedTotal(): string
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    public function formattedDeliveryFee(): string
    {
        return 'Rp ' . number_format($this->delivery_fee, 0, ',', '.');
    }

    public function grandTotal(): float
    {
        return (float)$this->total + (float)$this->delivery_fee;
    }

    public function formattedGrandTotal(): string
    {
        return 'Rp ' . number_format($this->grandTotal(), 0, ',', '.');
    }

    /**
     * Rp 3,000 per 3 km block (round up). e.g. 4km → 2 blocks → Rp 6,000
     */
    public static function calcDeliveryFee(float $km): float
    {
        if ($km <= 0) return 0;
        return ceil($km / 3) * 3000;
    }
}
