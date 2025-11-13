<?php
namespace Nexus\Modules\Http;

/**
 * Rate Limiting Middleware
 * Limits the number of requests per time window
 */
class RateLimitMiddleware {
    /**
     * Handle rate limiting
     */
    public function handle($next, $maxRequests = 60, $decayMinutes = 1) {
        $key = $this->getRateLimitKey();
        $requests = $this->getRequestCount($key);

        if ($requests >= $maxRequests) {
            return $this->sendRateLimitExceededResponse();
        }

        $this->incrementRequestCount($key, $decayMinutes);

        return $next();
    }

    /**
     * Get the rate limit key for the current request
     */
    protected function getRateLimitKey() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        return md5($ip . '|' . $method . '|' . $uri);
    }

    /**
     * Get the current request count for the key
     */
    protected function getRequestCount($key) {
        if (!isset($_SESSION['rate_limit'])) {
            $_SESSION['rate_limit'] = [];
        }

        $data = $_SESSION['rate_limit'][$key] ?? null;

        if (!$data || time() > $data['expires']) {
            return 0;
        }

        return $data['count'];
    }

    /**
     * Increment the request count for the key
     */
    protected function incrementRequestCount($key, $decayMinutes) {
        if (!isset($_SESSION['rate_limit'])) {
            $_SESSION['rate_limit'] = [];
        }

        $expires = time() + ($decayMinutes * 60);

        if (isset($_SESSION['rate_limit'][$key])) {
            $_SESSION['rate_limit'][$key]['count']++;
            $_SESSION['rate_limit'][$key]['expires'] = $expires;
        } else {
            $_SESSION['rate_limit'][$key] = [
                'count' => 1,
                'expires' => $expires
            ];
        }
    }

    /**
     * Send rate limit exceeded response
     */
    protected function sendRateLimitExceededResponse() {
        http_response_code(429);
        header('Content-Type: application/json');
        header('Retry-After: 60');

        echo json_encode([
            'success' => false,
            'error' => 'Too Many Requests',
            'message' => 'Rate limit exceeded. Please try again later.',
            'retry_after' => 60
        ]);

        exit;
    }
}