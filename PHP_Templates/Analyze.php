<?php
require_once __DIR__ . '/BaseTemplate.php';

class Analyze extends BaseTemplate {
    protected $result;

    protected $serviceStatus;

    public function __construct($result = null, $serviceStatus = false) {
        $this->result = $result;
        $this->serviceStatus = $serviceStatus;
        $title = 'Analyze Clinical Notes';
        $content = $this->buildContent();
        parent::__construct($title, $content);
    }

    private function buildContent() {
        $html = '<div class="analyze-container">
  <div class="analyze-card">
    <h2>🧠 Analyze Uploaded Notes</h2>
    <p>Select a file to analyze using AI NLP tools.</p>';

        if ($this->result) {
            // Service status indicator
            if (isset($this->result['service_used'])) {
                $serviceColor = $this->serviceStatus ? '#28a745' : '#ffc107';
                $html .= '<div style="margin-bottom: 15px; padding: 10px; background: ' . $serviceColor . '; color: white; border-radius: 5px; text-align: center; font-weight: bold;">';
                $html .= '🔧 ' . htmlspecialchars($this->result['service_used']);
                $html .= '</div>';
            }
            
            // Show results inline
            $html .= '<div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px; max-width: 800px; margin-left: auto; margin-right: auto;">';
            $html .= '<h3 style="color: #0056b3;">📊 Analysis Results</h3>';
            
            // Statistics
            if (isset($this->result['sentence_count']) || isset($this->result['word_count'])) {
                $html .= '<div style="margin: 15px 0; padding: 10px; background: white; border-radius: 5px;">';
                $html .= '<strong>📈 Statistics:</strong> ';
                if (isset($this->result['word_count'])) {
                    $html .= 'Words: ' . htmlspecialchars($this->result['word_count']) . ' | ';
                }
                if (isset($this->result['sentence_count'])) {
                    $html .= 'Sentences: ' . htmlspecialchars($this->result['sentence_count']);
                }
                if (isset($this->result['confidence'])) {
                    $html .= ' | Confidence: <strong>' . htmlspecialchars($this->result['confidence']) . '</strong>';
                }
                $html .= '</div>';
            }
            
            // Summary
            if (isset($this->result['summary'])) {
                $html .= '<div style="margin: 15px 0; padding: 15px; background: white; border-left: 4px solid #0056b3; border-radius: 5px;">';
                $html .= '<strong>📝 Summary:</strong><br>' . htmlspecialchars($this->result['summary']);
                $html .= '</div>';
            }
            
            // Patient Information
            if (!empty($this->result['patient_info'])) {
                $html .= '<div style="margin: 15px 0; padding: 15px; background: white; border-radius: 5px;">';
                $html .= '<strong>👤 Patient Information:</strong><br>';
                foreach ($this->result['patient_info'] as $key => $value) {
                    if (!is_array($value)) {
                        $html .= '<span style="display: inline-block; margin: 5px; padding: 5px 10px; background: #e7f3ff; border-radius: 4px;">';
                        $html .= '<strong>' . htmlspecialchars(ucfirst(str_replace('_', ' ', $key))) . ':</strong> ' . htmlspecialchars($value);
                        $html .= '</span>';
                    }
                }
                $html .= '</div>';
            }
            
            // Vital Signs
            if (!empty($this->result['vitals'])) {
                $html .= '<div style="margin: 15px 0; padding: 15px; background: white; border-radius: 5px;">';
                $html .= '<strong>💓 Vital Signs:</strong><br>';
                foreach ($this->result['vitals'] as $key => $value) {
                    $html .= '<span style="display: inline-block; margin: 5px; padding: 5px 10px; background: #fff3cd; border-radius: 4px;">';
                    $html .= '<strong>' . htmlspecialchars(ucfirst(str_replace('_', ' ', $key))) . ':</strong> ' . htmlspecialchars($value);
                    $html .= '</span>';
                }
                $html .= '</div>';
            }
            
            // Diagnoses
            if (!empty($this->result['diagnoses'])) {
                $html .= '<div style="margin: 15px 0; padding: 15px; background: white; border-radius: 5px;">';
                $html .= '<strong>🏥 Diagnoses:</strong><br>';
                foreach ($this->result['diagnoses'] as $diagnosis) {
                    $html .= '<span style="background: #f8d7da; padding: 6px 12px; margin: 4px; border-radius: 4px; display: inline-block; color: #721c24;">' . htmlspecialchars($diagnosis) . '</span>';
                }
                $html .= '</div>';
            }
            
            // Medications
            if (!empty($this->result['medications'])) {
                $html .= '<div style="margin: 15px 0; padding: 15px; background: white; border-radius: 5px;">';
                $html .= '<strong>💊 Medications:</strong><br>';
                foreach ($this->result['medications'] as $med) {
                    if (is_array($med)) {
                        $medName = htmlspecialchars($med['name'] ?? 'Unknown');
                        $medDosage = isset($med['dosage']) ? ' (' . htmlspecialchars($med['dosage']) . ')' : '';
                        $html .= '<span style="background: #d1ecf1; padding: 6px 12px; margin: 4px; border-radius: 4px; display: inline-block;">' . $medName . $medDosage . '</span>';
                    } else {
                        $html .= '<span style="background: #d1ecf1; padding: 6px 12px; margin: 4px; border-radius: 4px; display: inline-block;">' . htmlspecialchars($med) . '</span>';
                    }
                }
                $html .= '</div>';
            }
            
            // Medical Terms
            if (!empty($this->result['medical_terms'])) {
                $html .= '<div style="margin: 15px 0; padding: 15px; background: white; border-radius: 5px;">';
                $html .= '<strong>🔬 Medical Terms:</strong><br>';
                foreach ($this->result['medical_terms'] as $term) {
                    $html .= '<span style="background: #d4edda; padding: 4px 8px; margin: 4px; border-radius: 4px; display: inline-block;">' . htmlspecialchars($term) . '</span>';
                }
                $html .= '</div>';
            }
            
            // Potential Diagnoses
            if (!empty($this->result['potential_diagnoses'])) {
                $html .= '<div style="margin: 15px 0; padding: 15px; background: white; border-radius: 5px;">';
                $html .= '<strong>🔍 Potential Diagnoses:</strong><br>';
                foreach ($this->result['potential_diagnoses'] as $potential) {
                    $html .= '<span style="background: #e2e3e5; padding: 4px 8px; margin: 4px; border-radius: 4px; display: inline-block;">' . htmlspecialchars($potential) . '</span>';
                }
                $html .= '</div>';
            }
            
            // Risk Factors
            if (!empty($this->result['risk_factors'])) {
                $html .= '<div style="margin: 15px 0; padding: 15px; background: white; border-radius: 5px;">';
                $html .= '<strong>⚠️ Risk Factors:</strong><br>';
                foreach ($this->result['risk_factors'] as $risk) {
                    $html .= '<span style="background: #fff3cd; padding: 4px 8px; margin: 4px; border-radius: 4px; display: inline-block; color: #856404;">' . htmlspecialchars($risk) . '</span>';
                }
                $html .= '</div>';
            }
            
            // Recommendations
            if (!empty($this->result['recommendations'])) {
                $html .= '<div style="margin: 15px 0; padding: 15px; background: white; border-radius: 5px;">';
                $html .= '<strong>💡 Recommendations:</strong><br><ul style="margin: 10px 0; padding-left: 20px;">';
                foreach ($this->result['recommendations'] as $rec) {
                    $html .= '<li style="margin: 5px 0;">' . htmlspecialchars($rec) . '</li>';
                }
                $html .= '</ul></div>';
            }
            
            // Keywords
            if (!empty($this->result['keywords'])) {
                $html .= '<div style="margin: 15px 0; padding: 15px; background: white; border-radius: 5px;">';
                $html .= '<strong>🔑 Keywords:</strong><br>';
                foreach ($this->result['keywords'] as $keyword) {
                    $html .= '<span style="background: #f0f0f0; padding: 4px 8px; margin: 4px; border-radius: 4px; display: inline-block;">' . htmlspecialchars($keyword) . '</span>';
                }
                $html .= '</div>';
            }
            
            $html .= '<div style="margin-top: 20px; text-align: center;"><a href="analyze.php" class="btn-primary">Analyze Another File</a></div>';
            $html .= '</div>';
        } else {
            $html .= '    <form id="analyzeForm" action="analyze.php" method="POST" enctype="multipart/form-data">
      <input type="file" name="file" id="file" accept=".txt,.pdf,.docx" required>

      <button type="submit" class="btn-primary">Analyze</button>
    </form>

    <!-- Hidden message that appears only while analyzing -->
    <div id="loadingMessage" class="loading-message">Analyzing... Please wait ⏳</div>
  </div>
</div>

<style>
/* ====== PAGE LAYOUT ====== */
body {
  font-family: "Poppins", sans-serif;
  background-color: #f4f8fc;
  color: #333;
  margin: 0;
  padding: 0;
}

.analyze-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 80vh;
}

.analyze-card {
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  padding: 40px;
  width: 90%;
  max-width: 800px;
  text-align: center;
}

.analyze-card h2 {
  color: #0056b3;
  margin-bottom: 15px;
}

.analyze-card p {
  color: #555;
  margin-bottom: 25px;
}

/* ====== BUTTON STYLING ====== */
.btn-primary {
  background-color: #007bff;
  border: none;
  color: #fff;
  padding: 10px 25px;
  border-radius: 8px;
  font-size: 16px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  background-color: #0056b3;
}

/* ====== LOADING MESSAGE ====== */
.loading-message {
  margin-top: 20px;
  color: #0056b3;
  font-weight: 500;
  display: none; /* hidden initially */
}
</style>

<script>
  // Show the "Analyzing..." message only when the form is submitted
  document.getElementById("analyzeForm").addEventListener("submit", function() {
    document.getElementById("loadingMessage").style.display = "block";
  });
  </script>';
            $html .= '  </div>
</div>';
        }
        
        return $html;
    }
}
?>