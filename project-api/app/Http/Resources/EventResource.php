<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $assigned = array();
        foreach ($this->users as $user) {
            $assigned[] = $user->name;
        }
        return [
            'id' => $this->id,
            'data' => [
                $this->id,
                $this->details,
                $this->creator->name,
                implode(", ", $assigned),
                (string)$this->start_date,
                (string)$this->end_date,
                $this->is_visible,
                $this->status
            ]
        ];
    }
}
