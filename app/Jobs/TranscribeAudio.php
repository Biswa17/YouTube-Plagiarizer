<?php

namespace App\Jobs;

use App\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File; // Add this line

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

        // Placeholder for actual transcription logic
        // In a real application, this would call an external API or a local script
        sleep(5); // Simulate work

        $transcriptContent = 'This is a placeholder transcript for video ID: ' . $this->video->id . "\n\n" .
                             "Full transcript content would go here.";

        $outputDir = base_path('scripts/transcript');
        // Ensure the directory exists
        if (!File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }

        $transcriptFilename = $this->video->id . '.txt';
        $transcriptPath = $outputDir . '/' . $transcriptFilename;

        File::put($transcriptPath, $transcriptContent);

        $this->video->update([
            'status' => 'transcribed',
            'transcript' => $transcriptPath, // Store the file path
        ]);
    }
}
