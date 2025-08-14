# Plan: Milestone 1 - Laravel Base Setup & Core Functionality

This document details the specific steps required to complete the first milestone: setting up the Laravel application and implementing the core feature of downloading audio from a YouTube URL.

---

### **Phase 1.1: Environment & Database Setup**

1.  **Configure Environment (`.env`):**
    *   **Action:** Modify the `.env` file.
    *   **Change:** Set `QUEUE_CONNECTION=database`.
    *   **Purpose:** To enable the database-driven queue system for handling background jobs. This prevents the UI from freezing during long tasks.
    *   **Verification:** I will ask you to confirm your local MySQL database credentials (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) are correctly set up in this file.

2.  **Create `Video` Model & Migration:**
    *   **Action:** Run the command `php artisan make:model Video -m`.
    *   **Purpose:** This will generate two files: `app/Models/Video.php` (the Eloquent model) and a new migration file in `database/migrations/`.

3.  **Define `videos` Table Schema:**
    *   **Action:** Edit the newly created migration file.
    *   **Purpose:** To define the structure of the `videos` table, which will track the state of each job.
    *   **Schema:**
        ```php
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('youtube_url');
            $table->string('status')->default('pending');
            $table->string('audio_path')->nullable();
            $table->text('transcript')->nullable();
            $table->text('rewritten_script')->nullable();
            $table->string('final_audio_path')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
        ```

4.  **Run Database Migrations:**
    *   **Action:** Run the command `php artisan migrate`.
    *   **Purpose:** To create the `users`, `jobs`, and our new `videos` tables in the database.

---

### **Phase 1.2: Backend Logic**

1.  **Create Python Script Directory:**
    *   **Action:** Create a new directory named `scripts` in the project root.

2.  **Create `download_audio.py`:**
    *   **Action:** Create a new file `scripts/download_audio.py`.
    *   **Purpose:** This script will be called by Laravel to perform the download.
    *   **Functionality:**
        *   Accepts `--url` and `--output-path` as command-line arguments.
        *   Uses `yt-dlp` to download audio from the URL.
        *   Saves the file to the specified path (e.g., `storage/app/public/youtube_audio/{id}.mp3`).
        *   Prints the full path of the saved file to standard output on success.

3.  **Create `DownloadAudio` Job:**
    *   **Action:** Run `php artisan make:job DownloadAudio`.
    *   **Purpose:** To encapsulate the download logic in an asynchronous, queued job.
    *   **Functionality:**
        *   The job will accept a `Video` model in its constructor.
        *   The `handle()` method will execute the `download_audio.py` script using Laravel's `Process` facade.
        *   It will update the video's `status` to `downloading_audio` before starting, and to `audio_downloaded` on success or `failed` on error.
        *   On success, it will store the file path returned by the script in the `videos` table.

---

### **Phase 1.3: Frontend**

1.  **Create `VideoController`:**
    *   **Action:** Run `php artisan make:controller VideoController`.

2.  **Define Routes (`routes/web.php`):**
    *   **Action:** Add routes to `routes/web.php`.
    *   **Routes:**
        *   `GET /` -> `VideoController@index` (to show the main page).
        *   `POST /videos` -> `VideoController@store` (to handle form submission).

3.  **Implement `VideoController` Logic:**
    *   `index()`: Fetches all videos from the database and passes them to the view.
    *   `store()`: Validates the YouTube URL, creates a new `Video` record, and dispatches the `DownloadAudio` job.

4.  **Create Blade View (`resources/views/welcome.blade.php`):**
    *   **Action:** Modify the main welcome view.
    *   **Content:**
        *   An HTML form to submit a YouTube URL.
        *   A table to display the `youtube_url` and `status` of all submitted videos.
