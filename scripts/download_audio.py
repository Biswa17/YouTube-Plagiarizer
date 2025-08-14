import argparse
import yt_dlp
import sys

def download_audio(url, output_path):
    """
    Downloads the audio from a YouTube URL to the specified path.
    """
    try:
        ydl_opts = {
            'format': 'bestaudio/best',
            'outtmpl': output_path,
            'postprocessors': [{
                'key': 'FFmpegExtractAudio',
                'preferredcodec': 'mp3',
                'preferredquality': '192',
            }],
            'quiet': True,
        }
        with yt_dlp.YoutubeDL(ydl_opts) as ydl:
            ydl.download([url])
        # yt-dlp automatically adds the .mp3 extension
        final_path = output_path
        if not final_path.endswith('.mp3'):
            final_path += '.mp3'
        print(final_path)
        sys.exit(0)
    except Exception as e:
        print(f"Error: {e}", file=sys.stderr)
        sys.exit(1)

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Download YouTube audio.")
    parser.add_argument("--url", required=True, help="The YouTube URL to download from.")
    parser.add_argument("--output-path", required=True, help="The path to save the audio file.")
    args = parser.parse_args()
    download_audio(args.url, args.output_path)
