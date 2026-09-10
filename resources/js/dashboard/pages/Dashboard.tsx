import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { KpiGrid } from '../components/Kpi/KpiGrid'
import { TopMachineTable } from '../components/Table/TopMachineTable'
import { useDashboard } from '../hooks/useDashboard'
import { StatusPie } from '../components/Charts/StatusPie'

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

            <KpiGrid summary={data.summary}></KpiGrid>
            {/* Trend chart next — pass data.trend_7_days as prop */}
            <Card>
                <CardHeader>
                    <CardTitle>Status Breakdown</CardTitle>
                </CardHeader>
                <CardContent>
                    <StatusPie data={data.status_breakdown} />
                </CardContent>
            </Card>
            <Card>
                <CardHeader>
                    <CardTitle>Top Machine Performance</CardTitle>
                </CardHeader>
                <CardContent>
                    <TopMachineTable machines={data.top_machines} />
                </CardContent>
            </Card>
        </div>
    )
}