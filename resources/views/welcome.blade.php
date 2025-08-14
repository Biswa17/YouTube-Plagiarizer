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
    <body class="antialiased bg-gray-100">
        <nav class="bg-white shadow-sm py-4">
            <div class="container mx-auto px-4 flex justify-between items-center">
                <a href="/" class="text-2xl font-semibold text-gray-800">YouTube Plagiarizer</a>
                {{-- Add navigation links here if needed --}}
            </div>
        </nav>

        <div class="container mx-auto mt-8 px-4 max-w-3xl">
            <h1 class="text-4xl font-extrabold text-center text-gray-900 mb-8">Process YouTube Videos</h1>

            <div class="bg-white p-8 rounded-lg shadow-lg">
                <form action="/videos" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label for="youtube_url" class="block text-gray-700 text-sm font-semibold mb-2">YouTube URL:</label>
                        <input type="url" name="youtube_url" id="youtube_url" placeholder="e.g., https://www.youtube.com/watch?v=dQw4w9WgXcQ" class="shadow-sm border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 ease-in-out" required>
                        @error('youtube_url')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200 ease-in-out">
                            Start Process
                        </button>
                    </div>
                </form>
                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-md relative mt-6" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif
            </div>

            <div class="mt-10 bg-white p-8 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Job Status</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($videos as $video)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <a href="{{ $video->youtube_url }}" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline">{{ Str::limit($video->youtube_url, 50) }}</a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $video->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">No videos submitted yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>
