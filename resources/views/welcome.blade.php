<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>YouTube Plazarizer - Transform Videos to Text</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .pulse-animation {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .status-pending { @apply bg-yellow-100 text-yellow-800 border-yellow-200; }
        .status-downloading_audio { @apply bg-blue-100 text-blue-800 border-blue-200; }
        .status-audio_downloaded { @apply bg-green-100 text-green-800 border-green-200; }
        .status-transcribing { @apply bg-purple-100 text-purple-800 border-purple-200; }
        .status-completed { @apply bg-emerald-100 text-emerald-800 border-emerald-200; }
        .status-failed { @apply bg-red-100 text-red-800 border-red-200; }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>
<body class="antialiased">
    <!-- Navigation -->
    <nav class="glass-effect sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-play text-white text-lg"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-white">YouTube Plazarizer</h1>
                </div>
                <div class="hidden md:flex items-center space-x-6 text-white/80">
                    <a href="#" class="hover:text-white transition-colors">Dashboard</a>
                    <a href="#" class="hover:text-white transition-colors">History</a>
                    <a href="#" class="hover:text-white transition-colors">Settings</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center mb-12">
                <div class="float-animation inline-block mb-6">
                    <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                        <i class="fas fa-video text-white text-3xl"></i>
                    </div>
                </div>
                <h2 class="text-5xl font-extrabold text-white mb-4">
                    Transform YouTube Videos
                    <span class="block text-white/80">Into Perfect Transcripts</span>
                </h2>
                <p class="text-xl text-white/70 max-w-2xl mx-auto">
                    Extract audio, generate transcripts, and unlock the power of video content with our AI-powered platform.
                </p>
            </div>

            <!-- Upload Form -->
            <div class="max-w-2xl mx-auto mb-12" x-data="{ isProcessing: false, url: '' }">
                <div class="glass-effect rounded-2xl p-8 card-hover">
                    <form action="/videos" method="POST" @submit="isProcessing = true" class="space-y-6">
                        @csrf
                        <div>
                            <label for="youtube_url" class="block text-white font-semibold mb-3 text-lg">
                                <i class="fas fa-link mr-2"></i>YouTube URL
                            </label>
                            <div class="relative">
                                <input 
                                    type="url" 
                                    name="youtube_url" 
                                    id="youtube_url" 
                                    x-model="url"
                                    placeholder="https://www.youtube.com/watch?v=..." 
                                    class="w-full px-4 py-4 bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-transparent transition-all duration-200 backdrop-blur-sm text-lg"
                                    required
                                >
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                                    <i class="fas fa-paste text-white/50 cursor-pointer hover:text-white/80 transition-colors" 
                                       @click="navigator.clipboard.readText().then(text => url = text)"></i>
                                </div>
                            </div>
                            @error('youtube_url')
                                <p class="text-red-300 text-sm mt-2 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>
                        
                        <button 
                            type="submit" 
                            class="w-full bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white font-bold py-4 px-8 rounded-xl focus:outline-none focus:ring-4 focus:ring-purple-300 transition-all duration-200 transform hover:scale-105 text-lg shadow-lg"
                            :class="{ 'opacity-75 cursor-not-allowed': isProcessing }"
                            :disabled="isProcessing"
                        >
                            <span x-show="!isProcessing" class="flex items-center justify-center">
                                <i class="fas fa-rocket mr-3"></i>Start Processing
                            </span>
                            <span x-show="isProcessing" class="flex items-center justify-center">
                                <i class="fas fa-spinner fa-spin mr-3"></i>Processing...
                            </span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if (session('success'))
                <div class="max-w-2xl mx-auto mb-8">
                    <div class="bg-green-500/20 border border-green-400/30 text-green-100 px-6 py-4 rounded-xl backdrop-blur-sm flex items-center">
                        <i class="fas fa-check-circle mr-3 text-green-400"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="max-w-2xl mx-auto mb-8">
                    <div class="bg-red-500/20 border border-red-400/30 text-red-100 px-6 py-4 rounded-xl backdrop-blur-sm flex items-center">
                        <i class="fas fa-exclamation-circle mr-3 text-red-400"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Dashboard Section -->
    <div class="bg-gray-50 min-h-screen py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
                <div class="bg-white rounded-xl p-6 shadow-lg card-hover">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-video text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Videos</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $videos->count() }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl p-6 shadow-lg card-hover">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Completed</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $videos->where('status', 'completed')->count() }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl p-6 shadow-lg card-hover">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Processing</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $videos->whereIn('status', ['pending', 'downloading_audio', 'transcribing'])->count() }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl p-6 shadow-lg card-hover">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Failed</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $videos->where('status', 'failed')->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Videos Table -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-list mr-3 text-gray-600"></i>Recent Videos
                    </h3>
                </div>
                
                @if($videos->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Video</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($videos as $video)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-4">
                                                    <i class="fas fa-play text-gray-600"></i>
                                                </div>
                                                <div>
                                                    <a href="{{ $video->youtube_url }}" target="_blank" 
                                                       class="text-sm font-medium text-gray-900 hover:text-blue-600 transition-colors">
                                                        {{ Str::limit($video->youtube_url, 60) }}
                                                    </a>
                                                    <p class="text-xs text-gray-500">YouTube Video</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border status-{{ $video->status }}">
                                                @switch($video->status)
                                                    @case('pending')
                                                        <i class="fas fa-clock mr-2"></i>Pending
                                                        @break
                                                    @case('downloading_audio')
                                                        <i class="fas fa-download mr-2 pulse-animation"></i>Downloading Audio
                                                        @break
                                                    @case('audio_downloaded')
                                                        <i class="fas fa-music mr-2"></i>Audio Ready
                                                        @break
                                                    @case('transcribing')
                                                        <i class="fas fa-cog fa-spin mr-2"></i>Transcribing
                                                        @break
                                                    @case('transcribed')
                                                        <i class="fas fa-file-alt mr-2"></i>Transcribed
                                                        @break
                                                    @case('completed')
                                                        <i class="fas fa-check mr-2"></i>Completed
                                                        @break
                                                    @case('rewriting')
                                                        <i class="fas fa-pen-fancy fa-spin mr-2"></i>Rewriting
                                                        @break
                                                    @case('rewritten')
                                                        <i class="fas fa-check-double mr-2"></i>Rewritten
                                                        @break
                                                    @case('rewrite_failed')
                                                        <i class="fas fa-exclamation-triangle mr-2"></i>Rewrite Failed
                                                        @break
                                                    @case('failed')
                                                        <i class="fas fa-times mr-2"></i>Failed
                                                        @break
                                                    @default
                                                        <i class="fas fa-question mr-2"></i>{{ ucfirst(str_replace('_', ' ', $video->status)) }}
                                                @endswitch
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <div class="flex items-center">
                                                <i class="fas fa-calendar-alt mr-2"></i>
                                                {{ $video->created_at->diffForHumans() }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center space-x-3">
                                                @if ($video->status == 'audio_downloaded')
                                                    <form action="{{ route('videos.transcribe', $video) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-purple-500">
                                                            <i class="fas fa-file-alt mr-2"></i>Transcribe
                                                        </button>
                                                    </form>
                                @elseif (in_array($video->status, ['completed', 'transcribed', 'rewritten', 'rewrite_failed']))
                                    <a href="{{ route('videos.download', $video) }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                                        <i class="fas fa-download mr-2"></i>Download
                                    </a>
                                    <a href="{{ route('videos.view', $video) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                        <i class="fas fa-eye mr-2"></i>View
                                    </a>
                                    @if($video->transcript_path)
                                    <form action="{{ route('videos.rewrite', $video) }}" method="POST">
                                        @csrf
                                        <button type="submit" 
                                                class="inline-flex items-center px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-orange-500">
                                            <i class="fas fa-pen-fancy mr-2"></i>Rewrite
                                        </button>
                                    </form>
                                    @endif
                                    <form action="{{ route('videos.delete', $video) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this video and its transcript?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                                            <i class="fas fa-trash mr-2"></i>Delete
                                        </button>
                                    </form>
                                                @else
                                                    <button class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-500 text-sm font-medium rounded-lg cursor-not-allowed" disabled>
                                                        <i class="fas fa-hourglass-half mr-2"></i>Processing...
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-video text-gray-400 text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No videos yet</h3>
                        <p class="text-gray-500 mb-6">Get started by submitting your first YouTube URL above.</p>
                        <button onclick="document.getElementById('youtube_url').focus()" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                            <i class="fas fa-plus mr-2"></i>Add Your First Video
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Auto-refresh for status updates -->
    <script>
        // Auto-refresh every 30 seconds if there are processing videos
        const processingStatuses = ['pending', 'downloading_audio', 'transcribing', 'rewriting'];
        const hasProcessingVideos = {{ $videos->whereIn('status', ['pending', 'downloading_audio', 'transcribing'])->count() > 0 ? 'true' : 'false' }};
        
        if (hasProcessingVideos) {
            setTimeout(() => {
                window.location.reload();
            }, 30000);
        }
    </script>
</body>
</html>
