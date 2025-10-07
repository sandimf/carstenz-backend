<?php

namespace App\Http\Resources\Screening;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionnaireResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $options = collect($this->options ?? [])
            ->values()
            ->map(fn ($text, $index) => [
                'id' => $index + 1,
                'text' => $text,
            ])
            ->toArray();

        return [
            'id' => $this->id,
            'section' => $this->section,
            'question' => $this->question_text,
            'type' => $this->answer_type,
            'options' => $options,
        ];
    }
}
