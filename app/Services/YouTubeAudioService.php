<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class YouTubeAudioService
{
    private string $tempDirectory;

    public function __construct()
    {
        $this->tempDirectory = storage_path('app/temp');
        
        // Ensure temp directory exists
        if (!is_dir($this->tempDirectory)) {
            mkdir($this->tempDirectory, 0755, true);
        }
    }

    /**
     * Validate if the provided URL is a valid YouTube URL
     */
    public function validateUrl(string $url): bool
    {
        $pattern = '/^(https?:\/\/)?(www\.)?(youtube\.com\/(watch\?v=|embed\/|v\/)|youtu\.be\/)[\w\-_]{11}(&[\w=]*)?$/';
        return preg_match($pattern, $url) === 1;
    }

    /**
     * Extract audio from YouTube video using yt-dlp
     */
    public function extractAudio(string $url): string
    {
        if (!$this->validateUrl($url)) {
            throw new Exception('Invalid YouTube URL provided');
        }

        // Check if yt-dlp is available
        $ytDlpCheck = shell_exec('which yt-dlp 2>/dev/null');
        if (empty($ytDlpCheck)) {
            throw new Exception('yt-dlp is not installed or not available in PATH');
        }

        $outputFilename = 'audio_' . uniqid() . '.%(ext)s';
        $outputPath = $this->tempDirectory . '/' . $outputFilename;

        // Build yt-dlp command
        $command = sprintf(
            'yt-dlp --extract-audio --audio-format mp3 --audio-quality 0 -o %s %s 2>&1',
            escapeshellarg($outputPath),
            escapeshellarg($url)
        );

        Log::info('Executing yt-dlp command', ['command' => $command]);

        $output = shell_exec($command);
        
        if ($output === null) {
            throw new Exception('Failed to execute yt-dlp command');
        }

        // Find the actual output file (yt-dlp replaces %(ext)s with actual extension)
        $actualFilePath = str_replace('.%(ext)s', '.mp3', $outputPath);
        
        if (!file_exists($actualFilePath)) {
            Log::error('yt-dlp output', ['output' => $output]);
            throw new Exception('Audio extraction failed: ' . $output);
        }

        Log::info('Audio extraction successful', ['file_path' => $actualFilePath]);
        
        return $actualFilePath;
    }

    /**
     * Clean up temporary files
     */
    public function cleanup(string $filePath): void
    {
        if (file_exists($filePath)) {
            unlink($filePath);
            Log::info('Cleaned up temporary file', ['file_path' => $filePath]);
        }
    }

    /**
     * Get video information without downloading
     */
    public function getVideoInfo(string $url): array
    {
        if (!$this->validateUrl($url)) {
            throw new Exception('Invalid YouTube URL provided');
        }

        $command = sprintf(
            'yt-dlp --dump-json --no-download %s 2>&1',
            escapeshellarg($url)
        );

        $output = shell_exec($command);
        
        if ($output === null) {
            throw new Exception('Failed to get video information');
        }

        $videoInfo = json_decode($output, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed to parse video information: ' . $output);
        }

        return [
            'title' => $videoInfo['title'] ?? 'Unknown Title',
            'duration' => $videoInfo['duration'] ?? 0,
            'uploader' => $videoInfo['uploader'] ?? 'Unknown',
        ];
    }
}