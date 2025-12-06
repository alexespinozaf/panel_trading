// resources/js/types/exchange.ts

export type ExchangeStatus = 'active' | 'disabled' | 'error'

export interface ExchangeAccount {
  id: number
  name: string
  exchange: string
  is_futures: boolean
  is_testnet:boolean
  status: ExchangeStatus
  last_synced_at: string | null
  // si Laravel devuelve otros campos (extra, created_at...), los agregas aquí
}

export interface ExchangeAccountForm {
  name: string
  exchange: string
  api_key: string
  api_secret: string
  is_futures: boolean
  is_testnet:boolean
}
