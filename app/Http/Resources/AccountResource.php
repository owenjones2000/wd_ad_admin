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
            'ava_credit' => $this->ava_credit,
            'expend_credit' => $this->expend_credit,
            'isAdvertiseEnabled' => $this->isAdvertiseEnabled,
            'isPublishEnabled' => $this->isPublishEnabled,
            'status' => $this->status,
            'agency_name' => $this->agency_name,
            'is_agency' => $this->is_agency,
            'status' => $this->status,
            'subAccounts' => $this->advertisers,
            'bill' => $this->bill,
        ];
    }
}
