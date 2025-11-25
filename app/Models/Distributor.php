<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Distributor extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function sales()
    {
        return $this->hasMany(Sale::class, 'distributor_id');
    }
}
