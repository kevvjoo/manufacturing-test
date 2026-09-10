<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardResource;
use App\Http\Resources\MachineResource;
use App\Http\Resources\ProductionOrderResource;
use App\Http\Resources\ProductionResultResource;
use App\Models\Machine;
use App\Models\ProductionResult;
use App\Models\WorkOrder;
use App\Utils\Constants;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $latestDate = ProductionResult::max('actual_start');

        $today = $latestDate
            ? Carbon::parse($latestDate)->toDateString()
            : null;

        if (! $today) {
            return new DashboardResource([
                'summary'          => [
                    'total_machine'  => Machine::count(),
                    'running_order'  => WorkOrder::running()->count(),
                    'finished_order' => WorkOrder::finished()->count(),
                    'today_target'   => 0,
                    'today_good'     => 0,
                    'today_reject'   => 0,
                    'achievement'    => 0,
                ],
                'trend_7_days'     => [],
                'status_breakdown' => WorkOrder::query()->select('status')->selectRaw('COUNT(*) as total')->groupBy('status')->get(),
                'top_machines'     => [],
            ]);
        }

        $totalMachine = Machine::count();
        $openOrder = WorkOrder::open()->count();
        $runningOrder = WorkOrder::running()->count();
        $finishedOrder = WorkOrder::finished()->count();
        $cancelledOrder = WorkOrder::cancelled()->count();

        $todayAgg = DB::table('production_result')
            ->whereDate('actual_start', $today)
            ->selectRaw('SUM(good_qty) as good, SUM(reject_qty) as reject')
            ->first();

        $todayTarget = DB::table('work_order')
            ->join('production_result', 'production_result.wo_number', '=', 'work_order.wo_number')
            ->whereDate('production_result.actual_start', $today)
            ->sum('work_order.target_qty');

        $summary = [
            'total_machine'     => $totalMachine,
            'open_order'        => $openOrder,
            'running_order'     => $runningOrder,
            'finished_order'    => $finishedOrder,
            'cancelled_order'   => $cancelledOrder,
            'today_target'      => (int) $todayTarget,
            'today_good'        => (int) ($todayAgg->good ?? 0),
            'today_reject'      => (int) ($todayAgg->reject ?? 0),
            'achievement'       => $todayTarget > 0
                ? round(($todayAgg->good ?? 0) / $todayTarget * 100, 2)
                : 0,
        ];

        $startDate = Carbon::parse($today)->subDays(6)->toDateTimeString();

        $trend = DB::select("
            WITH RECURSIVE date_series AS (
                SELECT DATE(?) AS report_date
                UNION ALL
                SELECT DATE_ADD(report_date, INTERVAL 1 DAY)
                FROM date_series
                WHERE report_date < ?
            ),
            daily_production AS (
                SELECT
                    DATE(actual_start) AS production_date,
                    SUM(good_qty) AS good_qty,
                    SUM(reject_qty) AS reject_qty
                FROM production_result
                WHERE actual_start BETWEEN ? AND ?
                GROUP BY DATE(actual_start)
            ),
            daily_target AS (
                SELECT
                    DATE(pr.actual_start) AS production_date,
                    SUM(DISTINCT wo.target_qty) AS target_qty
                FROM production_result pr
                JOIN work_order wo ON wo.wo_number = pr.wo_number
                WHERE pr.actual_start BETWEEN ? and ?
                GROUP BY DATE(pr.actual_start)
            )
            SELECT
                ds.report_date AS date,
                COALESCE(dp.good_qty, 0)   AS good_qty,
                COALESCE(dp.reject_qty, 0) AS reject_qty,
                COALESCE(
                    ROUND(COALESCE(dp.good_qty, 0) / NULLIF(dt.target_qty, 0) * 100, 2),
                    0
                ) AS achievement
            FROM date_series ds
            LEFT JOIN daily_production dp ON dp.production_date = ds.report_date
            LEFT JOIN daily_target dt ON dt.production_date = ds.report_date
            ORDER BY ds.report_date
        ", [
            $startDate, $today,
            $startDate, $today,
            $startDate, $today,
        ]);

        $statusBreakdown = WorkOrder::query()
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->get();

        $topMachines = Machine::query()
            ->select('machine.machine_code', 'machine.machine_name')
            ->leftJoin('work_order', 'work_order.machine_code', '=', 'machine.machine_code')
            ->leftJoin('production_result', 'production_result.wo_number', '=', 'work_order.wo_number')
            ->selectRaw('SUM(production_result.good_qty) as good_qty')
            ->selectRaw(
                'ROUND(SUM(production_result.good_qty) / NULLIF(SUM(work_order.target_qty), 0) * 100, 2) as achievement'
            )
            ->groupBy('machine.machine_code', 'machine.machine_name')
            ->orderByDesc('good_qty')
            ->limit(10)
            ->get();

        return new DashboardResource([
            'summary'          => $summary,
            'trend_7_days'     => $trend,
            'status_breakdown' => $statusBreakdown,
            'top_machines'     => $topMachines,
        ]);
    }

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
                    ->where('work_order.status', '<>', Constants::STATUS_CANCELLED);
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
                ['work_order.status', Constants::STATUS_RUNNING],
                ['production_result.actual_start', '<=', now()],
                ['production_result.good_qty', '>=', 0],
                ['production_result.reject_qty', '>=', 0]
            ])
            ->get();

            return ProductionResultResource::collection($productionResults);
    }
}
