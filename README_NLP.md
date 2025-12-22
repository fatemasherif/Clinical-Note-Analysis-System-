# Clinical NLP Analysis System

## Overview

The NLP (Natural Language Processing) system for clinical note analysis has been refactored into a well-structured Python-based architecture. The system is modular, extensible, and designed for easy integration with the PHP application.

## Architecture

### Structure

```
models/nlp/
├── __init__.py              # Module initialization
├── clinical_analyzer.py     # Main orchestrator class
├── knowledge_base.py        # Medical terminology and patterns
├── extractors.py           # Specialized extraction classes
└── processors.py           # Text processing utilities
```

### Components

1. **ClinicalAnalyzer** - Main entry point that orchestrates all NLP components
2. **MedicalKnowledgeBase** - Centralized medical terminology, medications, and diagnoses
3. **PatientInfoExtractor** - Extracts patient demographics and vital signs
4. **MedicalTermExtractor** - Identifies medical terms and symptoms
5. **MedicationExtractor** - Extracts medications with dosages
6. **DiagnosisExtractor** - Identifies diagnoses and potential conditions
7. **TextProcessor** - Handles text preprocessing and keyword extraction
8. **SummaryGenerator** - Generates concise clinical summaries

## Setup

### 1. Install Python Dependencies

```bash
pip install -r requirements.txt
```

### 2. Start the NLP API Service

The Python NLP service runs as a Flask microservice on port 5001:

```bash
python models/nlp_api.py
```

Or on Windows:
```bash
python models\nlp_api.py
```

The service will be available at `http://127.0.0.1:5001`

### 3. PHP Integration

The PHP application uses `NLPBridge.php` to communicate with the Python service. The bridge:
- Attempts to call the Python API first
- Falls back to PHP implementation if the service is unavailable
- Provides seamless integration without breaking existing functionality

## Features

### Enhanced Analysis Capabilities

1. **Comprehensive Medical Term Extraction**
   - 50+ medical terms and symptoms
   - Pattern-based matching with word boundaries

2. **Medication Extraction**
   - 40+ common medications
   - Dosage extraction when available
   - Categorized by therapeutic class

3. **Diagnosis Identification**
   - 60+ common diagnoses
   - Pattern-based diagnosis statement extraction
   - Potential diagnosis suggestions based on symptoms

4. **Patient Information Extraction**
   - Age extraction (multiple patterns)
   - Gender identification
   - Patient name extraction
   - Vital signs (BP, HR, Temperature, O2 Sat)
   - Vital sign classification (Normal/High/Low)

5. **Advanced Text Processing**
   - Sentence segmentation
   - Keyword extraction with frequency
   - Stop word filtering
   - Text statistics

6. **Intelligent Summarization**
   - Key phrase identification
   - Important sentence selection
   - Context-aware summary generation

7. **Risk Factor Identification**
   - Smoking, diabetes, obesity, etc.
   - Family history detection

8. **Recommendations**
   - Basic clinical recommendations based on findings
   - Context-aware suggestions

## API Endpoints

### POST /analyze
Analyzes clinical text and returns comprehensive results.

**Request:**
```json
{
  "text": "Patient is a 45-year-old male with hypertension..."
}
```

**Response:**
```json
{
  "summary": "...",
  "sentence_count": 10,
  "word_count": 150,
  "medical_terms": ["Hypertension", "Pain"],
  "medications": [{"name": "Amlodipine", "dosage": "5mg daily"}],
  "diagnoses": ["Hypertension"],
  "patient_info": {"age": 45, "gender": "Male"},
  "vitals": {"blood_pressure": "140/90 mmHg", "bp_status": "High"},
  "keywords": ["Patient (3x)", "Hypertension (2x)"],
  "potential_diagnoses": ["Cardiac issue"],
  "risk_factors": ["Hypertension"],
  "recommendations": ["Monitor blood pressure regularly"],
  "confidence": "High",
  "timestamp": "2025-01-15 10:30:00"
}
```

### GET /health
Health check endpoint.

**Response:**
```json
{
  "status": "ok",
  "service": "Clinical NLP API"
}
```

## Usage in PHP

```php
require_once 'models/NLPBridge.php';

$nlp = new NLPBridge();
$result = $nlp->analyze($clinicalText);

// Check if Python service is available
if ($nlp->isServiceAvailable()) {
    echo "Using Python NLP Service";
} else {
    echo "Using PHP Fallback";
}
```

## Extending the System

### Adding New Medical Terms

Edit `models/nlp/knowledge_base.py`:

```python
def _load_medical_terms(self):
    return [
        # ... existing terms
        'new_term_1',
        'new_term_2'
    ]
```

### Adding New Extractors

1. Create a new class in `extractors.py`:

```python
class CustomExtractor:
    def __init__(self, knowledge_base):
        self.knowledge_base = knowledge_base
    
    def extract(self, text):
        # Your extraction logic
        pass
```

2. Add it to `ClinicalAnalyzer`:

```python
def __init__(self):
    # ... existing initializations
    self.custom_extractor = CustomExtractor(self.knowledge_base)
```

### Adding ML Models

The architecture is designed to easily integrate ML models:

1. Create a new module `models/nlp/ml_models.py`
2. Import in `clinical_analyzer.py`
3. Use in the `analyze()` method

Example:
```python
from .ml_models import DiagnosisPredictor

class ClinicalAnalyzer:
    def __init__(self):
        # ... existing code
        self.ml_predictor = DiagnosisPredictor()
    
    def analyze(self, text):
        # ... existing analysis
        results['ml_predictions'] = self.ml_predictor.predict(text)
        return results
```

## Future Enhancements

1. **Machine Learning Integration**
   - Named Entity Recognition (NER) using spaCy
   - Sentiment analysis for patient notes
   - Diagnosis prediction models
   - Medication interaction detection

2. **Advanced Features**
   - ICD-10 code mapping
   - SNOMED CT integration
   - Clinical decision support
   - Multi-language support

3. **Performance Optimization**
   - Caching for frequently analyzed terms
   - Batch processing
   - Async processing for large documents

## Troubleshooting

### Python Service Not Starting

1. Check if port 5001 is available:
   ```bash
   netstat -an | findstr 5001
   ```

2. Verify Python and Flask installation:
   ```bash
   python --version
   pip list | findstr Flask
   ```

### PHP Cannot Connect to Python Service

1. Ensure the Python service is running
2. Check firewall settings
3. Verify the API URL in `NLPBridge.php`
4. The system will automatically fall back to PHP implementation

### Import Errors

If you see import errors, ensure you're running from the project root:
```bash
cd /path/to/Clinical-Note-Analysis-System--main
python models/nlp_api.py
```

## Testing

Test the Python service directly:

```bash
curl -X POST http://127.0.0.1:5001/analyze \
  -H "Content-Type: application/json" \
  -d '{"text": "Patient is a 50-year-old male with chest pain and shortness of breath."}'
```

## License

Part of the Clinical Note Analysis System project.

