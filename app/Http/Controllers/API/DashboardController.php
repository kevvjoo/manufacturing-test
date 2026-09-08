<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\MachineResource;
use App\Http\Resources\ProductionResultResource;
use App\Models\Machine;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function machinePerformance(string $machineCode)
    {
        $machine = Machine::query()
            ->select(
                'machine.machine_code',
                'machine.machine_name',
                DB::raw('COUNT(DISTINCT work_order.wo_number) as total_order'),
                DB::raw('SUM(work_order.target_qty) as total_target_qty'),
                DB::raw('SUM(production_result.good_qty) as good_qty'),
                DB::raw('SUM(production_result.reject_qty) as reject_qty'),
                DB::raw('ROUND(SUM(production_result.good_qty) / NULLIF(SUM(work_order.target_qty), 0) * 100, 2) as achievement')
            )
            ->leftJoin('work_order', function ($join) {
                $join->on('work_order.machine_code', '=', 'machine.machine_code')
                    ->where('work_order.status', '<>', 'CANCELLED');
            })
            ->leftJoin('production_result', 'production_result.wo_number', '=', 'work_order.wo_number')
            ->where('machine.machine_code', $machineCode)
            ->groupBy('machine.machine_code', 'machine.machine_name')
            ->first();

            return new MachineResource($machine);
    }

    public function productionResults()
    {
        $productionResults = WorkOrder::query()
            ->select(
                'work_order.wo_number',
                'production_result.actual_start',
                'production_result.actual_finish',
                'product.product_code',
                'product.product_name',
                'machine.machine_code',
                'machine.machine_name',
                'work_order.target_qty',
                'production_result.good_qty',
                'production_result.reject_qty'
            )
            ->leftJoin('production_result', 'production_result.wo_number', '=', 'work_order.wo_number')
            ->leftJoin('product', 'product.product_code', '=', 'work_order.product_code')
            ->leftJoin('machine', 'machine.machine_code', '=', 'work_order.machine_code')
            ->where([
                ['work_order.status', 'RUNNING'],
                ['production_result.actual_start', '<=', now()],
                ['production_result.good_qty', '>=', 0],
                ['production_result.reject_qty', '>=', 0]
            ])
            ->get();

            return ProductionResultResource::collection($productionResults);
    }
}
