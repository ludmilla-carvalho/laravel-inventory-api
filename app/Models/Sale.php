<?php

namespace App\Models;

use App\Enums\SaleStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'total_amount',
        'total_cost',
        'total_profit',
        'status',
    ];

    protected $casts = [
        'status' => SaleStatus::class,
    ];
}
