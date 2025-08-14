# Project: YouTube Plagiarizer - Technical Plan

This document outlines the technical implementation plan for the YouTube Plagiarizer, a Laravel-based application that automates the process of transforming a YouTube video into a new, AI-generated video.

## 🚀 **High-Level Goal**

The application will take a YouTube URL as input and produce a new video file with a rewritten script and AI-generated voiceover. The process is managed through a Laravel backend, with specialized tasks delegated to Python scripts.

## **Milestones & Phases**

The project is broken down into the following milestones:

### **📌 Milestone 1: Laravel Base Setup & Core Functionality**

-   **Goal:** Create a functional web interface to accept a YouTube URL and manage the processing pipeline.
-   **Phase 1.1: Environment & Database Setup**
    -   Configure Laravel `.env` for local MySQL database and queue driver.
    -   Create the `videos` table migration to store all job-related data.
-   **Phase 1.2: Backend Logic**
    -   Implement a `DownloadAudio` job to handle asynchronous downloading.
    -   Create a Python script (`scripts/download_audio.py`) using `yt-dlp`.
-   **Phase 1.3: Frontend**
    -   Develop a simple Blade view with a form for URL submission.
    -   Display a status table of all submitted jobs.

### **📌 Milestone 2: Audio Transcription**

-   **Goal:** Transcribe the downloaded audio file into text.
-   **Technology:** `faster-whisper` (local) or OpenAI Whisper API.
-   **Implementation:**
    -   Create a `TranscribeAudio` job in Laravel.
    -   Develop a Python script (`scripts/transcribe.py`) that takes an audio file path and outputs the transcript.
    -   Store the resulting transcript in the `videos` table.

### **📌 Milestone 3: AI Script Rewriting**

-   **Goal:** Rewrite the transcript to be unique while preserving the original meaning.
-   **Technology:** GPT series (OpenAI API) or a local model like LLaMA.
-   **Implementation:**
    -   Create a `RewriteScript` job.
    -   Develop a Python script (`scripts/rewrite.py`) that sends the transcript to an AI model with a specialized prompt.
    -   The script will perform N-pass rewriting for thoroughness.
    -   Store the final script in the `videos` table.

### **📌 Milestone 4: Text-to-Speech (TTS) Generation**

-   **Goal:** Convert the rewritten script into a natural-sounding audio file.
-   **Technology:** ElevenLabs API or Coqui TTS (local).
-   **Implementation:**
    -   Create a `GenerateSpeech` job.
    -   Develop a Python script (`scripts/tts.py`) to handle the API call or local model inference.
    -   Save the generated `.mp3` file to `storage/app/final_audio/`.
    -   Store the file path in the `videos` table.

### **📌 Milestone 5: Video Assembly (Future Scope)**

-   **Goal:** Combine the final audio with visuals to create a video file.
-   **Technology:** `ffmpeg` or `moviepy`.
-   **Implementation:**
    -   Create a `AssembleVideo` job.
    -   Develop a Python script (`scripts/make_video.py`) to:
        -   Overlay the rewritten script as subtitles.
        -   Use stock footage or the original video's visuals as a backdrop.
        -   Combine with the final audio track.
        -   Output a final `.mp4` file.

## **💡 Core Tech Stack**

-   **Backend:** Laravel 11
-   **Frontend:** Laravel Blade
-   **Database:** MySQL
-   **Queue:** Laravel Queues (Database Driver)
-   **Python 3:** For all media and AI processing.
-   **Key Python Libraries:** `yt-dlp`, `faster-whisper`, `openai`, `requests` (for APIs), `moviepy`.
