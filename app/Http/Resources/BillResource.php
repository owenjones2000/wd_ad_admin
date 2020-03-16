<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BillResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'realname' => $this->account->realname,
            'email' => $this->account->email,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'fee_amount' => $this->fee_amount,
            'due_date' => $this->due_date,
            'paid_at' => $this->paid_at,
        ];
    }
}
