<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranscriptionJob extends Model
{
    /**
     * Status constants
     */
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'youtube_url',
        'original_title',
        'audio_file_path',
        'transcription_text',
        'file_size',
        'duration',
        'status',
        'error_message',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'file_size' => 'integer',
        'duration' => 'integer',
    ];
}
