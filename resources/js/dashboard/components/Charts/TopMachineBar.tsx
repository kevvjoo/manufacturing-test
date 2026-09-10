import { Bar } from 'react-chartjs-2'
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Tooltip,
    Legend,
} from 'chart.js'
import type { TopMachine } from '../../types/dashboard'

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend)

interface TopMachineBarProps {
    machines: TopMachine[]
}

export function TopMachineBar({ machines }: TopMachineBarProps) {
    const chartData = {
        labels: machines.map((m) => m.machine_name),
        datasets: [
            {
                label: 'Good Qty',
                data: machines.map((m) => m.good_qty),
                backgroundColor: '#3b82f6',
                borderRadius: 4,
            },
        ],
    }

    const options = {
        responsive: true,
        plugins: {
            legend: { display: false },
        },
        scales: {
            y: { beginAtZero: true },
        },
    }

    return <Bar data={chartData} options={options} />
}