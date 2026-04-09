<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['category_id', 'name', 'description', 'price', 'stock', 'image'];

    protected $casts = ['price' => 'decimal:2'];

    /** Product belongs to one category */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /** Product can appear in many order items */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Helper: check if stock is available */
    public function isAvailable(): bool
    {
        return $this->stock > 0;
    }

    /** Format price as Indonesian Rupiah */
    public function formattedPrice(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }
}
