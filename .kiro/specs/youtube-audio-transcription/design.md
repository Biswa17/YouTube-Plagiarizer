# Design Document

## Overview

The YouTube Audio Transcription feature will be implemented as a Laravel web application using Blade templating for the frontend. The system will integrate with external services for YouTube video processing and speech-to-text conversion. The architecture follows Laravel's MVC pattern with additional service classes for handling complex operations.

Key external dependencies:
- **yt-dlp**: Python tool for downloading YouTube videos and extracting audio
- **Google Gemini API**: For speech-to-text transcription using Gemini's multimodal capabilities

## Architecture

### High-Level Flow
1. User submits YouTube URL via web form
2. Laravel controller validates URL and processes request synchronously
3. System downloads video and extracts audio using yt-dlp
4. Audio file is sent to Google Gemini API for transcription
5. Results are stored in database and displayed to user
6. User can view, copy, or download transcription

### System Components
```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   Blade Views   │────│ Laravel Routes   │────│  Controllers    │
└─────────────────┘    └──────────────────┘    └─────────────────┘
                                                         │
                                                         ▼
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   Database      │────│     Models       │────│    Services     │
└─────────────────┘    └──────────────────┘    └─────────────────┘
                                                         │
                                                         ▼
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│  File Storage   │────│     yt-dlp       │────│  Gemini API     │
└─────────────────┘    └──────────────────┘    └─────────────────┘
```

## Components and Interfaces

### Models

#### TranscriptionJob Model
```php
class TranscriptionJob extends Model
{
    protected $fillable = [
        'youtube_url',
        'original_title',
        'audio_file_path',
        'transcription_text',
        'file_size',
        'duration',
        'status',
        'error_message'
    ];
    
    // Status constants: completed, failed
}
```

### Controllers

#### TranscriptionController
- `index()`: Display main form
- `store()`: Handle URL submission and process transcription
- `show($id)`: Display transcription results
- `download($id)`: Download transcription as text file

### Services

#### YouTubeAudioService
- `extractAudio(string $url): string`: Download video and extract audio
- `validateUrl(string $url): bool`: Validate YouTube URL format
- `cleanup(string $filePath): void`: Remove temporary files

#### GeminiTranscriptionService  
- `transcribe(string $audioFilePath): string`: Send audio to Gemini API for transcription
- `formatTranscription(string $text): string`: Format text for display
- `prepareAudioForGemini(string $filePath): string`: Convert audio to base64 for API



### Routes
```php
Route::get('/', [TranscriptionController::class, 'index'])->name('home');
Route::post('/transcribe', [TranscriptionController::class, 'store'])->name('transcribe.store');
Route::get('/transcription/{id}', [TranscriptionController::class, 'show'])->name('transcription.show');
Route::get('/transcription/{id}/download', [TranscriptionController::class, 'download'])->name('transcription.download');
```

## Data Models

### Database Schema

#### transcription_jobs table
```sql
CREATE TABLE transcription_jobs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    youtube_url VARCHAR(500) NOT NULL,
    original_title VARCHAR(255) NULL,
    audio_file_path VARCHAR(255) NULL,
    transcription_text LONGTEXT NULL,
    file_size BIGINT NULL,
    duration INTEGER NULL,
    status ENUM('completed', 'failed') NOT NULL,
    error_message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Error Handling

### Error Categories
1. **Validation Errors**: Invalid YouTube URLs, empty submissions
2. **Download Errors**: Video unavailable, network issues, restricted content
3. **Audio Extraction Errors**: Corrupted video, unsupported formats
4. **Transcription Errors**: Gemini API failures, audio quality issues, quota limits, unsupported audio formats
5. **System Errors**: Storage issues, yt-dlp installation problems

### Error Response Strategy
- User-friendly error messages displayed in Blade views
- Detailed error logging for debugging
- Graceful degradation with retry mechanisms
- Cleanup of temporary files on errors

## Testing Strategy

### Unit Tests
- Model validation and relationships
- Service class methods (mocked external dependencies)
- URL validation logic
- Text formatting functions

### Feature Tests
- Complete transcription workflow (with mocked external services)
- Form submission and validation
- File download functionality
- Error handling scenarios

### Integration Tests
- Database interactions
- File storage operations
- yt-dlp integration
- Gemini API integration

### Manual Testing Scenarios
- Various YouTube URL formats
- Different video lengths and audio qualities
- Network failure simulation
- Concurrent job processing

## Security Considerations

### Input Validation
- Strict YouTube URL format validation
- Sanitization of user inputs
- File type validation for uploaded content

### File Security
- Temporary file cleanup after processing
- Secure file storage with proper permissions
- Prevention of directory traversal attacks

## Performance Considerations

### Optimization Strategies
- Temporary file cleanup to manage storage
- Database indexing on frequently queried fields
- Efficient file serving for downloads

### Scalability
- File storage can be moved to cloud storage (S3)
- Database can be optimized with proper indexing
- Rate limiting to prevent abuse
#
# Google Gemini API Integration

### Configuration
- Store Gemini API key in `.env` file as `GEMINI_API_KEY`
- Use Google's generative AI client library for PHP
- Configure appropriate model (gemini-1.5-flash or gemini-1.5-pro)

### Audio Processing
- Gemini API accepts audio files in various formats (MP3, WAV, FLAC, etc.)
- Audio files need to be base64 encoded for API submission
- Maximum file size limits apply (check current Gemini API documentation)

### API Request Structure
```php
// Example API request structure
$request = [
    'contents' => [
        [
            'parts' => [
                [
                    'text' => 'Please transcribe this audio file:'
                ],
                [
                    'inline_data' => [
                        'mime_type' => 'audio/mp3',
                        'data' => base64_encode($audioContent)
                    ]
                ]
            ]
        ]
    ]
];
```

### Rate Limiting and Error Handling
- Implement exponential backoff for rate limit errors
- Handle quota exceeded errors gracefully
- Provide fallback error messages for API failures