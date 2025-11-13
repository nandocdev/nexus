<?php
namespace Nexus\Modules\Http;
use Nexus\Bootstrap\Application;
// app/Core/Controller.php
class Controller {
    protected function view($view, $data = []) {
        $viewFactory = Application::getInstance()->make('view');

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
        $response = Response::json($data, $status);
        $response->send();
        exit;
    }

    protected function redirect($url) {
        $response = Response::redirect($url);
        $response->send();
        exit;
    }
}