<?php

namespace App\Jobs;

use App\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class DownloadAudio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Video $video)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->video->update(['status' => 'downloading_audio']);

        // Ensure the directory exists
        Storage::makeDirectory('public/youtube_audio');

        $outputPath = Storage::path('public/youtube_audio/' . $this->video->id);

        $result = Process::run([
            sys_get_temp_dir() . '/../../bin/python', // Adjust this path if your python executable is elsewhere
            base_path('scripts/download_audio.py'),
            '--url',
            $this->video->youtube_url,
            '--output-path',
            $outputPath,
        ]);

        if ($result->successful()) {
            $this->video->update([
                'status' => 'audio_downloaded',
                'audio_path' => trim($result->output()),
            ]);
        } else {
            $this->video->update([
                'status' => 'failed',
                'error_message' => $result->errorOutput(),
            ]);
        }
    }
}
