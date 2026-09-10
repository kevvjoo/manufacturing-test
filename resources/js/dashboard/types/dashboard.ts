export interface DashboardSummary {
    total_machine: number
    running_order: number
    finished_order: number
    today_target: number
    today_good: number
    today_reject: number
    achievement: number
}

export interface TrendDay {
    date: string
    good_qty: number
    reject_qty: number
    achievement: number
}

export interface StatusBreakdown {
    status: string
    total: number
}

export interface TopMachine {
    machine_code: string
    machine_name: string
    good_qty: number
    achievement: number
}

export interface DashboardResponse {
    summary: DashboardSummary
    trend_7_days: TrendDay[]
    status_breakdown: StatusBreakdown[]
    top_machines: TopMachine[]
}