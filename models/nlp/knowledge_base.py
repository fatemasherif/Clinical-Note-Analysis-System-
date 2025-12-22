"""
Medical Knowledge Base
Contains medical terms, medications, diagnoses, and patterns
"""


class MedicalKnowledgeBase:
    """Centralized medical knowledge base"""
    
    def __init__(self):
        self.medical_terms = self._load_medical_terms()
        self.medications = self._load_medications()
        self.diagnoses = self._load_diagnoses()
        self.symptoms = self._load_symptoms()
        self.vital_patterns = self._load_vital_patterns()
    
    def _load_medical_terms(self):
        """Load comprehensive medical terminology"""
        return [
            # Symptoms
            'pain', 'fever', 'headache', 'nausea', 'vomiting', 'fatigue', 'weakness',
            'cough', 'shortness of breath', 'dyspnea', 'dizziness', 'vertigo',
            'swelling', 'edema', 'rash', 'itching', 'bleeding', 'hemorrhage',
            'bruising', 'palpitations', 'tachycardia', 'bradycardia',
            'hypertension', 'high blood pressure', 'hypotension', 'low blood pressure',
            'weight loss', 'weight gain', 'constipation', 'diarrhea', 'dysuria',
            'hematuria', 'blood in urine', 'polyuria', 'frequent urination',
            'oliguria', 'reduced urine', 'anuria', 'no urine', 'jaundice',
            'yellow skin', 'anorexia', 'loss of appetite', 'chest pain', 'angina',
            'syncope', 'seizure', 'convulsion', 'tremor', 'numbness', 'tingling',
            'blurred vision', 'photophobia', 'tinnitus', 'hearing loss'
        ]
    
    def _load_medications(self):
        """Load comprehensive medication list"""
        return [
            # Cardiovascular
            'amlodipine', 'lisinopril', 'atenolol', 'metoprolol', 'propranolol',
            'furosemide', 'lasix', 'hydrochlorothiazide', 'spironolactone',
            'warfarin', 'aspirin', 'clopidogrel', 'atorvastatin', 'simvastatin',
            
            # Diabetes
            'metformin', 'insulin', 'glipizide', 'glyburide', 'pioglitazone',
            
            # Antibiotics
            'amoxicillin', 'azithromycin', 'penicillin', 'cephalexin', 'ciprofloxacin',
            'doxycycline', 'trimethoprim', 'sulfamethoxazole',
            
            # Pain/Inflammation
            'ibuprofen', 'naproxen', 'acetaminophen', 'paracetamol', 'aspirin',
            'prednisone', 'dexamethasone', 'methylprednisolone',
            
            # GI
            'omeprazole', 'pantoprazole', 'ranitidine', 'famotidine',
            'lansoprazole', 'esomeprazole',
            
            # Respiratory
            'albuterol', 'salbutamol', 'ipratropium', 'montelukast',
            
            # Mental Health
            'sertraline', 'fluoxetine', 'citalopram', 'escitalopram',
            'lorazepam', 'alprazolam', 'diazepam'
        ]
    
    def _load_diagnoses(self):
        """Load comprehensive diagnosis list"""
        return [
            # Cardiovascular
            'hypertension', 'high blood pressure', 'hypotension', 'low blood pressure',
            'myocardial infarction', 'heart attack', 'angina', 'congestive heart failure',
            'chf', 'arrhythmia', 'atrial fibrillation', 'afib',
            
            # Respiratory
            'asthma', 'chronic obstructive pulmonary disease', 'copd', 'pneumonia',
            'bronchitis', 'influenza', 'flu', 'covid-19', 'coronavirus',
            'pulmonary embolism', 'pe',
            
            # Endocrine
            'diabetes mellitus', 'type 2 diabetes', 'type 1 diabetes', 'diabetes',
            'hypothyroidism', 'hyperthyroidism', 'thyroid disorder',
            
            # GI
            'gastroenteritis', 'gastroesophageal reflux disease', 'gerd',
            'peptic ulcer', 'gastritis', 'hepatitis', 'cirrhosis',
            'cholecystitis', 'pancreatitis',
            
            # Neurological
            'migraine', 'stroke', 'cva', 'seizure disorder', 'epilepsy',
            'parkinson', 'alzheimer', 'dementia',
            
            # Musculoskeletal
            'arthritis', 'osteoarthritis', 'rheumatoid arthritis', 'gout',
            'fibromyalgia', 'osteoporosis',
            
            # Renal
            'chronic kidney disease', 'ckd', 'kidney failure', 'renal failure',
            'urinary tract infection', 'uti', 'nephritis',
            
            # Mental Health
            'depression', 'anxiety disorder', 'bipolar', 'schizophrenia',
            'ptsd', 'post traumatic stress disorder',
            
            # Other
            'anemia', 'iron deficiency anemia', 'cancer', 'tumor', 'malignancy'
        ]
    
    def _load_symptoms(self):
        """Load symptom patterns"""
        return {
            'respiratory': ['cough', 'shortness of breath', 'dyspnea', 'wheezing', 'chest tightness'],
            'cardiovascular': ['chest pain', 'palpitations', 'dizziness', 'syncope', 'edema'],
            'gastrointestinal': ['nausea', 'vomiting', 'abdominal pain', 'diarrhea', 'constipation'],
            'neurological': ['headache', 'dizziness', 'seizure', 'numbness', 'tingling'],
            'musculoskeletal': ['joint pain', 'back pain', 'muscle pain', 'stiffness']
        }
    
    def _load_vital_patterns(self):
        """Load patterns for vital signs extraction"""
        return {
            'blood_pressure': [
                r'bp[:\s]*(\d{2,3})\s*[/\s]\s*(\d{2,3})',
                r'blood pressure[:\s]*(\d{2,3})\s*[/\s]\s*(\d{2,3})',
                r'(\d{2,3})\s*/\s*(\d{2,3})\s*mmhg'
            ],
            'heart_rate': [
                r'hr[:\s]*(\d{2,3})',
                r'heart rate[:\s]*(\d{2,3})',
                r'pulse[:\s]*(\d{2,3})',
                r'(\d{2,3})\s*bpm'
            ],
            'temperature': [
                r'temp[:\s]*(\d{2,3}\.?\d?)',
                r'temperature[:\s]*(\d{2,3}\.?\d?)',
                r'(\d{2,3}\.?\d?)\s*°[cf]'
            ],
            'oxygen_saturation': [
                r'o2[:\s]*(\d{2,3})',
                r'spo2[:\s]*(\d{2,3})',
                r'oxygen[:\s]*(\d{2,3})',
                r'(\d{2,3})\s*%'
            ]
        }
    
    def get_medical_terms(self):
        return self.medical_terms
    
    def get_medications(self):
        return self.medications
    
    def get_diagnoses(self):
        return self.diagnoses
    
    def get_symptoms(self):
        return self.symptoms
    
    def get_vital_patterns(self):
        return self.vital_patterns

