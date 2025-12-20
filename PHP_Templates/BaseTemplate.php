<?php
class BaseTemplate {
    protected $title;
    protected $content;

    public function __construct($title = 'Clinical Note System', $content = '') {
        $this->title = $title;
        $this->content = $content;
    }

    public function render() {
        $role = $_SESSION['role'] ?? '';
        $dashboardLink = ($role == 'admin') ? 'admin_dashboard.php' : 'doctor_dashboard.php';
        
        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{$this->title}</title>
  <link rel="stylesheet" href="Static/style.css">
</head>
<body>
  <nav class="navbar">
    <div class="logo"><a href="index.php">Clinical Note System</a></div>
    <ul class="nav-links">
      <li><a class="nav-link" href="{$dashboardLink}">Dashboard</a></li>
      <li><a class="nav-link" href="upload.php">Upload</a></li>
      <li><a class="nav-link" href="logout.php">Logout</a></li>
    </ul>
  </nav>
  <main class="container">
    {$this->content}
  </main>
  <footer class="footer">
    <div class="footer-content">
      <span>© 2025 Clinical Note Analysis</span>
    </div>
  </footer>
</body>
</html>
HTML;
    }
}
?>