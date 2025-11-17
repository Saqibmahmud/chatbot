<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImageGereration extends Model
{
    protected $fillable = [ 'user_id','generated_prompt', 'image_path', 'original_filename', 'file_size', 'mime_type' ];

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
