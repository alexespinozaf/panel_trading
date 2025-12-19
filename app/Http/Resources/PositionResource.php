<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PositionResource extends JsonResource
{
    public function toArray($request)
    {
        if (! $this->resource) {
            return null;
        }

        return [
            'id' => $this->id,
            'symbol' => $this->symbol,
            'side' => $this->side,
            'quantity' => $this->quantity,
            'entry_price' => $this->entry_price,
            'status' => $this->status,
            'opened_at' => optional($this->opened_at)->toIso8601String(),
            'closed_at' => optional($this->closed_at)->toIso8601String(),

            // estos NO son columnas, son atributos calculados:
            'last_price' => $this->last_price ?? null,
            'pnl_gross'  => $this->pnl_gross ?? null,
            'pnl_net'    => $this->pnl_net ?? null,
            'total_fees' => $this->total_fees ?? null,
        ];
    }
}
