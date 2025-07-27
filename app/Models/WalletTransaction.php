<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class WalletTransaction extends Model
{
    use HasFactory;

    public const STATUS_CREDITED = 101;
    public const STATUS_DEBITED = 102;
    public const STATUS_PROCESSING = 103;
    public const STATUS_ORDER_PROCESSING = 104;
    public const STATUS_ORDER_FAILED = 105;


    protected $fillable = ['user_id', 'invoice_number', 'name', 'issued_date', 'amount', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }



    public static function getStatusOptions()
    {
        return [
            self::STATUS_CREDITED => 'Wallet credited',
            self::STATUS_DEBITED => 'Wallet debited',
            self::STATUS_PROCESSING => 'Wallet processing',
            self::STATUS_ORDER_PROCESSING => 'Order processing',
            self::STATUS_ORDER_FAILED => 'Order failed',
        ];
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            self::STATUS_CREDITED => 'Wallet credited',
            self::STATUS_DEBITED => 'Wallet debited',
            self::STATUS_PROCESSING => 'Wallet processing',
            self::STATUS_ORDER_PROCESSING => 'Order processing',
            self::STATUS_ORDER_FAILED => 'Order failed',
        ];

        return $statuses[$this->status] ?? 'Unknown status';
    }
}
