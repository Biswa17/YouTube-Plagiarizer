<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rewritten Transcript - {{ $video->youtube_url }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="antialiased bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold text-gray-800">Rewritten Transcript</h1>
                <a href="{{ route('home') }}" class="text-blue-500 hover:underline">Back to Dashboard</a>
            </div>
            <div class="mb-4">
                <p class="text-gray-600"><strong>YouTube URL:</strong> <a href="{{ $video->youtube_url }}" target="_blank" class="text-blue-500 hover:underline">{{ $video->youtube_url }}</a></p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <pre class="whitespace-pre-wrap text-gray-700">{{ $transcript }}</pre>
            </div>
        </div>
    </div>
</body>
</html>
