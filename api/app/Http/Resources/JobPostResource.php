<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobPostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
public function toArray(Request $request): array
{
    return [
        'id'           => $this->id,
        'title'        => $this->title,
        'company_name' => $this->company_name,
        'description'  => $this->description,
        'location'     => $this->location,
        'type'         => $this->type,
        'salary_min'   => $this->salary_min,
        'salary_max'   => $this->salary_max,
        'status'       => $this->status,
        'employer'     => $this->whenLoaded('employer', fn () => [
            'id'   => $this->employer->id,
            'name' => $this->employer->name,
        ]),
        'created_at'   => $this->created_at->toIso8601String(),
    ];
}
}
