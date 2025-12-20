<?php
class SimpleNLP {
    private $medical_terms;
    private $medications;
    private $diagnoses;

    public function __construct() {
        $this->medical_terms = $this->_load_medical_terms();
        $this->medications = $this->_load_medications();
        $this->diagnoses = $this->_load_diagnoses();
    }

    private function _load_medical_terms() {
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
        ];
    }

    private function _load_medications() {
        return [
            'amlodipine', 'lisinopril', 'metformin', 'insulin', 'aspirin',
            'atorvastatin', 'simvastatin', 'warfarin', 'heparin', 'ibuprofen',
            'paracetamol', 'acetaminophen', 'antibiotics', 'penicillin',
            'amoxicillin', 'azithromycin', 'prednisone', 'dexamethasone',
            'omeprazole', 'pantoprazole', 'ranitidine', 'metoprolol',
            'propranolol', 'furosemide', 'lasix', 'hydrochlorothiazide'
        ];
    }

    private function _load_diagnoses() {
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
        ];
    }

    public function analyze_text($text) {
        if (!$text || strlen(trim($text)) < 10) {
            return $this->_empty_result("Text too short for analysis");
        }

        $text_clean = trim($text);

        // Basic statistics
        $words = explode(' ', strtolower($text_clean));
        $sentences = preg_split('/[.!?]+/', $text_clean, -1, PREG_SPLIT_NO_EMPTY);
        $sentences = array_map('trim', $sentences);

        // Extract all information
        $results = [
            'summary' => $this->_create_summary($text_clean, $sentences),
            'sentence_count' => count($sentences),
            'word_count' => count($words),
            'medical_terms' => $this->_extract_terms($text_clean, $this->medical_terms),
            'medications' => $this->_extract_terms($text_clean, $this->medications),
            'diagnoses' => $this->_extract_terms($text_clean, $this->diagnoses),
            'patient_info' => $this->_extract_patient_info($text_clean),
            'keywords' => $this->_extract_keywords($words),
            'potential_diagnoses' => $this->_find_potential_diagnoses($text_clean),
            'analysis_type' => 'Clinical NLP Analysis',
            'confidence' => $this->_calculate_confidence($text_clean)
        ];

        return $results;
    }

    private function _extract_terms($text, $term_list) {
        $text_lower = strtolower($text);
        $found_terms = [];

        foreach ($term_list as $term) {
            $pattern = '/\b' . preg_quote($term, '/') . '\b/';
            if (preg_match($pattern, $text_lower)) {
                $found_terms[] = $term;
            }
        }

        return array_unique($found_terms);
    }

    private function _extract_patient_info($text) {
        $info = [];
        $text_lower = strtolower($text);

        // Age
        $age_patterns = [
            '/(\d{1,3})[-\s]year[-\s]old/',
            '/age[:\s]*(\d{1,3})/',
            '/aged[:\s]*(\d{1,3})/',
            '/(\d{1,3})[-\s]yo/'
        ];
        foreach ($age_patterns as $pattern) {
            if (preg_match($pattern, $text_lower, $matches)) {
                $info['age'] = (int)$matches[1];
                break;
            }
        }

        // Gender
        if (preg_match('/\b(male|female|man|woman|boy|girl)\b/', $text_lower, $matches)) {
            $info['gender'] = ucfirst($matches[1]);
        }

        // Vitals - simplified
        if (preg_match('/bp[:\s]*(\d{2,3})\/(\d{2,3})/', $text_lower, $matches)) {
            $info['blood_pressure'] = $matches[1] . '/' . $matches[2];
        }

        if (preg_match('/temp(?:erature)?[:\s]*(\d{2,3}(?:\.\d)?)/', $text_lower, $matches)) {
            $info['temperature'] = $matches[1] . '°F';
        }

        return $info;
    }

    private function _extract_keywords($words) {
        $stop_words = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must', 'can', 'shall'];
        $keywords = array_filter($words, function($word) use ($stop_words) {
            return strlen($word) > 3 && !in_array($word, $stop_words);
        });
        $counts = array_count_values($keywords);
        arsort($counts);
        return array_slice(array_keys($counts), 0, 10);
    }

    private function _find_potential_diagnoses($text) {
        $text_lower = strtolower($text);
        $potential = [];

        // Simple rule-based diagnosis suggestions
        if (preg_match('/\b(shortness of breath|dyspnea)\b/', $text_lower)) {
            $potential[] = 'Respiratory issue';
        }
        if (preg_match('/\b(chest pain|angina)\b/', $text_lower)) {
            $potential[] = 'Cardiac issue';
        }
        if (preg_match('/\b(fever|cough)\b/', $text_lower)) {
            $potential[] = 'Infection';
        }
        if (preg_match('/\b(headache|dizziness)\b/', $text_lower)) {
            $potential[] = 'Neurological issue';
        }

        return array_unique($potential);
    }

    private function _create_summary($text, $sentences) {
        if (count($sentences) == 0) return "No sentences found.";

        // Simple summary: first sentence + key info
        $summary = $sentences[0];

        // Add patient info if found
        $patient_info = $this->_extract_patient_info($text);
        if (!empty($patient_info)) {
            $summary .= " Patient info: " . implode(', ', array_map(function($k, $v) { return "$k: $v"; }, array_keys($patient_info), $patient_info));
        }

        return $summary;
    }

    private function _calculate_confidence($text) {
        $score = 0;
        $text_lower = strtolower($text);

        // Increase score based on medical content
        $score += count($this->_extract_terms($text, $this->medical_terms)) * 5;
        $score += count($this->_extract_terms($text, $this->medications)) * 10;
        $score += count($this->_extract_terms($text, $this->diagnoses)) * 15;

        // Length factor
        $score += min(strlen($text) / 100, 20);

        return min($score, 100);
    }

    private function _empty_result($message) {
        return [
            'summary' => $message,
            'sentence_count' => 0,
            'word_count' => 0,
            'medical_terms' => [],
            'medications' => [],
            'diagnoses' => [],
            'patient_info' => [],
            'keywords' => [],
            'potential_diagnoses' => [],
            'analysis_type' => 'Clinical NLP Analysis',
            'confidence' => 0
        ];
    }
}
?>