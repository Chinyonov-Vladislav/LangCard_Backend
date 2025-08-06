<?php

namespace App\Http\Resources\V1\ProfileUserResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'vip_status_time_end' => $this->vip_status_time_end
                ? $this->vip_status_time_end->format('Y-m-d H:i:s')
                : null,
            'invite_code'=> $this->isAuthUser ? $this->invite_code : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'currency' => $this->when($this->relationLoaded('currency') && $this->currency !== null, function () {
                return [
                    'id' => $this->currency->id,
                    'name' => $this->currency->name,
                    'code' => $this->currency->code,
                    'symbol' => $this->currency->symbol
                ];
            }),
            'timezone' => $this->when($this->relationLoaded('timezone') && $this->timezone !== null, function () {
                return [
                    'id' => $this->timezone->id,
                    'name' => $this->timezone->name,
                    'offset_utc' => $this->timezone->offset_utc,
                ];
            }, null),
            'inviter'=>$this->when($this->relationLoaded('inviter') && $this->inviter !== null,function (){
                return [
                    'id'=>$this->inviter->id,
                    'name'=>$this->inviter->name,
                    'avatar'=>$this->inviter->avatar_url
                ];
            }, null)
        ];
    }
}
