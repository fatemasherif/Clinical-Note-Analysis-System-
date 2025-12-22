<?php
require_once __DIR__ . '/../models/Menu.php';

class BaseTemplate {
    protected $title;
    protected $content;

    public function __construct($title = 'Clinical Note System', $content = '') {
        $this->title = $title;
        $this->content = $content;
    }

    public function render() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $role = $_SESSION['role'] ?? '';
        $currentPage = Menu::getCurrentPage();
        $menu = new Menu($role, $currentPage);
        $menuHtml = $menu->render();
        
        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{$this->title}</title>
  <link rel="stylesheet" href="Static/style.css">
  <style>
    .nav-link.active {
      font-weight: bold;
      border-bottom: 2px solid #2563eb;
      padding-bottom: 4px;
    }
    .logout-link {
      color: #dc2626;
    }
    .logout-link:hover {
      color: #991b1b;
    }
  </style>
</head>
<body>
  <nav class="navbar">
    <div class="logo"><a href="index.php">Clinical Note System</a></div>
    {$menuHtml}
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