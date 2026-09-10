import { KpiCard } from './KpiCard'
import type { DashboardSummary } from '../../types/dashboard'

interface KpiGridProps {
    summary: DashboardSummary
}

export function KpiGrid({ summary }: KpiGridProps) {
    return (
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            <KpiCard label="Total Machine" value={summary.total_machine} />
            <KpiCard label="Running Order" value={summary.running_order} />
            <KpiCard label="Finished Order" value={summary.finished_order} />
            <KpiCard label="Today Target" value={summary.today_target} />
            <KpiCard label="Today Good Qty" value={summary.today_good} />
            <KpiCard label="Today Reject Qty" value={summary.today_reject} />
            <KpiCard label="Achievement" value={summary.achievement} suffix="%" />
        </div>
    )
}