"""
Information Extractors
Specialized classes for extracting different types of medical information
"""

import re
try:
    from typing import List, Dict, Any
except ImportError:
    # Python < 3.5 compatibility
    pass


class PatientInfoExtractor:
    """Extract patient demographics and information"""
    
    def __init__(self):
        self.age_patterns = [
            r'(\d{1,3})[\-\s]year[\-\s]old',
            r'age[:\s]*(\d{1,3})',
            r'aged[:\s]*(\d{1,3})',
            r'(\d{1,3})[\-\s]yo',
            r'(\d{1,3})[\-\s]y[/\s]o',
            r'(\d{1,3})[\-\s]*year',
            r'(\d{1,3})[\-\s]*years'
        ]
    
    def extract(self, text: str) -> Dict[str, Any]:
        """Extract patient information"""
        info = {}
        text_lower = text.lower()
        
        # Age
        for pattern in self.age_patterns:
            match = re.search(pattern, text_lower)
            if match:
                age = int(match.group(1))
                if 0 < age < 150:  # Sanity check
                    info['age'] = age
                    break
        
        # Gender
        if re.search(r'\bmale\b|\bman\b|\bgentleman\b|\bboy\b|\bm\b', text_lower):
            info['gender'] = 'Male'
        elif re.search(r'\bfemale\b|\bwoman\b|\blady\b|\bgirl\b|\bf\b', text_lower):
            info['gender'] = 'Female'
        
        # Name (simple pattern)
        name_match = re.search(r'patient[:\s]+([A-Z][a-z]+\s+[A-Z][a-z]+)', text)
        if name_match:
            info['name'] = name_match.group(1)
        
        return info
    
    def extract_vitals(self, text: str) -> Dict[str, Any]:
        """Extract vital signs"""
        vitals = {}
        
        # Blood pressure
        bp_patterns = [
            r'bp[:\s]*(\d{2,3})\s*[/\s]\s*(\d{2,3})',
            r'blood pressure[:\s]*(\d{2,3})\s*[/\s]\s*(\d{2,3})',
            r'(\d{2,3})\s*/\s*(\d{2,3})\s*mmhg'
        ]
        
        for pattern in bp_patterns:
            match = re.search(pattern, text, re.IGNORECASE)
            if match:
                systolic, diastolic = int(match.group(1)), int(match.group(2))
                vitals['blood_pressure'] = f"{systolic}/{diastolic} mmHg"
                
                # Classify BP
                if systolic >= 140 or diastolic >= 90:
                    vitals['bp_status'] = 'High (Hypertension)'
                elif systolic <= 90 or diastolic <= 60:
                    vitals['bp_status'] = 'Low (Hypotension)'
                else:
                    vitals['bp_status'] = 'Normal'
                break
        
        # Heart rate
        hr_patterns = [
            r'hr[:\s]*(\d{2,3})',
            r'heart rate[:\s]*(\d{2,3})',
            r'pulse[:\s]*(\d{2,3})',
            r'(\d{2,3})\s*bpm'
        ]
        
        for pattern in hr_patterns:
            match = re.search(pattern, text, re.IGNORECASE)
            if match:
                hr = int(match.group(1))
                vitals['heart_rate'] = f"{hr} bpm"
                
                # Classify HR
                if hr > 100:
                    vitals['hr_status'] = 'Tachycardia'
                elif hr < 60:
                    vitals['hr_status'] = 'Bradycardia'
                else:
                    vitals['hr_status'] = 'Normal'
                break
        
        # Temperature
        temp_patterns = [
            r'temp[:\s]*(\d{2,3}\.?\d?)',
            r'temperature[:\s]*(\d{2,3}\.?\d?)',
            r'(\d{2,3}\.?\d?)\s*°[cf]'
        ]
        
        for pattern in temp_patterns:
            match = re.search(pattern, text, re.IGNORECASE)
            if match:
                temp = float(match.group(1))
                vitals['temperature'] = f"{temp}°C"
                
                # Classify temperature
                if temp > 37.5:
                    vitals['temp_status'] = 'Fever'
                elif temp < 36.0:
                    vitals['temp_status'] = 'Hypothermia'
                else:
                    vitals['temp_status'] = 'Normal'
                break
        
        # Oxygen saturation
        o2_patterns = [
            r'o2[:\s]*(\d{2,3})',
            r'spo2[:\s]*(\d{2,3})',
            r'oxygen[:\s]*(\d{2,3})',
            r'(\d{2,3})\s*%\s*o2'
        ]
        
        for pattern in o2_patterns:
            match = re.search(pattern, text, re.IGNORECASE)
            if match:
                o2 = int(match.group(1))
                vitals['oxygen_saturation'] = f"{o2}%"
                
                if o2 < 90:
                    vitals['o2_status'] = 'Low (Hypoxia)'
                else:
                    vitals['o2_status'] = 'Normal'
                break
        
        return vitals


