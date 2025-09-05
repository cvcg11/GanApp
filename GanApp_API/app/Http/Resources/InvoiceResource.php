<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         return [
            'id'          => $this->id,
            'number'      => $this->invoice_number,
            'date'        => optional($this->date)->toDateString(),
            'total'       => $this->total,
            'description' => $this->description,
            'lines'       => $this->whenLoaded('products', function () {
                return $this->products->map(fn ($p) => [
                    'product_id'   => $p->id,
                    'name'         => $p->name,
                    'quantity'     => (int) $p->pivot->quantity,
                    'unit_price'   => (string) $p->pivot->unit_price,
                    'subtotal'     => (string) $p->pivot->subtotal,
                ]);
            })
                
        ];
    }
}
