<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class scheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "id"=>$this->id,
            "start_date"=> (string)$this->start_date,
            "end_date"=> (string)$this->end_date,
            "text"=>$this->details,
            "user_id"=>$this->user_id
        ];
    }
}
