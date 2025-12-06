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
