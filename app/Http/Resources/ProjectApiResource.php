<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class ProjectApiResource extends JsonResource
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
            'name' => $this->name,
            'share' => $this->shareUrl(),
            'created_at' => $this->created_at ? Carbon::parse($this->created_at)->toAtomString() : null,
            'updated_at' => $this->updated_at ? Carbon::parse($this->updated_at)->toAtomString() : null,
            'groups' => TimelineGroupApiResource::collection($this->whenLoaded('groups')),
        ];
    }
}
