<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Transcript View - YouTube Plazarizer</title>
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
        
        .transcript-text {
            line-height: 1.8;
            font-size: 1.1rem;
        }
        
        .copy-animation {
            animation: copySuccess 0.3s ease-in-out;
        }
        
        @keyframes copySuccess {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body class="antialiased">
    <!-- Navigation -->
    <nav class="glass-effect sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('home') }}" class="flex items-center space-x-3 hover:opacity-80 transition-opacity">
                        <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-play text-white text-lg"></i>
                        </div>
                        <h1 class="text-2xl font-bold text-white">YouTube Plazarizer</h1>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('home') }}" class="text-white/80 hover:text-white transition-colors flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-block mb-4">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                    <i class="fas fa-file-alt text-white text-2xl"></i>
                </div>
            </div>
            <h2 class="text-4xl font-bold text-white mb-2">Transcript Ready</h2>
            <p class="text-white/70 text-lg">Your video has been successfully transcribed</p>
        </div>

        <!-- Video Info Card -->
        <div class="glass-effect rounded-2xl p-6 mb-8">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-video text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-semibold text-lg">Source Video</h3>
                        <a href="{{ $video->youtube_url }}" target="_blank" 
                           class="text-white/70 hover:text-white transition-colors text-sm flex items-center">
                            <i class="fas fa-external-link-alt mr-2"></i>
                            {{ Str::limit($video->youtube_url, 80) }}
                        </a>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="px-3 py-1 bg-green-500/20 text-green-100 rounded-full text-sm font-medium border border-green-400/30">
                        <i class="fas fa-check mr-2"></i>Completed
                    </span>
                    <span class="text-white/70 text-sm">
                        <i class="fas fa-calendar-alt mr-2"></i>{{ $video->created_at->format('M d, Y') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-4 mb-8 justify-center">
            <button 
                onclick="copyTranscript()" 
                class="flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-all duration-200 transform hover:scale-105 shadow-lg"
                id="copyBtn"
            >
                <i class="fas fa-copy mr-2"></i>Copy to Clipboard
            </button>
            
            <a href="{{ route('videos.download', $video) }}" 
               class="flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl transition-all duration-200 transform hover:scale-105 shadow-lg">
                <i class="fas fa-download mr-2"></i>Download Transcript
            </a>
            
            <button 
                onclick="printTranscript()" 
                class="flex items-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-xl transition-all duration-200 transform hover:scale-105 shadow-lg">
                <i class="fas fa-print mr-2"></i>Print
            </button>
            
            <button 
                onclick="shareTranscript()" 
                class="flex items-center px-6 py-3 bg-pink-600 hover:bg-pink-700 text-white font-medium rounded-xl transition-all duration-200 transform hover:scale-105 shadow-lg">
                <i class="fas fa-share mr-2"></i>Share
            </button>
        </div>

        <!-- Transcript Content -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden" x-data="{ fontSize: 18 }">
            <!-- Transcript Header -->
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center space-x-4">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-file-text mr-3 text-gray-600"></i>Transcript Content
                    </h3>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                        {{ str_word_count($transcript) }} words
                    </span>
                </div>
                
                <!-- Font Size Controls -->
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600">Font Size:</span>
                    <button @click="fontSize = Math.max(12, fontSize - 2)" 
                            class="w-8 h-8 bg-gray-200 hover:bg-gray-300 rounded-lg flex items-center justify-center transition-colors">
                        <i class="fas fa-minus text-xs"></i>
                    </button>
                    <span class="text-sm text-gray-700 w-8 text-center" x-text="fontSize + 'px'"></span>
                    <button @click="fontSize = Math.min(24, fontSize + 2)" 
                            class="w-8 h-8 bg-gray-200 hover:bg-gray-300 rounded-lg flex items-center justify-center transition-colors">
                        <i class="fas fa-plus text-xs"></i>
                    </button>
                </div>
            </div>
            
            <!-- Transcript Text -->
            <div class="p-8">
                <div id="transcriptContent" 
                     class="transcript-text text-gray-800 leading-relaxed whitespace-pre-wrap"
                     :style="'font-size: ' + fontSize + 'px;'">{{ $transcript }}</div>
            </div>
        </div>

        <!-- Back to Dashboard -->
        <div class="text-center mt-12">
            <a href="{{ route('home') }}" 
               class="inline-flex items-center px-8 py-4 bg-white/20 hover:bg-white/30 text-white font-medium rounded-xl transition-all duration-200 backdrop-blur-sm border border-white/20">
                <i class="fas fa-arrow-left mr-3"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <script>
        function copyTranscript() {
            const transcriptText = document.getElementById('transcriptContent').textContent;
            navigator.clipboard.writeText(transcriptText).then(function() {
                const btn = document.getElementById('copyBtn');
                const originalContent = btn.innerHTML;
                
                btn.innerHTML = '<i class="fas fa-check mr-2"></i>Copied!';
                btn.classList.add('bg-green-600', 'copy-animation');
                btn.classList.remove('bg-blue-600');
                
                setTimeout(() => {
                    btn.innerHTML = originalContent;
                    btn.classList.remove('bg-green-600', 'copy-animation');
                    btn.classList.add('bg-blue-600');
                }, 2000);
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
                alert('Failed to copy transcript. Please try again.');
            });
        }

        function printTranscript() {
            const transcriptContent = document.getElementById('transcriptContent').textContent;
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Transcript - YouTube Plazarizer</title>
                        <style>
                            body { 
                                font-family: 'Times New Roman', serif; 
                                line-height: 1.6; 
                                margin: 40px;
                                color: #333;
                            }
                            h1 { 
                                color: #2d3748; 
                                border-bottom: 2px solid #e2e8f0; 
                                padding-bottom: 10px;
                            }
                            .meta {
                                color: #666;
                                font-size: 14px;
                                margin-bottom: 30px;
                            }
                            .content {
                                white-space: pre-wrap;
                                font-size: 16px;
                            }
                        </style>
                    </head>
                    <body>
                        <h1>YouTube Video Transcript</h1>
                        <div class="meta">
                            <p><strong>Source:</strong> {{ $video->youtube_url }}</p>
                            <p><strong>Generated:</strong> ${new Date().toLocaleDateString()}</p>
                            <p><strong>Word Count:</strong> ${transcriptContent.split(' ').length} words</p>
                        </div>
                        <div class="content">${transcriptContent}</div>
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }

        function shareTranscript() {
            if (navigator.share) {
                navigator.share({
                    title: 'YouTube Video Transcript',
                    text: 'Check out this transcript from YouTube Plazarizer',
                    url: window.location.href
                }).catch(console.error);
            } else {
                // Fallback: copy URL to clipboard
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Link copied to clipboard! You can now share it.');
                }).catch(() => {
                    alert('Sharing not supported. You can copy the URL manually: ' + window.location.href);
                });
            }
        }
    </script>
</body>
</html>
