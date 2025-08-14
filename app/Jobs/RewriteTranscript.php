<?php

namespace App\Jobs;

use App\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RewriteTranscript implements ShouldQueue
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
        if (!$this->video->transcript_path || !File::exists($this->video->transcript_path)) {
            $this->fail('Original transcript not found or is inaccessible.');
            return;
        }

        try {
            $originalTranscript = File::get($this->video->transcript_path);
            $response = $this->callGeminiApi($originalTranscript);
            

            if ($response->successful()) {
                $rewrittenTranscript = $response->json('candidates.0.content.parts.0.text');
                
                $outputPath = base_path("scripts/final_draft/{$this->video->id}.txt");
                File::ensureDirectoryExists(dirname($outputPath));
                File::put($outputPath, $rewrittenTranscript);

                $this->video->update([
                    'status' => 'rewritten',
                    'rewritten_script' => $rewrittenTranscript,
                    'rewritten_transcript_path' => $outputPath,
                    'rewritten_at' => now(),
                ]);
            } else {
                $this->video->update([
                    'status' => 'rewrite_failed',
                    'error_message' => $response->body(),
                ]);
                $this->fail('API call failed: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Rewrite job failed: ' . $e->getMessage());
            $this->video->update([
                'status' => 'rewrite_failed',
                'error_message' => $e->getMessage(),
            ]);
            $this->fail($e->getMessage());
        }
    }

    /**
     * Call the Gemini API to rewrite the transcript.
     *
     * @param string $transcript
     * @return \Illuminate\Http\Client\Response
     */
    private function callGeminiApi(string $transcript)
    {
        $apiKey = env('GEMINI_API_KEY');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";

        $prompt = "You are an expert YouTube scriptwriter for an informative channel. 
            Rewrite the following transcript into a fresh, engaging, and easy-to-follow script 
            while keeping every fact, detail, and logical flow intact.

            - Use a friendly and conversational tone that sounds like a natural voiceover.
            - Organize content into short, clear paragraphs.
            - Use simple, direct language without jargon unless it’s explained.
            - Avoid repeating phrases and remove filler words.
            - Add smooth transitions between ideas so the script flows well.
            - Keep the meaning 100% accurate to the original — do not add or remove facts.
            - Make it sound engaging enough for a YouTube audience that wants to learn something new.

            Original Text:
            ---
            {$transcript}
            ---
            Final YouTube Script:";
        return Http::timeout(180)->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
        ]);
    }
}
