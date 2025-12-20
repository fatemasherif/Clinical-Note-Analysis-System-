<?php
require_once __DIR__ . '/BaseTemplate.php';

class Upload extends BaseTemplate {
    protected $message;

    public function __construct($message = '') {
        $this->message = $message;
        $title = 'Upload Clinical Notes';
        $content = $this->buildContent();
        parent::__construct($title, $content);
    }

    private function buildContent() {
        $html = '<div class="upload-container">
  <h2>📄 Upload or Add a Note</h2>
  <p class="upload-subtitle">Upload a file or write a note directly to save as a .txt file.</p>';
        
        if ($this->message) {
            $html .= '<p class="message" style="color: ' . (strpos($this->message, 'success') !== false ? 'green' : 'red') . ';">' . htmlspecialchars($this->message) . '</p>';
        }
        
        $html .= '  <!-- File Upload Form - FIXED: Added missing action and method -->
  <form action="upload.php" method="POST" enctype="multipart/form-data" class="upload-form">
    <input type="file" name="file" class="file-input" required>
    <button type="submit" class="upload-btn">Upload File</button>
  </form>

  <hr style="margin: 20px 0;">

  <!-- Text Note Form - FIXED: Added correct action -->
  <form action="upload.php" method="POST" class="upload-form">
    <textarea name="note_content" rows="5" placeholder="Write your note here..." class="file-input" required></textarea>
    <input type="text" name="note_filename" placeholder="Optional filename (e.g. note1)" class="file-input">
    <button type="submit" class="upload-btn">Save Note as Text</button>
  </form>
</div>';
        return $html;
    }
}
?>