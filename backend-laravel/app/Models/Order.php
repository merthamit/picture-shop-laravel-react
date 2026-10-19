<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = ['total', 'user_id', 'picture_id', 'status', 'payment_intent_id'];
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Buyers keep access to pictures the seller removed from the shop
    public function picture()
    {
        return $this->belongsTo(Picture::class)->withTrashed();
    }
}
