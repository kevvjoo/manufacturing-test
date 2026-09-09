import { useDashboard } from '../hooks/useDashboard'

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

            {/* KPI section next — pass data.summary as prop */}
            {/* Trend chart next — pass data.trend_7_days as prop */}
            {/* Status pie next — pass data.status_breakdown as prop */}
            {/* Top machine table + bar next — pass data.top_machines as prop */}
        </div>
    )
}