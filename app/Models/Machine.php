<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(
    'machine_code',
    'machine_name',
    'production_line'
)]
class Machine extends Model
{
    protected $table = 'machine';
    protected $primaryKey = 'machine_code';

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }
}
