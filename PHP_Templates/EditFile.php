<?php
require_once __DIR__ . '/BaseTemplate.php';

class EditFile extends BaseTemplate {
    protected $filename;
    protected $role;
    protected $fileContent;
    protected $message;

    public function __construct($filename = '', $role = '', $fileContent = '', $message = '') {
        $this->filename = $filename;
        $this->role = $role;
        $this->fileContent = $fileContent;
        $this->message = $message;
        $title = 'Edit ' . $this->filename;
        $content = $this->buildContent();
        parent::__construct($title, $content);
    }

    private function buildContent() {
        $html = '<div class="view-uploads-container">
  <h2 class="section-title">✏️ Edit File: ' . htmlspecialchars($this->filename) . '</h2>';
        
        if ($this->message) {
            $html .= '<p class="message">' . htmlspecialchars($this->message) . '</p>';
        }
        
        $html .= '
  <form method="POST" action="edit_file.php">
    <input type="hidden" name="filename" value="' . htmlspecialchars($this->filename) . '">
    <input type="hidden" name="uploader_role" value="' . htmlspecialchars($this->role) . '">
    <textarea name="content" rows="15" style="width:100%;padding:10px;border-radius:8px;border:1px solid #ccc;">' . htmlspecialchars($this->fileContent) . '</textarea>
    <button type="submit" class="upload-btn" style="margin-top:15px;">💾 Save Changes</button>
  </form>
</div>';
        return $html;
    }
}
?>