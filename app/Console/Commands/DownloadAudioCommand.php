<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class DownloadAudioCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audio:download-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Downloads a test audio from YouTube to verify the download script.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting test audio download...");

        $url = "https://www.youtube.com/watch?v=ftZTNhJOXaI";
        $outputDir = base_path('scripts/audio');
        $outputFilename = "test_download_command";
        $outputPath = $outputDir . '/' . $outputFilename;
        $expectedFile = $outputPath . ".mp3";

        // Ensure the directory exists
        File::makeDirectory($outputDir, 0755, true, true);

        $this->info("Downloading audio from: {$url}");
        $this->info("Saving to: {$expectedFile}");

        $result = Process::run([
            'python3',
            base_path('scripts/download_audio.py'),
            '--url',
            $url,
            '--output-path',
            $outputPath,
        ]);

        if ($result->successful()) {
            $this->info("Download script executed successfully.");
            if (file_exists($expectedFile) && filesize($expectedFile) > 0) {
                $this->info("\nSUCCESS: Audio file was downloaded successfully to {$expectedFile}");
            } else {
                $this->error("\nFAILURE: Audio file was not created or is empty.");
            }
        } else {
            $this->error("Download script failed.");
            $this->error($result->errorOutput());
        }

        return 0;
    }
}
