<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
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
            'email' => $this->email,
            'realname' => $this->realname,
            'isAdvertiseEnabled' => $this->isAdvertiseEnabled,
            'isPublishEnabled' => $this->isPublishEnabled,
            'status' => $this->status,
            'children' => $this->advertisers,
            'bill' => $this->bill,
        ];
    }
}
