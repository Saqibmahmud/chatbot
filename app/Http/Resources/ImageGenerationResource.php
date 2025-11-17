<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageGenerationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
'id'=>$this->id ,
'image_path'=>$this->image_path ?asset('storage/'.$this->image_path):null ,
'original_filename'=>$this->original_filename ,
'generated_prompt'=> $this->generated_prompt,
'file_size'=> $this->file_size,
'mime_type' => $this->mime_type,
'created_at' => $this->created_at->format('Y-m-d H:i:s'),
'updated_at' => $this->updated_at->format('Y-m-d H:i:s')

        ];
    }
}
