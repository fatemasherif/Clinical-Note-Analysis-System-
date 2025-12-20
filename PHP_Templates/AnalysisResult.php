<?php
require_once __DIR__ . '/BaseTemplate.php';

class AnalysisResult extends BaseTemplate {
    protected $results;

    public function __construct($results = []) {
        $this->results = $results;
        $title = 'Analysis Results';
        $content = $this->buildContent();
        parent::__construct($title, $content);
    }

    private function buildContent() {
        $html = '<div class="view-uploads-container">
    <h2 class="section-title">📊 Analysis Results: ' . htmlspecialchars($this->results['filename'] ?? '') . '</h2>
    
    <div style="background: #e8f4fd; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <p><strong>Analysis Type:</strong> ' . htmlspecialchars($this->results['analysis_type'] ?? '') . '</p>
        <p><strong>Confidence:</strong> ' . htmlspecialchars($this->results['confidence'] ?? '') . '</p>
        <p><strong>Words:</strong> ' . htmlspecialchars($this->results['word_count'] ?? '') . ' | <strong>Sentences:</strong> ' . htmlspecialchars($this->results['sentence_count'] ?? '') . '</p>';
        if (!empty($this->results['saved_as'])) {
            $html .= '        <p><strong>✅ Saved as:</strong> ' . htmlspecialchars($this->results['saved_as']) . '</p>';
        }
        $html .= '    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        
        <!-- Summary Card -->
        <div class="result-card">
            <h3>📝 Summary</h3>
            <div style="background: white; padding: 15px; border-radius: 6px; border-left: 4px solid #007bff;">
                ' . htmlspecialchars($this->results['summary'] ?? '') . '
            </div>
        </div>
        
        <!-- Medical Terms Card -->
        <div class="result-card">
            <h3>🏥 Medical Terms Found (' . count($this->results['medical_terms'] ?? []) . ')</h3>
            <div style="background: white; padding: 15px; border-radius: 6px; border-left: 4px solid #28a745;">';
        if (!empty($this->results['medical_terms'])) {
            $html .= '                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">';
            foreach ($this->results['medical_terms'] as $term) {
                $html .= '                            <span style="background: #d4edda; padding: 4px 8px; border-radius: 4px; font-size: 0.9rem;">
                                ' . htmlspecialchars($term) . '
                            </span>';
            }
            $html .= '                    </div>';
        } else {
            $html .= '                    <p style="color: #666;">No medical terms identified.</p>';
        }
        $html .= '            </div>
        </div>
        
        <!-- Keywords Card -->
        <div class="result-card">
            <h3>🔑 Keywords</h3>
            <div style="background: white; padding: 15px; border-radius: 6px; border-left: 4px solid #ffc107;">';
        if (!empty($this->results['keywords'])) {
            $html .= '                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">';
            foreach ($this->results['keywords'] as $keyword) {
                $html .= '                            <span style="background: #fff3cd; padding: 4px 8px; border-radius: 4px; font-size: 0.9rem;">
                                ' . htmlspecialchars($keyword) . '
                            </span>';
            }
            $html .= '                    </div>';
        } else {
            $html .= '                    <p style="color: #666;">No keywords extracted.</p>';
        }
        $html .= '            </div>
        </div>
        
        <!-- Patient Info Card -->';
        if (!empty($this->results['patient_info'])) {
            $html .= '        <div class="result-card">
            <h3>👤 Patient Information</h3>
            <div style="background: white; padding: 15px; border-radius: 6px; border-left: 4px solid #dc3545;">
                <ul style="margin: 0; padding-left: 20px;">';
            foreach ($this->results['patient_info'] as $key => $value) {
                if (!empty($value)) {
                    $html .= '                        <li><strong>' . htmlspecialchars(ucwords(str_replace('_', ' ', $key))) . ':</strong> ';
                    if (is_array($value)) {
                        $subs = [];
                        foreach ($value as $subkey => $subvalue) {
                            $subs[] = htmlspecialchars($subkey) . ': ' . htmlspecialchars($subvalue);
                        }
                        $html .= implode(', ', $subs);
                    } else {
                        $html .= htmlspecialchars($value);
                    }
                    $html .= '</li>';
                }
            }
            $html .= '                </ul>
            </div>
        </div>';
        }
        
        $html .= '        
        <!-- Potential Diagnoses Card -->';
        if (!empty($this->results['potential_diagnoses'])) {
            $html .= '        <div class="result-card">
            <h3>🩺 Potential Diagnoses</h3>
            <div style="background: white; padding: 15px; border-radius: 6px; border-left: 4px solid #6f42c1;">
                <ul style="margin: 0; padding-left: 20px;">';
            foreach ($this->results['potential_diagnoses'] as $diagnosis) {
                $html .= '                        <li>' . htmlspecialchars($diagnosis) . '</li>';
            }
            $html .= '                </ul>
            </div>
        </div>';
        }
        
        $html .= '        
        <!-- Text Preview Card -->
        <div class="result-card">
            <h3>📄 Text Preview</h3>
            <div style="background: white; padding: 15px; border-radius: 6px; border-left: 4px solid #17a2b8; max-height: 200px; overflow-y: auto;">
                <pre style="white-space: pre-wrap; margin: 0; font-size: 0.9rem;">' . htmlspecialchars($this->results['original_text_preview'] ?? '') . '</pre>
            </div>
        </div>
        
    </div>
    
    <div style="margin-top: 30px; display: flex; gap: 10px;">
        <a href="analyze.php" class="download-btn" style="background-color: #007bff;">
            🔄 Analyze Another File
        </a>
        <a href="doctor_dashboard.php" class="download-btn" style="background-color: #6c757d;">
            ← Back to Dashboard
        </a>';
        if (!empty($this->results['saved_as'])) {
            $html .= '        <a href="view_uploads.php" class="download-btn" style="background-color: #28a745;">
            📁 View All Uploads
        </a>';
        }
        $html .= '    </div>
</div>

<style>
.result-card {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.result-card h3 {
    margin-top: 0;
    color: #333;
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 8px;
}
.download-btn {
    display: inline-block;
    background: #007BFF;
    color: white;
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.9rem;
    transition: background 0.3s;
}
.download-btn:hover {
    background: #0056b3;
    color: white;
    text-decoration: none;
}
</style>';
        return $html;
    }
}
?>