<?php

namespace Tests\Unit;

use App\Services\YouTubeAudioService;
use Exception;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\TestCase;

class YouTubeAudioServiceTest extends TestCase
{
    private YouTubeAudioService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new YouTubeAudioService();
    }

    /** @test */
    public function it_validates_correct_youtube_urls()
    {
        $validUrls = [
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'https://youtube.com/watch?v=dQw4w9WgXcQ',
            'http://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'https://youtu.be/dQw4w9WgXcQ',
            'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'https://www.youtube.com/v/dQw4w9WgXcQ',
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=30s',
        ];

        foreach ($validUrls as $url) {
            $this->assertTrue(
                $this->service->validateUrl($url),
                "URL should be valid: {$url}"
            );
        }
    }

    /** @test */
    public function it_rejects_invalid_youtube_urls()
    {
        $invalidUrls = [
            'https://www.google.com',
            'https://vimeo.com/123456789',
            'not-a-url',
            'https://www.youtube.com/watch?v=invalid',
            'https://www.youtube.com/watch?v=',
            'https://youtu.be/',
            '',
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ123', // too long
        ];

        foreach ($invalidUrls as $url) {
            $this->assertFalse(
                $this->service->validateUrl($url),
                "URL should be invalid: {$url}"
            );
        }
    }

    /** @test */
    public function it_throws_exception_for_invalid_url_in_extract_audio()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid YouTube URL provided');

        $this->service->extractAudio('https://www.google.com');
    }

    /** @test */
    public function it_throws_exception_when_yt_dlp_not_available()
    {
        // Mock shell_exec to simulate yt-dlp not being available
        $service = $this->getMockBuilder(YouTubeAudioService::class)
            ->onlyMethods(['checkYtDlpAvailability'])
            ->getMock();

        $service->method('checkYtDlpAvailability')
            ->willReturn(false);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('yt-dlp is not installed or not available in PATH');

        // Use reflection to call the private method or modify the service to make it testable
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('extractAudio');
        $method->invoke($service, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');
    }

    /** @test */
    public function it_throws_exception_for_invalid_url_in_get_video_info()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid YouTube URL provided');

        $this->service->getVideoInfo('https://www.google.com');
    }

    /** @test */
    public function cleanup_removes_existing_file()
    {
        // Create a temporary test file
        $tempFile = storage_path('app/temp/test_file.mp3');
        
        // Ensure temp directory exists
        $tempDir = dirname($tempFile);
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        file_put_contents($tempFile, 'test content');
        
        $this->assertTrue(file_exists($tempFile));
        
        $this->service->cleanup($tempFile);
        
        $this->assertFalse(file_exists($tempFile));
    }

    /** @test */
    public function cleanup_handles_non_existent_file_gracefully()
    {
        $nonExistentFile = storage_path('app/temp/non_existent_file.mp3');
        
        // Should not throw an exception
        $this->service->cleanup($nonExistentFile);
        
        // Test passes if no exception is thrown
        $this->assertTrue(true);
    }

    /** @test */
    public function it_creates_temp_directory_on_construction()
    {
        $tempDirectory = storage_path('app/temp');
        
        // Remove directory if it exists
        if (is_dir($tempDirectory)) {
            rmdir($tempDirectory);
        }
        
        $this->assertFalse(is_dir($tempDirectory));
        
        // Create new service instance
        new YouTubeAudioService();
        
        $this->assertTrue(is_dir($tempDirectory));
    }

    protected function tearDown(): void
    {
        // Clean up any test files
        $tempDir = storage_path('app/temp');
        if (is_dir($tempDir)) {
            $files = glob($tempDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        
        parent::tearDown();
    }
}