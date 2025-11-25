<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recovery extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function distributor()
    {
        return $this->belongsTo(Distributor::class, 'distributor_ledger_id');
    }

    public function ledger()
    {
        return $this->belongsTo(DistributorLedger::class, 'distributor_ledger_id');
    }

}
