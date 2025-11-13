<?php
namespace Nexus\Modules\Http;
// app/Core/Controller.php
class Controller {
    protected function view($view, $data = []) {
        $viewFactory = \Nexus\Bootstrap\Application::getInstance()->make('view');

        // Handle layout if specified
        if (isset($data['layout'])) {
            $layout = $data['layout'];
            unset($data['layout']);

            // Render the main view
            $content = $viewFactory->make($view, $data);

            // Render the layout with content
            return $viewFactory->make($layout, array_merge($data, ['content' => $content]));
        }

        return $viewFactory->make($view, $data);
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