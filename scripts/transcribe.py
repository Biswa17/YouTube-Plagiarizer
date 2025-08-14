import google.generativeai as genai
import os
import sys
import time

def setup_gemini(api_key):
    """Initialize Gemini client with API key"""
    genai.configure(api_key=api_key)

def transcribe_audio_file(audio_file_path, output_transcript_path, model="gemini-2.5-flash"):
    """
    Transcribe audio file using Gemini 2.5 API (File Upload Method)
    
    Args:
        audio_file_path (str): Path to the audio file
        model (str): Model to use (gemini-2.5-flash or gemini-2.5-pro)
        
    Returns:
        str: Transcribed text
    """
    try:
        # Upload the audio file
        myfile = genai.upload_file(path=audio_file_path)
        
        # Wait for the file to be processed
        while myfile.state.name == "PROCESSING":
            time.sleep(5) # Wait for 5 seconds before checking again
            myfile = genai.get_file(myfile.name)
        
        if myfile.state.name == "FAILED":
            raise ValueError(f"File processing failed: {myfile.error_message}")

        # Generate transcript
        model_instance = genai.GenerativeModel(model_name=model)
        response = model_instance.generate_content(
        contents=["""
        Transcribe the following audio into clear, well-punctuated, human-readable text in natural language.
        - Correct any obvious grammar or pronunciation mistakes.
        - Keep the meaning and facts exactly as spoken.
        - Remove timestamps, filler words (uh, um, you know), and repeated phrases.
        - Format as clean paragraphs suitable for reading, not as a verbatim speech log.
        - Do not summarize or shorten — keep all details intact.
        """, myfile]
        )
        
        # Save the transcript to the specified output path
        with open(output_transcript_path, "w") as f:
            f.write(response.text)
        
        return True # Indicate success
        
    except Exception as e:
        print(f"Error during transcription: {e}", file=sys.stderr)
        return False # Indicate failure

if __name__ == "__main__":
    if len(sys.argv) != 4:
        print("Usage: python3 transcribe.py <audio_file_path> <api_key> <output_transcript_path>", file=sys.stderr)
        sys.exit(1)

    audio_file_path = sys.argv[1]
    api_key = sys.argv[2]
    output_transcript_path = sys.argv[3]
    
    setup_gemini(api_key) # Configure the API key
    
    success = transcribe_audio_file(audio_file_path, output_transcript_path)
    
    if not success:
        sys.exit(1) # Indicate failure
