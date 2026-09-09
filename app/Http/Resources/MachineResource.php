<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class MachineResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'machine_code',
        'machine_name',
        'total_order',
        'total_target_qty',
        'good_qty',
        'reject_qty',
        'achievement',
    ];
}
