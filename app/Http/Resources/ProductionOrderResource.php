<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ProductionOrderResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'wo_number',
        'start_date',
        'end_date',
        'product_code',
        'product_name',
        'machine_code',
        'machine_name',
        'employee_no',
        'full_name',
        'target_qty',
        'shift',
        'status'
    ];
}
