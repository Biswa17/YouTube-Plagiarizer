<?php

namespace App\Jobs;

use App\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class TranscribeAudio implements ShouldQueue
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
        $this->video->update(['status' => 'transcribing']);

        $audioPath = base_path("scripts/audio/{$this->video->id}.mp3");
        $transcriptPath = base_path("scripts/transcriped/{$this->video->id}.txt");
        $apiKey = env('GEMINI_API_KEY');

        $command = [
            'python3',
            base_path('scripts/transcribe.py'),
            $audioPath,
            $apiKey,
            $transcriptPath,
        ];

        try {
            $result = Process::timeout(180)->run($command);

            if ($result->successful()) {
                $transcript = File::get($transcriptPath);
                $this->video->update([
                    'status' => 'completed',
                    'transcript' => $transcript,
                    'transcript_path' => $transcriptPath,
                ]);
            } else {
                $this->video->update([
                    'status' => 'failed',
                    'error_message' => $result->errorOutput(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Transcription job failed: ' . $e->getMessage());
            $this->video->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
