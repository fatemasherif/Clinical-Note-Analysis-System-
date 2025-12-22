<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/NLPBridge.php';
require_once __DIR__ . '/../PHP_Templates/Analyze.php';

class AnalyzeController extends BaseController {
    private $nlpBridge;

    public function __construct() {
        parent::__construct();
        $this->nlpBridge = new NLPBridge();
    }

    public function analyze() {
        $this->requireAuth();
        $result = null;
        $serviceStatus = $this->nlpBridge->isServiceAvailable();

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
            $file = $_FILES['file'];
            if ($file['error'] == 0) {
                $content = file_get_contents($file['tmp_name']);
                
                // Use Python NLP service (with PHP fallback)
                $result = $this->nlpBridge->analyze($content);
                
                if ($result) {
                    $result['service_used'] = $serviceStatus ? 'Python NLP Service' : 'PHP Fallback';
                }
            }
        }

        $analyze = new Analyze($result, $serviceStatus);
        $analyze->render();
    }
}
?>








