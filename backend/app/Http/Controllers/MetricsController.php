<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class MetricsController extends Controller
{
    private static string $metricsFile = '';

    private static function getMetricsFile(): string
    {
        if (empty(self::$metricsFile)) {
            self::$metricsFile = storage_path('framework/metrics.json');
        }
        return self::$metricsFile;
    }

    public static function incrementRequest(string $method, string $path, int $status): void
    {
        $file = self::getMetricsFile();
        $data = [];
        if (file_exists($file)) {
            $content = file_get_contents($file);
            $data = json_decode($content, true) ?: [];
        }

        // Normalize path
        $normalizedPath = '/' . ltrim($path, '/');
        $normalizedPath = preg_replace('#/api/books/\d+#', '/api/books/{id}', $normalizedPath);
        $key = sprintf('%s|%s|%d', strtoupper($method), $normalizedPath, $status);

        $data['requests'][$key] = ($data['requests'][$key] ?? 0) + 1;
        $data['total'] = ($data['total'] ?? 0) + 1;
        $data['last_updated'] = time();

        @file_put_contents($file, json_encode($data));
    }

    public function metrics(): Response
    {
        $file = self::getMetricsFile();
        $data = [];
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true) ?: [];
        }

        $lines = [];
        $lines[] = '# HELP http_requests_total Total number of HTTP requests processed by the bookstore application';
        $lines[] = '# TYPE http_requests_total counter';

        $requests = $data['requests'] ?? [];
        if (empty($requests)) {
            $lines[] = 'http_requests_total{method="GET",handler="/health",status="200"} 0';
        } else {
            foreach ($requests as $key => $count) {
                [$method, $path, $status] = explode('|', $key);
                $lines[] = sprintf('http_requests_total{method="%s",handler="%s",status="%d"} %d', $method, $path, (int)$status, $count);
            }
        }

        // Database connection status metric
        $lines[] = '# HELP app_database_up Whether the PostgreSQL database connection is operational (1 = healthy, 0 = down)';
        $lines[] = '# TYPE app_database_up gauge';
        $dbUp = 0;
        try {
            DB::connection()->getPdo();
            $dbUp = 1;
        } catch (\Throwable) {
            $dbUp = 0;
        }
        $lines[] = "app_database_up {$dbUp}";

        // Books total count
        $lines[] = '# HELP app_books_total Total number of books registered in bookstore catalog';
        $lines[] = '# TYPE app_books_total gauge';
        $booksCount = 0;
        if ($dbUp) {
            try {
                $booksCount = Book::count();
            } catch (\Throwable) {
                $booksCount = 0;
            }
        }
        $lines[] = "app_books_total {$booksCount}";

        // Users total count
        $lines[] = '# HELP app_users_total Total number of registered users';
        $lines[] = '# TYPE app_users_total gauge';
        $usersCount = 0;
        if ($dbUp) {
            try {
                $usersCount = User::count();
            } catch (\Throwable) {
                $usersCount = 0;
            }
        }
        $lines[] = "app_users_total {$usersCount}";

        // PHP memory usage metric
        $lines[] = '# HELP php_memory_usage_bytes Current PHP memory usage in bytes';
        $lines[] = '# TYPE php_memory_usage_bytes gauge';
        $lines[] = 'php_memory_usage_bytes ' . memory_get_usage();

        $output = implode("\n", $lines) . "\n";

        return response($output, 200, [
            'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8',
        ]);
    }
}
