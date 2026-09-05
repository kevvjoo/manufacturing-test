<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(
    'employee_no',
    'full_name'
)]
class Employee extends Model
{
    protected $table = 'employee';
    protected $primaryKey = 'employee_no';

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }
}
