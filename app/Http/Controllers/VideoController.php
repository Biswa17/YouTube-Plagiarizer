<?php

namespace App\Http\Controllers;

use App\Jobs\DownloadAudio;
use App\Jobs\RewriteTranscript;
use App\Jobs\TranscribeAudio;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

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
            'youtube_url' => ['required', 'url', 'regex:/^(https?\:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+/'],
        ], [
            'youtube_url.regex' => 'Please enter a valid YouTube URL.',
        ]);

        // Check if URL already exists
        $existingVideo = Video::where('youtube_url', $request->youtube_url)->first();
        if ($existingVideo) {
            return redirect('/')->with('error', 'This video has already been submitted!');
        }

        $video = Video::create([
            'youtube_url' => $request->youtube_url,
        ]);

        DownloadAudio::dispatch($video);

        return redirect('/')->with('success', 'Your video is being processed! 🚀');
    }

    public function transcribe(Video $video)
    {
        // Ensure audio is downloaded before transcribing
        if ($video->status !== 'audio_downloaded') {
            return redirect()->back()->with('error', 'Audio must be downloaded before transcription can begin.');
        }

        // Update status to transcribing
        $video->update(['status' => 'transcribing']);

        // Dispatch the transcription job
        TranscribeAudio::dispatch($video);

        return redirect()->back()->with('success', 'Transcription process started! ⚡');
    }

    public function rewrite(Video $video)
    {
        if (!$video->transcript_path) {
            return redirect()->back()->with('error', 'An original transcript is required before rewriting.');
        }

        $video->update(['status' => 'rewriting']);

        RewriteTranscript::dispatch($video);

        return redirect()->back()->with('success', 'Rewrite process started! ✍️');
    }

    public function view(Video $video)
    {
        if ($video->status !== 'completed' || !$video->transcript_path) {
            return redirect()->back()->with('error', 'Transcript is not available yet.');
        }

        if (!File::exists($video->transcript_path)) {
            return redirect()->back()->with('error', 'Transcript file not found.');
        }

        $transcript = File::get($video->transcript_path);
        
        return view('transcript.view', compact('video', 'transcript'));
    }

    public function viewFinal(Video $video)
    {
        if ($video->status !== 'rewritten' || !$video->rewritten_transcript_path) {
            return redirect()->back()->with('error', 'Rewritten transcript is not available yet.');
        }

        if (!File::exists($video->rewritten_transcript_path)) {
            return redirect()->back()->with('error', 'Rewritten transcript file not found.');
        }

        $transcript = File::get($video->rewritten_transcript_path);
        
        return view('transcript.view_final', compact('video', 'transcript'));
    }

    public function download(Video $video)
    {
        if ($video->status !== 'completed' || !$video->transcript_path) {
            return redirect()->back()->with('error', 'Transcript is not available for download.');
        }

        if (!File::exists($video->transcript_path)) {
            return redirect()->back()->with('error', 'Transcript file not found.');
        }

        $filename = 'transcript_' . $video->id . '_' . date('Y-m-d_H-i-s') . '.txt';
        
        return response()->download($video->transcript_path, $filename);
    }

    public function delete(Video $video)
    {
        // Delete associated files
        if ($video->audio_path && File::exists($video->audio_path)) {
            File::delete($video->audio_path);
        }
        
        if ($video->transcript_path && File::exists($video->transcript_path)) {
            File::delete($video->transcript_path);
        }

        $video->delete();

        return redirect('/')->with('success', 'Video and associated files deleted successfully! 🗑️');
    }
}
