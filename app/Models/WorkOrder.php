<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(
    'wo_number', 'product_code', 'machine_code', 'employee_no',
    'shift', 'target_qty', 'plan_start', 'plan_finish', 'status'
)]
class WorkOrder extends Model
{
    protected   $table = 'work_order';
    protected   $primaryKey = 'wo_number';
    protected   $keyType = 'string';
    public      $incrementing = false;
    public      $timestamps = false;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class, 'machine_code', 'machine_code');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function productionResults(): HasOne
    {
        return $this->hasOne(ProductionResult::class, 'wo_number', 'wo_number');
    }
}
