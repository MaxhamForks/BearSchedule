<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class TimelineGroupApiResource extends JsonResource
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
            'external_id' => $this->external_id,
            'title' => $this->title,
            'content' => $this->content,
            'created_at' => $this->created_at ? Carbon::parse($this->created_at)->toAtomString() : null,
            'updated_at' => $this->updated_at ? Carbon::parse($this->updated_at)->toAtomString() : null,
            'items' => TimelineItemApiResource::collection($this->whenLoaded('items')),
        ];
    }
}
