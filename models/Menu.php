<?php
class Menu {
    private $role;
    private $currentPage;
    
    public function __construct($role = '', $currentPage = '') {
        $this->role = $role;
        $this->currentPage = $currentPage;
    }
    
    /**
     * Get menu items based on role
     */
    public function getMenuItems() {
        $menuItems = [];
        
        // Common menu items for all authenticated users
        if ($this->role) {
            $menuItems[] = [
                'label' => 'Dashboard',
                'url' => $this->role == 'admin' ? 'admin_dashboard.php' : 'doctor_dashboard.php',
                'active' => $this->isActive('dashboard')
            ];
            
            $menuItems[] = [
                'label' => 'Upload',
                'url' => 'upload.php',
                'active' => $this->isActive('upload')
            ];
            
            $menuItems[] = [
                'label' => 'View Uploads',
                'url' => 'view_uploads.php',
                'active' => $this->isActive('view_uploads')
            ];
            
            $menuItems[] = [
                'label' => 'Settings',
                'url' => 'settings.php',
                'active' => $this->isActive('settings')
            ];
            
            // Feedback only for non-admin users
            if ($this->role != 'admin') {
                $menuItems[] = [
                    'label' => 'Feedback',
                    'url' => 'feedback.php',
                    'active' => $this->isActive('feedback')
                ];
            }
            
            // Admin-specific menu items
            if ($this->role == 'admin') {
                $menuItems[] = [
                    'label' => 'Manage Users',
                    'url' => 'admin_users.php',
                    'active' => $this->isActive('admin_users')
                ];
                
                $menuItems[] = [
                    'label' => 'Clinical Notes',
                    'url' => 'admin_notes.php',
                    'active' => $this->isActive('admin_notes')
                ];
            }
            
            // Doctor-specific menu items
            if ($this->role == 'doctor') {
                $menuItems[] = [
                    'label' => 'Analyze',
                    'url' => 'analyze.php',
                    'active' => $this->isActive('analyze')
                ];
            }
            
            $menuItems[] = [
                'label' => 'Logout',
                'url' => 'logout.php',
                'active' => false,
                'class' => 'logout-link'
            ];
        } else {
            // Public menu items
            $menuItems[] = [
                'label' => 'Home',
                'url' => 'index.php',
                'active' => $this->isActive('index')
            ];
            
            $menuItems[] = [
                'label' => 'Login',
                'url' => 'login.php',
                'active' => $this->isActive('login')
            ];
            
            $menuItems[] = [
                'label' => 'Sign Up',
                'url' => 'signup.php',
                'active' => $this->isActive('signup')
            ];
        }
        
        return $menuItems;
    }
    
    /**
     * Check if a menu item is active
     */
    private function isActive($page) {
        if (empty($this->currentPage)) {
            return false;
        }
        
        $currentPageLower = strtolower($this->currentPage);
        $pageLower = strtolower($page);
        
        return strpos($currentPageLower, $pageLower) !== false || 
               strpos($pageLower, $currentPageLower) !== false;
    }
    
    /**
     * Render menu HTML
     */
    public function render() {
        $menuItems = $this->getMenuItems();
        $html = '<ul class="nav-links">';
        
        foreach ($menuItems as $item) {
            $activeClass = $item['active'] ? 'active' : '';
            $additionalClass = isset($item['class']) ? $item['class'] : '';
            $class = trim("nav-link $activeClass $additionalClass");
            
            $html .= '<li>';
            $html .= '<a href="' . htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8') . '" class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '">';
            $html .= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8');
            $html .= '</a>';
            $html .= '</li>';
        }
        
        $html .= '</ul>';
        return $html;
    }
    
    /**
     * Get current page name from script name
     */
    public static function getCurrentPage() {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $page = basename($scriptName, '.php');
        return $page;
    }
}
?>

