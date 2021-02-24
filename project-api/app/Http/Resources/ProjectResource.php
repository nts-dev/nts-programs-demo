<?php

namespace App\Http\Resources;

use App\Project;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        //transforms the resource into an array made up of the attributes to be converted to JSON
        return [
            'id' => $this->id,
            'project_id' => Project::generateProjectId($this->id),
            'title' => $this->title,
            'goal' => $this->goal,
            'input' => $this->input,
            'output' => $this->output,
            'created_by' => $this->creator->name,
            'is_published' => $this->is_published
        ];
    }

}
