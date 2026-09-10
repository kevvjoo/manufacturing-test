import { Line } from 'react-chartjs-2'
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Tooltip,
    Legend,
} from 'chart.js'
import type { TrendDay } from '../../types/dashboard'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Tooltip, Legend)

interface TrendLineProps {
    data: TrendDay[]
}

export function TrendLine({ data }: TrendLineProps) {
    const chartData = {
        labels: data.map((d) => d.date),
        datasets: [
            {
                label: 'Good Qty',
                data: data.map((d) => d.good_qty),
                borderColor: '#22c55e',
                backgroundColor: '#22c55e',
                yAxisID: 'y',
                tension: 0.3,
            },
            {
                label: 'Reject Qty',
                data: data.map((d) => d.reject_qty),
                borderColor: '#ef4444',
                backgroundColor: '#ef4444',
                yAxisID: 'y',
                tension: 0.3,
            },
            {
                label: 'Achievement %',
                data: data.map((d) => d.achievement),
                borderColor: '#3b82f6',
                backgroundColor: '#3b82f6',
                yAxisID: 'y1',
                tension: 0.3,
                borderDash: [5, 5],
            },
        ],
    }

    const options = {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index' as const, intersect: false },
        scales: {
            y: {
                type: 'linear' as const,
                position: 'left' as const,
                beginAtZero: true,
                title: { display: true, text: 'Qty' },
            },
            y1: {
                type: 'linear' as const,
                position: 'right' as const,
                beginAtZero: true,
                max: 100,
                grid: { drawOnChartArea: false },
                title: { display: true, text: 'Achievement (%)' },
            },
        },
    }

    return <Line data={chartData} options={options} />
}