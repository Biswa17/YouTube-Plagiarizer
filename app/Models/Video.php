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
        'transcript_path',
        'rewritten_script',
        'rewritten_transcript_path',
        'rewritten_at',
        'final_audio_path',
        'error_message',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
