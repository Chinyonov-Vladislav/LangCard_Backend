<?php

namespace App\Http\Resources\V1\ProfileUserResources;

use App\Http\Resources\V1\CurrencyResources\CurrencyResource;
use App\Http\Resources\V1\LanguageResources\LanguageResource;
use App\Http\Resources\V1\TimezoneResources\TimezoneResource;
use Carbon\Carbon;
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
            'invite_code'=> $this->when(auth()->id() === $this->id, function () {
                return $this->invite_code;
            }),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'currency' => $this->whenLoaded('currency', function (){
                $currency = $this->currency;
                if($currency === null)
                {
                    return null;
                }
                return new CurrencyResource($currency);
            }),
            'timezone' => $this->whenLoaded('timezone', function (){
                $timezone = $this->timezone;
                if($timezone === null)
                {
                    return null;
                }
                return new TimezoneResource($timezone);
            }),
            'language' => $this->whenLoaded('language', function (){
                $language = $this->language;
                if($language === null)
                {
                    return null;
                }
                return new LanguageResource($language);
            }),
            'inviter'=>$this->whenLoaded('inviter', function (){
                $inviter =$this->inviter;
                if($inviter === null)
                {
                    return null;
                }
                return [
                    'id'=>$inviter->id,
                    'name'=>$inviter->name,
                    'avatar'=>$inviter->avatar_url
                ];
            }),
            'coordinates'=>$this->when(auth()->id() === $this->id || $this->hideMyCoordinates === false, function(){
                return [
                    'latitude'=>$this->latitude,
                    'longitude'=>$this->longitude,
                ];
            }),
            "updating_parameters"=>$this->when(auth()->id() === $this->id, function () {
                return [
                    'exist_job_for_updating_all_parameters' => $this->jobForDefiningAllParameters === null ? null :
                        [
                            "job_id"=> $this->jobForDefiningAllParameters->job_id,
                            "type"=>$this->jobForDefiningAllParameters->initial_data['type'],
                            "execution_date"=>Carbon::parse($this->jobForDefiningAllParameters->initial_data['execution_date'])->format('Y-m-d H:i:s'),
                            "status"=>$this->jobForDefiningAllParameters->status,
                        ],

                    'updating_timezone' => $this->buildUpdatingBlock(
                        $this->last_time_update_timezone,
                        $this->jobForDefiningTimezone
                    ),

                    'updating_language' => $this->buildUpdatingBlock(
                        $this->last_time_update_language,
                        $this->jobForDefiningLanguage
                    ),

                    'updating_currency' => $this->buildUpdatingBlock(
                        $this->last_time_update_currency,
                        $this->jobForDefiningCurrency
                    ),

                    'updating_coordinates' => $this->buildUpdatingBlock(
                        $this->last_time_update_coordinates,
                        $this->jobForDefiningCoordinates
                    ),
                ];
            }),
        ];
    }

    private function buildUpdatingBlock($lastUpdate, $jobInfo): array
    {
        $timeLastUpdate = $lastUpdate ? Carbon::parse($lastUpdate) : null;
        $nextTimeUpdate = $lastUpdate
            ? Carbon::parse($lastUpdate)->addMonths(config("app.limit_count_months_to_update_profile_data"))
            : null;
        $canUpdateByTime = $nextTimeUpdate === null || $nextTimeUpdate->isPast();

        return [
            "last_time_update" => $timeLastUpdate?->format('Y-m-d H:i:s'),
            "next_time_update" => $nextTimeUpdate?->format('Y-m-d H:i:s'),
            "can_update"       => $canUpdateByTime,
            "job"        => $jobInfo === null ? null :
                [
                    "job_id"=> $jobInfo->job_id,
                    "type"=>$jobInfo->initial_data['type'],
                    "execution_date"=>Carbon::parse($jobInfo->initial_data['execution_date'])->format('Y-m-d H:i:s'),
                    "status"=>$jobInfo->status,
                ],
        ];
    }
}
