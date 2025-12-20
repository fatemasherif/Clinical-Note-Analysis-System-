<?php
require_once __DIR__ . '/BaseTemplate.php';

class Analyze extends BaseTemplate {
    protected $result;

    public function __construct($result = null) {
        $this->result = $result;
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
            // Show results inline
            $html .= '<div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px;">';
            $html .= '<h3 style="color: #0056b3;">📊 Analysis Results</h3>';
            
            if (isset($this->result['summary'])) {
                $html .= '<div style="margin: 15px 0;"><strong>Summary:</strong><br>' . htmlspecialchars($this->result['summary']) . '</div>';
            }
            
            if (!empty($this->result['medical_terms'])) {
                $html .= '<div style="margin: 15px 0;"><strong>Medical Terms:</strong><br>';
                foreach ($this->result['medical_terms'] as $term) {
                    $html .= '<span style="background: #d4edda; padding: 4px 8px; margin: 4px; border-radius: 4px; display: inline-block;">' . htmlspecialchars($term) . '</span>';
                }
                $html .= '</div>';
            }
            
            if (!empty($this->result['keywords'])) {
                $html .= '<div style="margin: 15px 0;"><strong>Keywords:</strong><br>';
                foreach ($this->result['keywords'] as $keyword) {
                    $html .= '<span style="background: #fff3cd; padding: 4px 8px; margin: 4px; border-radius: 4px; display: inline-block;">' . htmlspecialchars($keyword) . '</span>';
                }
                $html .= '</div>';
            }
            
            $html .= '<div style="margin-top: 20px;"><a href="analyze.php" class="btn-primary">Analyze Another File</a></div>';
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
  width: 450px;
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