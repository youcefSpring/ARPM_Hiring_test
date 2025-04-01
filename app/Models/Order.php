<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'status', 'created_at', 'completed_at'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function latestCartItem()
    {
        return $this->hasOne(CartItem::class)->latestOfMany();
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
