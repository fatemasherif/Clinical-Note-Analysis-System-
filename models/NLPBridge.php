<?php
/**
 * PHP Bridge to Python NLP Service
 * Connects PHP application to Python-based NLP analysis
 */
class NLPBridge {
    private $apiUrl;
    
    public function __construct($apiUrl = 'http://127.0.0.1:5001') {
        $this->apiUrl = $apiUrl;
    }
    
    /**
     * Analyze clinical text using Python NLP service
     */
    public function analyze($text) {
        if (empty($text)) {
            return $this->_empty_result("No text provided");
        }
        
        // Try to use Python API first
        $result = $this->_callPythonAPI($text);
        if ($result !== false) {
            return $result;
        }
        
        // Fallback to PHP implementation if Python service is not available
        return $this->_fallbackAnalysis($text);
    }
    
    /**
     * Call Python NLP API
     * Uses cURL if available, otherwise falls back to file_get_contents
     */
    private function _callPythonAPI($text) {
        $url = $this->apiUrl . '/analyze';
        $data = json_encode(['text' => $text]);
        
        // Try cURL first if available
        if (function_exists('curl_init')) {
            return $this->_callWithCurl($url, $data);
        }
        
        // Fallback to file_get_contents
        return $this->_callWithFileGetContents($url, $data);
    }
    
    /**
     * Call API using cURL
     */
    private function _callWithCurl($url, $data) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            if ($result && !isset($result['error'])) {
                return $result;
            }
        }
        
        return false;
    }
    
    /**
     * Call API using file_get_contents (fallback when cURL not available)
     */
    private function _callWithFileGetContents($url, $data) {
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data)
                ],
                'content' => $data,
                'timeout' => 10
            ]
        ];
        
        $context = stream_context_create($options);
        
        // Suppress warnings for connection errors
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            return false;
        }
        
        $result = json_decode($response, true);
        if ($result && !isset($result['error'])) {
            return $result;
        }
        
        return false;
    }
    
    /**
     * Fallback to PHP implementation
     */
    private function _fallbackAnalysis($text) {
        require_once __DIR__ . '/SimpleNLP.php';
        $nlp = new SimpleNLP();
        return $nlp->analyze_text($text);
    }
    
    /**
     * Check if Python NLP service is available
     * Uses cURL if available, otherwise falls back to file_get_contents
     */
    public function isServiceAvailable() {
        $url = $this->apiUrl . '/health';
        
        // Try cURL first if available
        if (function_exists('curl_init')) {
            return $this->_checkHealthWithCurl($url);
        }
        
        // Fallback to file_get_contents
        return $this->_checkHealthWithFileGetContents($url);
    }
    
    /**
     * Check health using cURL
     */
    private function _checkHealthWithCurl($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200;
    }
    
    /**
     * Check health using file_get_contents (fallback when cURL not available)
     */
    private function _checkHealthWithFileGetContents($url) {
        $options = [
            'http' => [
                'method' => 'GET',
                'timeout' => 2
            ]
        ];
        
        $context = stream_context_create($options);
        
        // Suppress warnings for connection errors
        $response = @file_get_contents($url, false, $context);
        
        return $response !== false;
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
            'analysis_type' => 'No Analysis',
            'confidence' => 'None'
        ];
    }
}
?>

