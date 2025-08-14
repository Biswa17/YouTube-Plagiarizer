import google.generativeai as genai
import os
import sys
import time

def setup_gemini(api_key):
    """Initialize Gemini client with API key"""
    genai.configure(api_key=api_key)

def rewrite_transcript(input_path, output_path, model="gemini-1.5-flash"):
    """
    Rewrites a transcript using the Gemini API.
    
    Args:
        input_path (str): Path to the original transcript file.
        output_path (str): Path to save the rewritten transcript.
        model (str): Model to use.
        
    Returns:
        bool: True for success, False for failure.
    """
    try:
        # Read the original transcript
        with open(input_path, 'r') as f:
            original_transcript = f.read()

        if not original_transcript:
            print("Error: Input transcript is empty.", file=sys.stderr)
            return False

        # Generate rewritten transcript
        model_instance = genai.GenerativeModel(model_name=model)
        response = model_instance.generate_content(
            contents=[f"""
            You are an expert YouTube scriptwriter for an informative channel. 
            Rewrite the following transcript into a fresh, engaging, and easy-to-follow script 
            while keeping every fact, detail, and logical flow intact.

            - Use a friendly and conversational tone that sounds like a natural voiceover.
            - Organize content into short, clear paragraphs.
            - Use simple, direct language without jargon unless it’s explained.
            - Avoid repeating phrases and remove filler words.
            - Add smooth transitions between ideas so the script flows well.
            - Keep the meaning 100% accurate to the original — do not add or remove facts.
            - Make it sound engaging enough for a YouTube audience that wants to learn something new.

            Original Transcript:
            ---
            {original_transcript}
            ---

            Final YouTube Script:
            """]
        )

        # Save the rewritten transcript to the specified output path
        os.makedirs(os.path.dirname(output_path), exist_ok=True)
        with open(output_path, "w") as f:
            f.write(response.text)
        
        return True  # Indicate success
        
    except Exception as e:
        print(f"Error during rewriting: {e}", file=sys.stderr)
        return False  # Indicate failure

if __name__ == "__main__":
    if len(sys.argv) != 4:
        print("Usage: python3 rewrite_transcript.py <input_transcript_path> <api_key> <output_transcript_path>", file=sys.stderr)
        sys.exit(1)

    input_transcript_path = sys.argv[1]
    api_key = sys.argv[2]
    output_transcript_path = sys.argv[3]
    
    setup_gemini(api_key)  # Configure the API key
    
    success = rewrite_transcript(input_transcript_path, output_transcript_path)
    
    if not success:
        sys.exit(1)  # Indicate failure
