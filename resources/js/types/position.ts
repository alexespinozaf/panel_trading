export interface Position {
  id: number
  symbol: string
  side: string
  quantity: string
  entry_price: string
  status: string
  opened_at: string | null
  closed_at: string | null
  last_price: string | null
  pnl_gross: number | null
  pnl_net: number | null
  total_fees: number | null
}
