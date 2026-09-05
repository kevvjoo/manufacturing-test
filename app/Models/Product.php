<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(
    'product_code',
    'product_name',
    'category',
    'target_min', 'target_max'
)]
class Product extends Model
{
    protected $table = 'product';
    protected $primaryKey = 'product_code';

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }
}
