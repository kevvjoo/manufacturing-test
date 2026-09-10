import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { KpiGrid } from '../components/Kpi/KpiGrid'
import { TopMachineTable } from '../components/Table/TopMachineTable'
import { useDashboard } from '../hooks/useDashboard'
import { StatusPie } from '../components/Charts/StatusPie'
import { TopMachineBar } from '../components/Charts/TopMachineBar'
import { TrendLine } from '../components/Charts/TrendLine'

export default function Dashboard() {
    const { data, isLoading, isError, error } = useDashboard()

    if (isLoading) {
        return <div className="p-6 text-gray-500">Loading dashboard...</div>
    }

    if (isError) {
        return (
            <div className="p-6 text-red-600">
                Failed to load dashboard: {error.message}
            </div>
        )
    }

    if (!data) {
        return null
    }

    return (
        <div className="p-6 space-y-6">
            <h1 className="text-2xl font-semibold text-gray-800">Production Dashboard</h1>

            <KpiGrid summary={data.summary} />

            {/* Pie + Bar shared row */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Status Breakdown</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="h-72">
                            <StatusPie data={data.status_breakdown} />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Top 10 Machine — Good Qty</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="h-72 w-full">
                            <TopMachineBar machines={data.top_machines} />
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Line chart full row */}
            <Card>
                <CardHeader>
                    <CardTitle>7-Day Production Trend</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="h-72">
                        <TrendLine data={data.trend_7_days} />
                    </div>
                </CardContent>
            </Card>
        </div>
    )
}