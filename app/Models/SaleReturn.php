<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleReturn extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function distributor()
    {
        return $this->belongsTo(Distributor::class, 'party_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'party_id');
    }
}
