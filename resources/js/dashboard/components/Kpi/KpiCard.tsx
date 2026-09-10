import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'

interface KpiCardProps {
    label: string
    value: number | string
    suffix?: string
}

export function KpiCard({ label, value, suffix }: KpiCardProps) {
    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-sm font-medium text-gray-500">
                    {label}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <p className="text-2xl font-semibold text-gray-900">
                    {value}
                    {suffix && <span className="text-base font-normal ml-1">{suffix}</span>}
                </p>
            </CardContent>
        </Card>
    )
}