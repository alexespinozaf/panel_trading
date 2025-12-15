// resources/js/types/trading.ts

export interface Strategy {
  id: number
  name: string
  type: string | null
  description: string | null
  config: Record<string, any> | null
  is_public: boolean
  created_at: string
  updated_at: string
}

export type BotMode = 'live' | 'paper'
export type BotStatus = 'created' | 'running' | 'paused' | 'stopped' | 'error'

export interface BotRelationSummary {
  id: number
  name: string
}

export interface Bot {
  id: number
  name: string
  symbol: string
  mode: BotMode
  status: BotStatus
  base_order_size: string | null
  leverage: number
  max_positions: number
  config: Record<string, any> | null
  exchange_account_id: number
  strategy_id: number
  exchange_account?: BotRelationSummary
  strategy?: BotRelationSummary
  started_at: string | null
  stopped_at: string | null
  created_at: string
  updated_at: string
}
export interface BotLog {
  id: number
  bot_id: number
  bot_run_id: number | null
  level: string
  code: string | null
  message: string
  context: Record<string, any> | null
  created_at: string
  updated_at: string
}

export interface Order {
  id: number
  bot_id: number
  symbol: string
  side: 'BUY' | 'SELL'
  type: string
  status: string
  price: string | null
  avg_price: string | null
  quantity: string
  executed_quantity: string
  commission: string | null
  commission_asset: string | null
  reduce_only: boolean
  time_in_force: string | null
  placed_at: string | null
  updated_at_exchange: string | null
  created_at: string
  updated_at: string
}

export interface BotSignal {
  id: number
  bot_id: number
  symbol: string
  timeframe: string
  signal_time: string
  signal: 'BUY' | 'SELL' | 'NONE'
  price: string
  rsi: string | null
  ema_fast: string | null
  ema_slow: string | null
  atr: string | null
  future_return: string | null
  created_at: string
  updated_at: string
}

export interface BotLoopData {
  bot: Bot
  logs: BotLog[]
  orders: Order[]
  signals: BotSignal[]
}
