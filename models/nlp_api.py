"""
Python NLP API Server
Runs as a Flask microservice to handle NLP requests from PHP
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
from models.nlp import ClinicalAnalyzer
import sys
import os

# Add models directory to path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

app = Flask(__name__)
CORS(app)  # Allow cross-origin requests from PHP

# Initialize analyzer
analyzer = ClinicalAnalyzer()

@app.route('/analyze', methods=['POST'])
def analyze():
    """Analyze clinical text"""
    try:
        data = request.get_json()
        text = data.get('text', '')
        
        if not text:
            return jsonify({'error': 'No text provided'}), 400
        
        # Perform analysis
        results = analyzer.analyze(text)
        
        return jsonify(results)
    
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/health', methods=['GET'])
def health():
    """Health check endpoint"""
    return jsonify({'status': 'ok', 'service': 'Clinical NLP API'})

if __name__ == '__main__':
    # Run on port 5001 to avoid conflict with main Flask app
    app.run(host='127.0.0.1', port=5001, debug=False)

