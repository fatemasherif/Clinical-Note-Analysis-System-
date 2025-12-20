<?php
require_once __DIR__ . '/BaseTemplate.php';

class ViewUploads extends BaseTemplate {
    protected $files;
    protected $role;
    protected $message;

    public function __construct($files = [], $message = '') {
        $this->files = $files;
        $this->message = $message;
        $this->role = $_SESSION['role'] ?? '';
        $title = 'View Uploaded Notes';
        $content = $this->buildContent();
        parent::__construct($title, $content);
    }

    private function buildContent() {
        $html = '<div class="view-uploads-container">
  <h2 class="section-title">📁 Uploaded Clinical Notes</h2>';
        
        if ($this->message) {
            $html .= '<p class="message">' . htmlspecialchars($this->message) . '</p>';
        }

        if (!empty($this->files)) {
            $html .= '<table class="uploads-table">
    <thead>
      <tr>
        <th>Filename</th>
        <th>Uploader</th>
        <th>Role</th>
        <th>Uploaded At</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>';
            foreach ($this->files as $file) {
                $html .= '<tr>
        <td>' . htmlspecialchars($file['filename']) . '</td>
        <td>' . htmlspecialchars($file['uploader']) . '</td>
        <td>' . htmlspecialchars($file['role']) . '</td>
        <td>' . htmlspecialchars($file['uploaded_at']) . '</td>
        <td>
  <!-- Download button - Always visible -->
  <a href="view_uploads.php?download=1&filename=' . urlencode($file['filename']) . '&uploader_role=' . urlencode($file['role']) . '" 
     class="download-btn">⬇️ Download</a>

  <!-- Edit button — only if: 
       1. User is doctor AND 
       2. File is .txt AND 
       3. (File is doctor\'s OR file is nurse\'s) -->
';
                if ($this->role == 'doctor' && str_ends_with($file['filename'], '.txt')) {
                    if ($file['role'] == 'doctor' || $file['role'] == 'nurse') {
                        $html .= '    <a href="edit_file.php?filename=' . urlencode($file['filename']) . '&uploader_role=' . urlencode($file['role']) . '" 
       class="download-btn" style="background:#ff9800;">
      ✏️ Edit
    </a>
';
                    }
                }

                $html .= '
  <!-- Delete button — only if:
       1. User is doctor AND
       2. (File is doctor\'s OR file is nurse\'s) -->
';
                if ($this->role == 'doctor') {
                    if ($file['role'] == 'doctor' || $file['role'] == 'nurse') {
                        $html .= '    <form action="view_uploads.php" method="POST" style="display:inline;">
      <input type="hidden" name="delete_filename" value="' . htmlspecialchars($file['filename']) . '">
      <input type="hidden" name="uploader_role" value="' . htmlspecialchars($file['role']) . '">
      <button type="submit" class="download-btn" style="background:#dc3545;" 
              onclick="return confirm(\'Are you sure you want to delete this file?\')">
        🗑️ Delete
      </button>
    </form>
';
                    }
                }
                $html .= '</td>
      </tr>';
            }
            $html .= '    </tbody>
  </table>';
        } else {
            $html .= '  <p class="no-files-msg">No files uploaded yet. 📄</p>';
        }
        $html .= '</div>';
        return $html;
    }
}
?>