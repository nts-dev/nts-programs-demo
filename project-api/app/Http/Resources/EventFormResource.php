<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EventFormResource extends JsonResource
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
            'id' => $this->id,
            'title' => $this->title,
            'details' => $this->details,
            'start_date' => (string)$this->start_date->toDateString(),
            'begin_time' => (string)$this->start_date->toTimeString(),
            'end_date' => (string)$this->end_date->toDateString(),
            'end_time' => (string)$this->end_date->toTimeString(),
            'is_variable' => $this->is_variable,
            'comments' => $this->comments,
            'frequency' => $this->frequency
        ];
    }
}
