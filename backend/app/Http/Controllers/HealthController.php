<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HealthController extends Controller
{
    /**
     * Pemeriksaan status kesehatan sistem riil (Database & Cache).
     *
     * @return JsonResponse HTTP 200 (healthy) atau HTTP 503 (unhealthy)
     */
    public function check(): JsonResponse
    {
        $checks = [];
        $issues = [];

        // 1. Pemeriksaan Konektivitas dan Kueri Basis Data Riil
        $dbStart = microtime(true);
        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $dbDuration = round((microtime(true) - $dbStart) * 1000, 2);

            $checks['database'] = [
                'status' => 'healthy',
                'message' => 'Koneksi dan kueri basis data PostgreSQL berhasil.',
                'response_time_ms' => $dbDuration,
            ];
        } catch (\Throwable $e) {
            $issues[] = 'database';
            $checks['database'] = [
                'status' => 'unhealthy',
                'message' => 'Gagal menghubungi basis data: ' . $e->getMessage(),
            ];

            Log::error('Pemeriksaan kesehatan sistem (/health) mendeteksi kegagalan basis data', [
                'who' => 'system_healthcheck',
                'action' => 'health_check',
                'service' => 'database',
                'reason' => 'Koneksi ke host basis data terputus atau query gagal dijalankan',
                'error' => $e->getMessage(),
            ]);
        }

        // 2. Pemeriksaan Operasi Tulis & Baca Cache Riil
        $cacheStart = microtime(true);
        try {
            $probeKey = 'health_probe_' . microtime(true);
            Cache::put($probeKey, 'ok', 10);
            $probeVal = Cache::get($probeKey);
            Cache::forget($probeKey);

            if ($probeVal !== 'ok') {
                throw new \RuntimeException('Nilai cache yang dibaca kembali tidak valid.');
            }

            $cacheDuration = round((microtime(true) - $cacheStart) * 1000, 2);
            $checks['cache'] = [
                'status' => 'healthy',
                'message' => 'Operasi baca dan tulis cache berhasil.',
                'response_time_ms' => $cacheDuration,
            ];
        } catch (\Throwable $e) {
            $issues[] = 'cache';
            $checks['cache'] = [
                'status' => 'unhealthy',
                'message' => 'Operasi cache bermasalah: ' . $e->getMessage(),
            ];

            Log::error('Pemeriksaan kesehatan sistem (/health) mendeteksi kegagalan cache', [
                'who' => 'system_healthcheck',
                'action' => 'health_check',
                'service' => 'cache',
                'reason' => 'Store cache tidak dapat diakses untuk operasi read/write',
                'error' => $e->getMessage(),
            ]);
        }

        $isHealthy = empty($issues);

        if ($isHealthy) {
            return response()->json([
                'status' => 'healthy',
                'timestamp' => now()->toIso8601String(),
                'checks' => $checks,
            ], 200);
        }

        return response()->json([
            'status' => 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
            'issues' => $issues,
        ], 503);
    }
}
