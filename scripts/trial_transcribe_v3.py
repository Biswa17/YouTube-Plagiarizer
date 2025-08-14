import requests
import json
import os
import time

def transcribe_audio_file_direct_api(api_key, audio_file_path, model="gemini-2.5-flash"):
    """
    Transcribe audio file using Gemini 2.5 API (Pure Direct API Calls)
    
    Args:
        api_key (str): Your Google API key.
        audio_file_path (str): Path to the audio file.
        model (str): Model to use (gemini-2.5-flash or gemini-2.5-pro).
        
    Returns:
        str: Transcribed text.
    """
    try:
        # 1. Upload the audio file
        upload_url = f"https://generativelanguage.googleapis.com/v1beta/files?key={api_key}"
        upload_headers = {
            "Content-Type": "application/octet-stream", # Changed to octet-stream
            "X-Goog-Upload-Protocol": "raw",
            "X-Goog-Upload-File-Name": os.path.basename(audio_file_path)
        }
        
        with open(audio_file_path, "rb") as f:
            audio_bytes = f.read()
        
        print(f"Attempting to upload file: {audio_file_path}")
        upload_response = requests.post(upload_url, headers=upload_headers, data=audio_bytes)
        upload_response.raise_for_status() # Raise an exception for HTTP errors
        file_data = upload_response.json()
        file_name = file_data.get("name") # This is the fileUri
        
        if not file_name:
            raise ValueError("File upload failed: No file name returned in response.")

        print(f"File uploaded successfully, file_name (fileUri): {file_name}")

        # 2. Wait for the file to be processed
        get_file_url = f"https://generativelanguage.googleapis.com/v1beta/{file_name}?key={api_key}"
        while True:
            print("Checking file processing status...")
            get_response = requests.get(get_file_url)
            get_response.raise_for_status()
            file_status = get_response.json()
            state = file_status.get("state")
            
            if state == "ACTIVE":
                print("File processing complete.")
                break
            elif state == "PROCESSING":
                print("File still processing, waiting 5 seconds...")
                time.sleep(5)
            elif state == "FAILED":
                raise ValueError(f"File processing failed: {file_status.get('error_message', 'Unknown error')}")
            else:
                raise ValueError(f"Unexpected file state: {state}")

        # 3. Generate transcript using the fileUri
        generate_content_url = f"https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent?key={api_key}"
        generate_headers = {
            "Content-Type": "application/json"
        }
        payload = {
            "contents": [
                {"parts": [{"text": "Generate a transcript of the speech."}]},
                {"parts": [{"fileData": {"mimeType": "audio/mpeg", "fileUri": file_name}}]} # Use file_name as fileUri
            ]
        }
        
        print("Generating transcript...")
        generate_response = requests.post(generate_content_url, headers=generate_headers, data=json.dumps(payload))
        generate_response.raise_for_status()
        transcript_data = generate_response.json()
        
        # Extract the text from the response
        text_content = transcript_data.get("candidates", [{}])[0].get("content", {}).get("parts", [{}])[0].get("text")
        return text_content
        
    except requests.exceptions.RequestException as req_err:
        print(f"Network or API error: {req_err}")
        if req_err.response is not None:
            print(f"Response status code: {req_err.response.status_code}")
            print(f"Response content: {req_err.response.text}")
        return None
    except Exception as e:
        print(f"An unexpected error occurred: {e}")
        return None

if __name__ == "__main__":
    api_key = "AIzaSyCOorQRcf4gKcLy8FqGNjIqFqSvMfokmtI" # Your provided API key
    
    if api_key == "YOUR_API_KEY":
        print("Please replace 'YOUR_API_KEY' with your actual Google API key or set the GOOGLE_API_KEY environment variable.")
    else:
        audio_file_path = "scripts/audio/1.mp3"
        
        print(f"Starting transcription process for: {audio_file_path}")
        transcript = transcribe_audio_file_direct_api(api_key, audio_file_path)
        
        if transcript:
            print("\nTranscription Result:")
            print(transcript)
        else:
            print("\nTranscription failed.")
