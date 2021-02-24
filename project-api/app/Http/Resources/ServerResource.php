<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'domain' => $this->domain,
            'token' => $this->token,
            'path' => $this->path,
//            'location' => $this->location,
            'is_moodle' => $this->is_moodle
        ];
    }
}
