import requests
import json
import os
from pathlib import Path
import time
import google.generativeai as genai # Import the SDK for file handling

# Initialize the client with your API key
def setup_gemini(api_key):
    """Initialize Gemini client with API key for SDK file operations."""
    genai.configure(api_key=api_key)
    return api_key # Return API key for direct requests

def transcribe_audio_file(api_key, audio_file_path, model="gemini-2.5-flash"):
    """
    Transcribe audio file using Gemini 2.5 API (Hybrid: SDK for upload, direct for generateContent)
    
    Args:
        api_key (str): Your Google API key.
        audio_file_path (str): Path to the audio file.
        model (str): Model to use (gemini-2.5-flash or gemini-2.5-pro).
        
    Returns:
        str: Transcribed text.
    """
    try:
        # 1. Upload the audio file using the SDK's file upload utility
        # This handles the complex raw upload protocol correctly.
        print("Uploading file using Gemini SDK...")
        myfile = genai.upload_file(path=audio_file_path)
        
        # Wait for the file to be processed
        while myfile.state.name == "PROCESSING":
            print("File still processing, waiting...")
            time.sleep(5) # Wait for 5 seconds before checking again
            myfile = genai.get_file(myfile.name)
        
        if myfile.state.name == "FAILED":
            raise ValueError(f"File processing failed: {myfile.error_message}")

        file_uri = myfile.name # This is the resource name, e.g., "files/12345"
        print(f"File uploaded and processed, fileUri: {file_uri}")

        # 2. Generate transcript using direct API call
        generate_content_url = f"https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent?key={api_key}"
        payload = {
            "contents": [
                {"parts": [{"text": "Generate a transcript of the speech."}]},
                {"parts": [{"fileData": {"mimeType": "audio/mpeg", "fileUri": file_uri}}]}
            ]
        }
        
        print("Generating content using direct API call...")
        generate_response = requests.post(generate_content_url, headers={"Content-Type": "application/json"}, data=json.dumps(payload))
        generate_response.raise_for_status()
        transcript_data = generate_response.json()
        
        return transcript_data.get("candidates", [{}])[0].get("content", {}).get("parts", [{}])[0].get("text")
        
    except requests.exceptions.RequestException as req_err:
        print(f"Network or API error during transcription: {req_err}")
        if req_err.response is not None:
            print(f"Response content: {req_err.response.text}")
        return None
    except Exception as e:
        print(f"Error during transcription: {e}")
        return None

if __name__ == "__main__":
    api_key = "AIzaSyCOorQRcf4gKcLy8FqGNjIqFqSvMfokmtI" # Your provided API key
    
    setup_gemini(api_key) # Configure the API key for SDK file operations
    audio_file_path = "scripts/audio/1.mp3"
    
    print(f"Attempting to transcribe: {audio_file_path}")
    transcript = transcribe_audio_file(api_key, audio_file_path)
    
    if transcript:
        print("\nTranscription Result:")
        print(transcript)
    else:
        print("\nTranscription failed.")
