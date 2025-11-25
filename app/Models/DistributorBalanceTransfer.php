<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistributorBalanceTransfer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function toDistributor()
    {
        return $this->belongsTo(Distributor::class, 'to_distributor');
    }
}
