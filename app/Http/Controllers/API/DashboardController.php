<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\MachineResource;
use App\Http\Resources\ProductionOrderResource;
use App\Http\Resources\ProductionResultResource;
use App\Models\Machine;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function productionOrders(Request $request)
    {
        $productionOrders = WorkOrder::query()
            ->select(
                'work_order.wo_number',
                'work_order.plan_start as start_date',
                'work_order.plan_finish as end_date',
                'product.product_code',
                'product.product_name',
                'machine.machine_code',
                'machine.machine_name',
                'employee.employee_no',
                'employee.full_name',
                'work_order.target_qty',
                'work_order.shift',
                'work_order.status'
            )
            ->leftJoin('product', 'product.product_code', '=', 'work_order.product_code')
            ->leftJoin('machine', 'machine.machine_code', '=', 'work_order.machine_code')
            ->leftJoin('employee', 'employee.employee_no', '=', 'work_order.employee_no');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $productionOrders->where(function ($q) use ($search) {
                $q->where('work_order.wo_number', 'like', "%{$search}%")
                  ->orWhere('product.product_name', 'like', "%{$search}%")
                  ->orWhere('machine.machine_name', 'like', "%{$search}%")
                  ->orWhere('employee.full_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $productionOrders->where('work_order.status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $productionOrders->whereDate('work_order.plan_start', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $productionOrders->whereDate('work_order.plan_start', '<=', $request->input('date_to'));
        }

        $sortableMap = [
            'wo_number'   => 'work_order.wo_number',
            'start_date'  => 'work_order.plan_start',
            'end_date'    => 'work_order.plan_finish',
            'target_qty'  => 'work_order.target_qty',
            'status'      => 'work_order.status',
        ];
        $sortByInput = $request->input('sort_by', 'start_date');
        $sortColumn = $sortableMap[$sortByInput] ?? $sortableMap['start_date'];
        $sortDir = $request->input('sort_dir') === 'asc' ? 'asc' : 'desc';
        $productionOrders->orderBy($sortColumn, $sortDir);

        $perPage = $request->input('per_page', 15);
        $workOrders = $productionOrders->paginate($perPage);

        return ProductionOrderResource::collection($workOrders);
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
