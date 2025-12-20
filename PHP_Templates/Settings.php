<?php
require_once __DIR__ . '/BaseTemplate.php';

class Settings extends BaseTemplate {
    protected $messages;

    public function __construct($messages = []) {
        $this->messages = $messages;
        $title = 'Settings';
        $content = $this->buildContent();
        parent::__construct($title, $content);
    }

    private function buildContent() {
        $html = '<div class="settings-container">
  <h2 class="section-title">Account Settings</h2>

  <!-- ✅ Flash messages -->';
        if (!empty($this->messages)) {
            foreach ($this->messages as $msg) {
                $category = $msg[0];
                $message = $msg[1];
                $html .= '        <div class="alert alert-' . htmlspecialchars($category) . '">' . htmlspecialchars($message) . '</div>';
            }
        }
        $html .= '
  <form method="POST" class="settings-form">
    <div class="form-group">
      <label for="new_password">Change Password</label>
      <input type="password" name="new_password" id="new_password" required>
    </div>
    <button type="submit" class="update-btn">Update</button>
  </form>
</div>';
        return $html;
    }
}
?>