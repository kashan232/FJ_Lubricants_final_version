<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Customer extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $guarded = [];


    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function businessType()
    {
        return $this->belongsTo(BusinessType::class);
    }

    public function localSales()
    {
        return $this->hasMany(LocalSale::class, 'customer_id');
    }
}
