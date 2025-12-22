<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/SimpleNLP.php';
require_once __DIR__ . '/../PHP_Templates/Analyze.php';

class AnalyzeController extends BaseController {
    public function analyze() {
        $this->requireAuth();
        $nlp = new SimpleNLP();
        $result = null;

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
            $file = $_FILES['file'];
            if ($file['error'] == 0) {
                $content = file_get_contents($file['tmp_name']);
                $result = $nlp->analyze_text($content);
            }
        }

        $analyze = new Analyze($result);
        $analyze->render();
    }
}
?>




