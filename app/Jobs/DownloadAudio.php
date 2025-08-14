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

        $outputDir = base_path('scripts/audio');

        // Ensure the directory exists
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $outputPath = $outputDir . '/' . $this->video->id;

        $result = Process::run([
            'python3',
            base_path('scripts/download_audio.py'),
            '--url',
            $this->video->youtube_url,
            '--output-path',
            $outputPath,
        ]);

        $expected_file = $outputPath . '.mp3';

        if ($result->successful()) {
            if (file_exists($expected_file) && filesize($expected_file) > 0) {
                $this->video->update([
                    'status' => 'audio_downloaded',
                    'audio_path' => $expected_file,
                ]);
            } else {
                $this->video->update([
                    'status' => 'failed',
                    'error_message' => 'Audio file not created or is empty.',
                ]);
            }
        } else {
            $this->video->update([
                'status' => 'failed',
                'error_message' => $result->errorOutput(),
            ]);
        }
    }
}
