<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $table = 'stocks';

    protected $fillable = [
        'nm_id',
        'subject',
        'category',
        'brand',
        'is_supply',
        'is_realization',
        'tech_size',
        'barcode',
        'quantity',
    ];
}
