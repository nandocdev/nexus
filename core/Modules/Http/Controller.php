<?php
namespace Nexus\Modules\Http;
// app/Core/Controller.php
class Controller {
    protected function view($view, $data = []) {
        $viewPath = __DIR__ . "/../../../app/Views/{$view}.php";
        if (file_exists($viewPath)) {
            extract($data);
            ob_start();
            include $viewPath;
            $content = ob_get_clean();
            
            // Simple template inheritance
            if (isset($layout)) {
                $layoutPath = __DIR__ . "/../../../app/Views/{$layout}.php";
                if (file_exists($layoutPath)) {
                    ob_start();
                    include $layoutPath;
                    $layoutContent = ob_get_clean();
                    $content = str_replace('{{content}}', $content, $layoutContent);
                }
            }
            
            echo $content;
        } else {
            throw new \Exception("View not found: $view");
        }
    }
    
    protected function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function redirect($url) {
        header("Location: $url");
        exit;
    }
}