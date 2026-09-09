<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ProductionResultResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'wo_number',
        'actual_start',
        'actual_finish',
        'product_code',
        'product_name', 
        'machine_code',
        'machine_name',
        'target_qty',
        'good_qty',
        'reject_qty'
    ];
}
