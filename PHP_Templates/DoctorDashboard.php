<?php
require_once __DIR__ . '/BaseTemplate.php';

class DoctorDashboard extends BaseTemplate {
    protected $username;
    protected $role;

    public function __construct($username = '', $role = '') {
        $this->username = $username;
        $this->role = $role;
        $title = 'Doctor Dashboard';
        $content = '<div class="doctor-dashboard">

  <h2 class="dashboard-title">
';
        if ($this->role == 'doctor') {
            $content .= '    👨‍⚕️ Doctor Dashboard
';
        } else {
            $content .= '    👩‍⚕️ Nurse Dashboard
';
        }
        $content .= '  </h2>
  <p class="welcome-msg">Welcome, <strong>' . htmlspecialchars($this->username) . '</strong>. Manage clinical notes and access your tools below.</p>

  <div class="card-grid">
    <a href="upload.php" class="dashboard-card">
      <div class="icon">📄</div>
      <div class="card-content">
        <h3>Upload Clinical Notes</h3>
        <p>Submit new clinical notes for analysis.</p>
      </div>
    </a>

    <a href="view_uploads.php" class="dashboard-card">
      <div class="icon">📁</div>
      <div class="card-content">
        <h3>View Uploaded Notes</h3>
        <p>Access and manage previously uploaded files.</p>
      </div>
    </a>

    <!-- CHANGED: Now available for BOTH doctors and nurses -->
    <a href="analyze.php" class="dashboard-card">
      <div class="icon">🧠</div>
      <div class="card-content">
        <h3>Analyze with AI</h3>
        <p>Use NLP to extract insights from clinical notes.</p>
      </div>
    </a>

    <a href="settings.php" class="dashboard-card">
      <div class="icon">⚙️</div>
      <div class="card-content">
        <h3>Settings</h3>
        <p>Manage your account preferences.</p>
      </div>
    </a>
  </div>

</div>';
        parent::__construct($title, $content);
    }
}
?>