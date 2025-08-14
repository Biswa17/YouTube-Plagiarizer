<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'youtube_url',
        'status',
        'audio_path',
        'transcript',
        'rewritten_script',
        'final_audio_path',
        'error_message',
    ];
}