class MedicalTermExtractor:
    """Extract medical terms and symptoms"""
    
    def __init__(self, knowledge_base):
        self.knowledge_base = knowledge_base
    
    def extract(self, text: str) -> List[str]:
        """Extract medical terms from text"""
        text_lower = text.lower()
        found_terms = []
        
        for term in self.knowledge_base.get_medical_terms():
            pattern = r'\b' + re.escape(term) + r'\b'
            if re.search(pattern, text_lower):
                found_terms.append(term.title())
        
        return list(set(found_terms))


class MedicationExtractor:
    """Extract medications from text"""
    
    def __init__(self, knowledge_base):
        self.knowledge_base = knowledge_base
    
    def extract(self, text: str) -> List[Dict[str, Any]]:
        """Extract medications with dosages if available"""
        text_lower = text.lower()
        medications = []
        
        for med in self.knowledge_base.get_medications():
            pattern = r'\b' + re.escape(med) + r'\b'
            if re.search(pattern, text_lower):
                # Try to extract dosage
                dosage_pattern = rf'\b{re.escape(med)}\s+(\d+\s*(?:mg|g|ml|units?)?(?:\s*(?:daily|bid|tid|qid|once|twice))?)'
                dosage_match = re.search(dosage_pattern, text_lower)
                
                med_info = {
                    'name': med.title(),
                    'dosage': dosage_match.group(1) if dosage_match else 'Not specified'
                }
                medications.append(med_info)
        
        return medications


class DiagnosisExtractor:
    """Extract diagnoses from text"""
    
    def __init__(self, knowledge_base):
        self.knowledge_base = knowledge_base
    
    def extract(self, text: str) -> List[str]:
        """Extract confirmed diagnoses"""
        text_lower = text.lower()
        diagnoses = []
        
        # Look for explicit diagnosis statements
        diagnosis_patterns = [
            r'diagnosis[:\s]+([^\.\n]+)',
            r'diagnosed with ([^\.\n]+)',
            r'primary diagnosis[:\s]+([^\.\n]+)',
            r'final diagnosis[:\s]+([^\.\n]+)'
        ]
        
        for pattern in diagnosis_patterns:
            matches = re.findall(pattern, text_lower)
            for match in matches:
                diagnosis = match.strip()
                if len(diagnosis) > 3:
                    diagnoses.append(diagnosis.title())
        
        # Also check for known diagnoses in knowledge base
        for diagnosis in self.knowledge_base.get_diagnoses():
            pattern = r'\b' + re.escape(diagnosis) + r'\b'
            if re.search(pattern, text_lower):
                diagnoses.append(diagnosis.title())
        
        return list(set(diagnoses))
    
    def find_potential(self, text: str) -> List[str]:
        """Find potential diagnoses based on symptoms"""
        text_lower = text.lower()
        potential = []
        
        # Symptom-based diagnosis suggestions
        symptom_diagnosis_map = {
            'shortness of breath': 'Respiratory issue',
            'dyspnea': 'Respiratory issue',
            'chest pain': 'Cardiac issue',
            'angina': 'Cardiac issue',
            'fever': 'Infection',
            'cough': 'Respiratory infection',
            'headache': 'Neurological issue',
            'dizziness': 'Neurological/Cardiovascular issue',
            'abdominal pain': 'Gastrointestinal issue',
            'nausea': 'Gastrointestinal issue'
        }
        
        for symptom, diagnosis in symptom_diagnosis_map.items():
            if re.search(r'\b' + re.escape(symptom) + r'\b', text_lower):
                potential.append(diagnosis)
        
        return list(set(potential))

