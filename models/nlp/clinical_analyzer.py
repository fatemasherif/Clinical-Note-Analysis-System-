"""
Main Clinical NLP Analyzer
Orchestrates all NLP components for comprehensive clinical note analysis
"""

from .extractors import PatientInfoExtractor, MedicalTermExtractor, MedicationExtractor, DiagnosisExtractor
from .processors import TextProcessor, SummaryGenerator
from .knowledge_base import MedicalKnowledgeBase


class ClinicalAnalyzer:
    """Main analyzer class for clinical notes"""
    
    def __init__(self):
        """Initialize all NLP components"""
        self.knowledge_base = MedicalKnowledgeBase()
        self.text_processor = TextProcessor()
        self.patient_extractor = PatientInfoExtractor()
        self.term_extractor = MedicalTermExtractor(self.knowledge_base)
        self.medication_extractor = MedicationExtractor(self.knowledge_base)
        self.diagnosis_extractor = DiagnosisExtractor(self.knowledge_base)
        self.summary_generator = SummaryGenerator()
    
    def analyze(self, text):
        """
        Main analysis method - performs comprehensive clinical note analysis
        
        Args:
            text (str): Clinical note text to analyze
            
        Returns:
            dict: Comprehensive analysis results
        """
        if not text or len(text.strip()) < 10:
            return self._empty_result("Text too short for analysis")
        
        # Preprocess text
        processed = self.text_processor.preprocess(text)
        
        # Extract all information
        results = {
            'summary': self.summary_generator.generate(processed['sentences'], text),
            'sentence_count': len(processed['sentences']),
            'word_count': processed['word_count'],
            'character_count': len(text),
            'medical_terms': self.term_extractor.extract(text),
            'medications': self.medication_extractor.extract(text),
            'diagnoses': self.diagnosis_extractor.extract(text),
            'patient_info': self.patient_extractor.extract(text),
            'vitals': self.patient_extractor.extract_vitals(text),
            'keywords': self.text_processor.extract_keywords(processed['words']),
            'potential_diagnoses': self.diagnosis_extractor.find_potential(text),
            'risk_factors': self._identify_risk_factors(text),
            'recommendations': self._generate_recommendations(text),
            'analysis_type': 'Enhanced Clinical NLP Analysis',
            'confidence': self._calculate_confidence(text),
            'timestamp': self.text_processor.get_timestamp()
        }
        
        return results
    
    def _identify_risk_factors(self, text):
        """Identify potential risk factors mentioned in the note"""
        risk_patterns = [
            (r'\b(smoking|smoker|tobacco)\b', 'Smoking'),
            (r'\b(diabetes|diabetic)\b', 'Diabetes'),
            (r'\b(obesity|obese|overweight)\b', 'Obesity'),
            (r'\b(hypertension|high blood pressure)\b', 'Hypertension'),
            (r'\b(family history|fhx)\b', 'Family History'),
            (r'\b(sedentary|inactive)\b', 'Sedentary Lifestyle'),
            (r'\b(alcohol|drinking)\b', 'Alcohol Use'),
        ]
        
        text_lower = text.lower()
        risks = []
        for pattern, label in risk_patterns:
            if __import__('re').search(pattern, text_lower):
                risks.append(label)
        
        return list(set(risks))
    
    def _generate_recommendations(self, text):
        """Generate basic recommendations based on findings"""
        recommendations = []
        text_lower = text.lower()
        
        if 'hypertension' in text_lower or 'high blood pressure' in text_lower:
            recommendations.append('Monitor blood pressure regularly')
            recommendations.append('Consider lifestyle modifications')
        
        if 'diabetes' in text_lower:
            recommendations.append('Monitor blood glucose levels')
            recommendations.append('Follow diabetic diet plan')
        
        if 'fever' in text_lower or 'infection' in text_lower:
            recommendations.append('Monitor temperature')
            recommendations.append('Complete prescribed antibiotic course if applicable')
        
        return recommendations
    
    def _calculate_confidence(self, text):
        """Calculate confidence score for the analysis"""
        medical_terms = len(self.term_extractor.extract(text))
        medications = len(self.medication_extractor.extract(text))
        diagnoses = len(self.diagnosis_extractor.extract(text))
        patient_info = self.patient_extractor.extract(text)
        
        score = 0
        score += medical_terms * 3
        score += medications * 5
        score += diagnoses * 8
        score += len(patient_info) * 5
        score += min(len(text) / 50, 20)
        
        if score >= 80:
            return "High"
        elif score >= 50:
            return "Medium"
        elif score >= 25:
            return "Low"
        else:
            return "Very Low"
    
    def _empty_result(self, message="No text provided"):
        """Return empty result structure"""
        return {
            'summary': message,
            'sentence_count': 0,
            'word_count': 0,
            'character_count': 0,
            'medical_terms': [],
            'medications': [],
            'diagnoses': [],
            'patient_info': {},
            'vitals': {},
            'keywords': [],
            'potential_diagnoses': [],
            'risk_factors': [],
            'recommendations': [],
            'analysis_type': 'No Analysis',
            'confidence': 'None',
            'timestamp': None
        }

