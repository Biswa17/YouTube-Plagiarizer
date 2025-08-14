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

        $prompt = "Rewrite the following text. Your goal is to rephrase the content, freshen up the language, and change the way the facts are presented, but you must keep the original meaning, logic, and all factual details completely intact. Do not add new information or remove any details. Format the output as clean, readable paragraphs.\n\nOriginal Text:\n---\n{$transcript}\n---\nRewritten Text:";

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
