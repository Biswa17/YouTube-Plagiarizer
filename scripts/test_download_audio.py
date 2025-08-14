import os
import sys
from download_audio import download_audio

def main():
    """
    A simple script to test the audio download functionality.
    """
    url = "https://www.youtube.com/watch?v=ftZTNhJOXaI"
    
    output_dir = os.path.join(os.path.dirname(__file__), "audio")
    os.makedirs(output_dir, exist_ok=True)
    
    output_filename = "test_download"
    output_path = os.path.join(output_dir, output_filename)
    expected_file = output_path + ".mp3"

    print(f"Downloading audio from: {url}")
    print(f"Saving to: {expected_file}")

    try:
        download_audio(url, output_path)
        print("\nDownload function executed.")
    except SystemExit as e:
        if e.code == 0:
            print("Download script exited successfully.")
        else:
            print(f"Download script exited with error code: {e.code}")
            sys.exit(1)

    if os.path.exists(expected_file) and os.path.getsize(expected_file) > 0:
        print(f"\nSUCCESS: Audio file was downloaded successfully to {expected_file}")
    else:
        print(f"\nFAILURE: Audio file was not created or is empty.")

if __name__ == '__main__':
    main()
