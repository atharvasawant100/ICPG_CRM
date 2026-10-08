<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company' => $this->company,
            'source' => $this->source,
            'status' => $this->status,
            'department_id' => $this->department_id,
            'department' => $this->whenLoaded('department', function () {
                return [
                    'id' => $this->department->id,
                    'name' => $this->department->name
                ];
            }),
            'created_by' => $this->created_by,
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name
                ];
            }),
            'converted_to_customer_id' => $this->converted_to_customer_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'notes_count' => $this->notes_count ?? $this->notes()->count(),
            'notes' => $this->whenLoaded('notes'), // Assuming NoteResource if needed, but array for now
            'followups' => $this->whenLoaded('followups'),
        ];
    }
}
