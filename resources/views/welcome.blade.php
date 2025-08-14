<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>YouTube Plagiarizer</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            body {
                background-color: #f3f4f6;
            }
        </style>
    </head>
    <body class="antialiased">
        <div class="container mx-auto mt-10 max-w-3xl">
            <h1 class="text-3xl font-bold text-center text-gray-800">YouTube Plagiarizer</h1>

            <div class="mt-8 bg-white p-6 rounded-lg shadow-md">
                <form action="/videos" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="youtube_url" class="block text-gray-700 text-sm font-bold mb-2">YouTube URL:</label>
                        <input type="url" name="youtube_url" id="youtube_url" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        @error('youtube_url')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Start Process
                        </button>
                    </div>
                </form>
                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif
            </div>

            <div class="mt-12 bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Job Status</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">URL</th>
                                <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($videos as $video)
                                <tr>
                                    <td class="py-2 px-4 border-b border-gray-200 text-sm">
                                        <a href="{{ $video->youtube_url }}" target="_blank" class="text-blue-500 hover:underline">{{ Str::limit($video->youtube_url, 50) }}</a>
                                    </td>
                                    <td class="py-2 px-4 border-b border-gray-200 text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @switch($video->status)
                                                @case('pending') bg-yellow-100 text-yellow-800 @break
                                                @case('downloading_audio') bg-blue-100 text-blue-800 @break
                                                @case('audio_downloaded') bg-green-100 text-green-800 @break
                                                @case('failed') bg-red-100 text-red-800 @break
                                                @default bg-gray-100 text-gray-800
                                            @endswitch">
                                            {{ str_replace('_', ' ', $video->status) }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-4 border-b border-gray-200 text-sm">{{ $video->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-4 px-4 text-center text-gray-500">No videos submitted yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>
