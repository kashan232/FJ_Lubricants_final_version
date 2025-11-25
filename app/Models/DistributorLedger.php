<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistributorLedger extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function distributor()
    {
        return $this->belongsTo(Distributor::class, 'distributor_id');
    }
}
