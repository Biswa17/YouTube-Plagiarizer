<?php

namespace App\Http\Controllers;

use App\Jobs\DownloadAudio;
use App\Jobs\TranscribeAudio; // Add this line
use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function index()
    {
        $videos = Video::latest()->get();
        return view('welcome', compact('videos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'youtube_url' => ['required', 'url'],
        ]);

        $video = Video::create([
            'youtube_url' => $request->youtube_url,
        ]);

        DownloadAudio::dispatch($video);

        return redirect('/')->with('success', 'Your video is being processed!');
    }

    public function transcribe(Video $video)
    {
        // Ensure audio is downloaded before transcribing
        if ($video->status !== 'audio_downloaded') {
            return redirect()->back()->with('error', 'Audio must be downloaded before transcription can begin.');
        }

        // Dispatch the transcription job
        TranscribeAudio::dispatch($video);

        return redirect()->back()->with('success', 'Transcription process started!');
    }
}
