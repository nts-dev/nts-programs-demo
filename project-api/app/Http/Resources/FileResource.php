<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
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
            'data' => [
                $this->id,
                $this->title,
                $this->type,
                $this->size,
                (string)$this->created_at,
                $this->uploader->name,
                $this->path
            ]
        ];
    }
}
