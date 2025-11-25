<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public static function generateItemcodeNo()
    {
        $prefix = 'COD-';
        $lastcode = self::orderBy('id', 'desc')->first();
        $lastNumber = $lastcode ? (int)substr($lastcode->item_code, strlen($prefix)) : 0;
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        // Return the new invoice number
        return $prefix . $newNumber;
    }
}
