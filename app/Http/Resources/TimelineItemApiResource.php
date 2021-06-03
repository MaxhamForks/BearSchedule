<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class TimelineItemApiResource extends JsonResource
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
            'content' => $this->content,
            'start' => $this->start,
            'end' => $this->end,
            'description' => $this->description,
            'subtitle' => $this->subtitle,
            'status' => $this->status,
            'created_at' => $this->created_at ? Carbon::parse($this->created_at)->toAtomString() : null,
            'updated_at' => $this->updated_at ? Carbon::parse($this->updated_at)->toAtomString() : null,
        ];
    }
}
