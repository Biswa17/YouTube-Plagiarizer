<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class GenerateImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate images using the Gemini API and save them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating images...');

        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            $this->error('GEMINI_API_KEY is not set in your .env file.');
            return 1;
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/imagen-4.0-generate-001:predict";

        $response = Http::withHeaders([
            'x-goog-api-key' => $apiKey,
            'Content-Type' => 'application/json',
        ])->post($url, [
            'instances' => [
                ['prompt' => 'A cartoon-style illustration, hand-drawn with deliberately clumsy “bad computer graphics” aesthetics — low-poly feel, exaggerated proportions, mismatched colors, visible pixel edges, and humorous imperfections. Scene: my custom cartoon avatar (describe avatar’s appearance — hair, clothing, expression, etc.) looking amazed at a giant snail. The snail is drawn with thousands of tiny comic-style teeth shown in ridiculous detail, with an oversized, slightly wobbly shell. The background has a simple, flat-colored meadow with awkwardly drawn flowers. Overall style: whimsical, funny, with hand-drawn lines and amateur retro-digital shading, evoking early 90s PC clipart mixed with a child’s coloring book.']
            ],
            'parameters' => [
                'sampleCount' => 4
            ]
        ]);

        if ($response->failed()) {
            $this->error('Failed to generate images. API response:');
            $this->line($response->body());
            return 1;
        }

        $predictions = $response->json('predictions');

        if (empty($predictions)) {
            $this->error('No images were returned in the API response.');
            return 1;
        }

        $outputDir = base_path('scripts/image/1');
        File::ensureDirectoryExists($outputDir);

        foreach ($predictions as $index => $prediction) {
            if (isset($prediction['bytesBase64Encoded'])) {
                $imageData = base64_decode($prediction['bytesBase64Encoded']);
                $fileName = ($index + 1) . '.png';
                $filePath = $outputDir . '/' . $fileName;
                File::put($filePath, $imageData);
                $this->info("Image saved: {$filePath}");
            }
        }

        $this->info('Image generation complete.');
        return 0;
    }
}
