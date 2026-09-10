import { Pie } from 'react-chartjs-2'
import {
    Chart as ChartJS,
    ArcElement,
    Tooltip,
    Legend,
} from 'chart.js'
import type { StatusBreakdown } from '../../types/dashboard'

ChartJS.register(ArcElement, Tooltip, Legend)

interface StatusPieProps {
    data: StatusBreakdown[]
}

const STATUS_COLOR: Record<string, string> = {
    RUNNING: '#22c55e',
    FINISHED: '#3b82f6',
    OPEN: '#eab308',
    CANCELLED: '#ef4444',
}

export function StatusPie({ data }: StatusPieProps) {
    const chartData = {
        labels: data.map((d) => d.status),
        datasets: [
            {
                data: data.map((d) => d.total),
                backgroundColor: data.map((d) => STATUS_COLOR[d.status] ?? '#9ca3af'),
                borderWidth: 1,
            },
        ],
    }

    return <Pie data={chartData} />
}