import re
from collections import Counter

class SimpleNLP:
    """Enhanced NLP processor for clinical notes with better medical analysis"""
    
    def __init__(self):
        self.medical_terms = self._load_medical_terms()
        self.medications = self._load_medications()
        self.diagnoses = self._load_diagnoses()
    
    def _load_medical_terms(self):
        return [
            'pain', 'fever', 'headache', 'nausea', 'vomiting', 'fatigue', 'weakness',
            'cough', 'shortness of breath', 'dyspnea', 'dizziness', 'vertigo', 
            'swelling', 'edema', 'rash', 'itching', 'bleeding', 'hemorrhage',
            'bruising', 'palpitations', 'tachycardia', 'bradycardia', 
            'hypertension', 'high blood pressure', 'hypotension', 'low blood pressure',
            'weight loss', 'weight gain', 'constipation', 'diarrhea', 'dysuria',
            'hematuria', 'blood in urine', 'polyuria', 'frequent urination',
            'oliguria', 'reduced urine', 'anuria', 'no urine', 'jaundice',
            'yellow skin', 'anorexia', 'loss of appetite'
        ]
    
    def _load_medications(self):
        return [
            'amlodipine', 'lisinopril', 'metformin', 'insulin', 'aspirin',
            'atorvastatin', 'simvastatin', 'warfarin', 'heparin', 'ibuprofen',
            'paracetamol', 'acetaminophen', 'antibiotics', 'penicillin',
            'amoxicillin', 'azithromycin', 'prednisone', 'dexamethasone',
            'omeprazole', 'pantoprazole', 'ranitidine', 'metoprolol',
            'propranolol', 'furosemide', 'lasix', 'hydrochlorothiazide'
        ]
    
    def _load_diagnoses(self):
        return [
            'hypertension', 'diabetes mellitus', 'type 2 diabetes', 'asthma',
            'chronic obstructive pulmonary disease', 'copd', 'pneumonia',
            'bronchitis', 'influenza', 'covid-19', 'urinary tract infection',
            'uti', 'gastroenteritis', 'myocardial infarction', 'heart attack',
            'angina', 'congestive heart failure', 'chf', 'arthritis',
            'osteoarthritis', 'rheumatoid arthritis', 'anemia',
            'iron deficiency anemia', 'migraine', 'stroke', 'cva',
            'gastroesophageal reflux disease', 'gerd', 'peptic ulcer',
            'depression', 'anxiety disorder', 'chronic kidney disease',
            'ckd', 'hepatitis', 'cirrhosis', 'cholecystitis'
        ]
    
    def analyze_text(self, text):
        """Main analysis function - called from Flask route"""
        if not text or len(text.strip()) < 10:
            return self._empty_result("Text too short for analysis")
        
        text_clean = text.strip()
        
        # Basic statistics
        words = text_clean.lower().split()
        sentences = [s.strip() for s in re.split(r'[.!?]+', text_clean) if s.strip()]
        
        # Extract all information
        results = {
            'summary': self._create_summary(text_clean, sentences),
            'sentence_count': len(sentences),
            'word_count': len(words),
            'medical_terms': self._extract_terms(text_clean, self.medical_terms),
            'medications': self._extract_terms(text_clean, self.medications),
            'diagnoses': self._extract_terms(text_clean, self.diagnoses),
            'patient_info': self._extract_patient_info(text_clean),
            'keywords': self._extract_keywords(words),
            'potential_diagnoses': self._find_potential_diagnoses(text_clean),
            'analysis_type': 'Clinical NLP Analysis',
            'confidence': self._calculate_confidence(text_clean)
        }
        
        return results
    
    def _extract_terms(self, text, term_list):
        """Extract specific terms from text"""
        text_lower = text.lower()
        found_terms = []
        
        for term in term_list:
            # Check for whole word matches
            pattern = r'\b' + re.escape(term) + r'\b'
            if re.search(pattern, text_lower):
                found_terms.append(term)
        
        return list(set(found_terms))
    
    def _extract_patient_info(self, text):
        """Extract patient demographics and vitals"""
        info = {}
        text_lower = text.lower()
        
        # Age - multiple patterns
        age_patterns = [
            r'(\d{1,3})[\-\s]year[\-\s]old',
            r'age[:\s]*(\d{1,3})',
            r'aged[:\s]*(\d{1,3})',
            r'(\d{1,3})[\-\s]yo',
            r'(\d{1,3})[\-\s]y[/\s]o',
            r'(\d{1,3})[\-\s]*year',
            r'(\d{1,3})[\-\s]*years'
        ]
        
        for pattern in age_patterns:
            matches = re.findall(pattern, text_lower)
            if matches:
                info['age'] = matches[0]
                break
        
        # Gender
        if re.search(r'\bmale\b|\bman\b|\bgentleman\b|\bboy\b|\bm\b', text_lower):
            info['gender'] = 'Male'
        elif re.search(r'\bfemale\b|\bwoman\b|\blady\b|\bgirl\b|\bf\b', text_lower):
            info['gender'] = 'Female'
        
        # Vital signs
        vitals = {}
        
        # Blood pressure
        bp_match = re.search(r'bp[:\s]*(\d{2,3})\s*[/\s]\s*(\d{2,3})', text, re.IGNORECASE)
        if not bp_match:
            bp_match = re.search(r'blood pressure[:\s]*(\d{2,3})\s*[/\s]\s*(\d{2,3})', text, re.IGNORECASE)
        
        if bp_match:
            systolic, diastolic = bp_match.groups()
            vitals['blood_pressure'] = f"{systolic}/{diastolic} mmHg"
            # Classify BP
            if int(systolic) >= 140 or int(diastolic) >= 90:
                vitals['bp_status'] = 'High'
            elif int(systolic) <= 90 or int(diastolic) <= 60:
                vitals['bp_status'] = 'Low'
            else:
                vitals['bp_status'] = 'Normal'
        
        # Heart rate
        hr_match = re.search(r'hr[:\s]*(\d{2,3})', text, re.IGNORECASE)
        if not hr_match:
            hr_match = re.search(r'heart rate[:\s]*(\d{2,3})', text, re.IGNORECASE)
        if not hr_match:
            hr_match = re.search(r'pulse[:\s]*(\d{2,3})', text, re.IGNORECASE)
        
        if hr_match:
            hr = hr_match.group(1)
            vitals['heart_rate'] = f"{hr} bpm"
        
        # Temperature
        temp_match = re.search(r'temp[:\s]*(\d{2,3}\.?\d?)', text, re.IGNORECASE)
        if not temp_match:
            temp_match = re.search(r'temperature[:\s]*(\d{2,3}\.?\d?)', text, re.IGNORECASE)
        
        if temp_match:
            temp = temp_match.group(1)
            vitals['temperature'] = f"{temp}°C"
            if float(temp) > 37.5:
                vitals['temp_status'] = 'Fever'
        
        if vitals:
            info['vitals'] = vitals
        
        return info
    
    def _create_summary(self, text, sentences):
        """Create a concise clinical summary"""
        if not sentences:
            return text[:200] + "..." if len(text) > 200 else text
        
        # Look for diagnosis/treatment sentences
        key_phrases = [
            'diagnosis', 'diagnosed', 'presented with', 'complains of',
            'symptoms', 'treatment', 'prescribed', 'recommended',
            'advised', 'findings', 'assessment', 'plan'
        ]
        
        important_sentences = []
        for sentence in sentences:
            sentence_lower = sentence.lower()
            # Check for key phrases
            if any(phrase in sentence_lower for phrase in key_phrases):
                important_sentences.append(sentence)
            
            # Check for medical terms
            medical_terms_in_sentence = sum(1 for term in self.medical_terms[:20] if term in sentence_lower)
            if medical_terms_in_sentence >= 2:
                important_sentences.append(sentence)
        
        if important_sentences:
            # Use important sentences for summary
            summary = " ".join(important_sentences[:3])
        elif len(sentences) >= 3:
            # Use first and last sentences
            summary = sentences[0] + " " + sentences[-1]
        else:
            # Use all sentences
            summary = " ".join(sentences)
        
        # Trim if too long
        if len(summary) > 300:
            summary = summary[:297] + "..."
        
        return summary
    
    def _extract_keywords(self, words):
        """Extract significant keywords"""
        # Common words to ignore
        ignore_words = {
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to',
            'for', 'of', 'with', 'by', 'as', 'is', 'are', 'was', 'were',
            'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does',
            'did', 'will', 'would', 'should', 'could', 'can', 'may',
            'might', 'must', 'shall', 'this', 'that', 'these', 'those',
            'patient', 'patients', 'doctor', 'dr', 'hospital', 'clinic',
            'said', 'says', 'report', 'note', 'history'
        }
        
        # Filter and clean words
        filtered_words = []
        for word in words:
            # Remove punctuation and convert to lowercase
            clean_word = re.sub(r'[^\w\s]', '', word).lower()
            if (clean_word not in ignore_words and 
                len(clean_word) > 2 and 
                clean_word.isalpha()):
                filtered_words.append(clean_word)
        
        # Count frequency
        if not filtered_words:
            return ["No significant keywords found"]
        
        word_counts = Counter(filtered_words)
        
        # Get top keywords with frequency
        keywords = []
        for word, count in word_counts.most_common(15):
            # Skip very common medical words that aren't specific
            common_medical = {'pain', 'patient', 'history', 'treatment'}
            if word not in common_medical:
                keywords.append(f"{word.title()} ({count}x)")
        
        return keywords[:10] if keywords else ["No keywords extracted"]
    
    def _find_potential_diagnoses(self, text):
        """Identify potential medical diagnoses"""
        text_lower = text.lower()
        diagnoses_found = []
        
        # Look for diagnosis patterns
        patterns = [
            (r'diagnosis[:\s]+([^\.\n]+)', "Diagnosis"),
            (r'diagnosed with ([^\.\n]+)', "Diagnosed with"),
            (r'suffering from ([^\.\n]+)', "Suffering from"),
            (r'presented with ([^\.\n]+)', "Presented with"),
            (r'complains of ([^\.\n]+)', "Complains of"),
            (r'symptoms of ([^\.\n]+)', "Symptoms of"),
            (r'history of ([^\.\n]+)', "History of")
        ]
        
        for pattern, label in patterns:
            matches = re.findall(pattern, text_lower)
            for match in matches:
                diagnosis = match.strip()
                if diagnosis and len(diagnosis) > 3:
                    diagnoses_found.append(f"{label}: {diagnosis.title()}")
        
        # Also check for known diagnoses in text
        for diagnosis in self.diagnoses:
            if re.search(r'\b' + re.escape(diagnosis) + r'\b', text_lower):
                diagnoses_found.append(f"Suspected: {diagnosis.title()}")
        
        # Remove duplicates and limit
        unique_diagnoses = []
        seen = set()
        for d in diagnoses_found:
            if d not in seen:
                seen.add(d)
                unique_diagnoses.append(d)
        
        return unique_diagnoses[:5]
    
    def _calculate_confidence(self, text):
        """Calculate confidence score based on medical content"""
        text_lower = text.lower()
        
        # Count medical terms found
        medical_term_count = sum(1 for term in self.medical_terms if term in text_lower)
        medication_count = sum(1 for med in self.medications if med in text_lower)
        diagnosis_count = sum(1 for diag in self.diagnoses if diag in text_lower)
        
        total_medical_content = medical_term_count + medication_count + diagnosis_count
        
        # Determine confidence
        if total_medical_content >= 10:
            return "High"
        elif total_medical_content >= 5:
            return "Medium"
        elif total_medical_content >= 2:
            return "Low"
        else:
            return "Very Low"
    
    def _empty_result(self, message="No text provided"):
        """Return empty result structure"""
        return {
            'summary': message,
            'sentence_count': 0,
            'word_count': 0,
            'medical_terms': [],
            'medications': [],
            'diagnoses': [],
            'patient_info': {},
            'keywords': [message],
            'potential_diagnoses': [],
            'analysis_type': 'No Analysis',
            'confidence': 'None'
        }