<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(
    'id', 'wo_number', 'actual_start', 'actual_finish', 'runtime_minutes',
    'good_qty', 'reject_qty', 'achivement'
)]
class ProductionResult extends Model
{
    protected $table = 'production_result';
    protected $primaryKey = 'id';

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
