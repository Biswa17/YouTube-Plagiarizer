<?php

namespace App\Http\Controllers;

use App\Jobs\DownloadAudio;
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
}
