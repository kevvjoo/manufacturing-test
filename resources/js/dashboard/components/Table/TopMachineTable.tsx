import {
    Table,
    TableHeader,
    TableBody,
    TableRow,
    TableHead,
    TableCell,
} from '@/components/ui/table'
import type { TopMachine } from '../../types/dashboard'

interface TopMachineTableProps {
    machines: TopMachine[]
}

export function TopMachineTable({ machines }: TopMachineTableProps) {
    return (
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Machine Code</TableHead>
                    <TableHead>Machine Name</TableHead>
                    <TableHead className="text-right">Good Qty</TableHead>
                    <TableHead className="text-right">Achievement</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {machines.map((machine) => (
                    <TableRow key={machine.machine_code}>
                        <TableCell>{machine.machine_code}</TableCell>
                        <TableCell>{machine.machine_name}</TableCell>
                        <TableCell className="text-right">{machine.good_qty}</TableCell>
                        <TableCell className="text-right">{machine.achievement}%</TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    )
}