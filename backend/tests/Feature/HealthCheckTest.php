<?php

use Illuminate\Support\Facades\DB;

test('endpoint /health mengembalikan status 200 saat database dan cache sehat', function () {
    $response = $this->getJson('/health');

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'healthy',
            'checks' => [
                'database' => [
                    'status' => 'healthy',
                ],
                'cache' => [
                    'status' => 'healthy',
                ],
            ],
        ])
        ->assertJsonStructure([
            'status',
            'timestamp',
            'checks' => [
                'database' => ['status', 'message', 'response_time_ms'],
                'cache' => ['status', 'message', 'response_time_ms'],
            ],
        ]);
});

test('endpoint /api/health juga dapat diakses secara publik dan sehat', function () {
    $response = $this->getJson('/api/health');

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'healthy',
        ]);
});

test('endpoint /health mengembalikan status 503 saat basis data mengalami kegagalan', function () {
    DB::shouldReceive('connection')
        ->andThrow(new \PDOException('SQLSTATE[08006] [7] could not connect to server: Connection refused'));

    $response = $this->getJson('/health');

    $response->assertStatus(503)
        ->assertJson([
            'status' => 'unhealthy',
            'checks' => [
                'database' => [
                    'status' => 'unhealthy',
                ],
            ],
            'issues' => ['database'],
        ]);
});
