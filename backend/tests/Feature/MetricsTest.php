<?php

test('endpoint /metrics mengembalikan status 200 dengan format prometheus', function () {
    $response = $this->get('/metrics');

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/plain; version=0.0.4; charset=utf-8');
    
    $content = $response->getContent();
    expect($content)->toContain('# HELP http_requests_total');
    expect($content)->toContain('# HELP app_database_up');
    expect($content)->toContain('app_database_up 1');
});

test('endpoint /api/metrics juga dapat diakses secara publik', function () {
    $response = $this->get('/api/metrics');

    $response->assertStatus(200);
    expect($response->getContent())->toContain('http_requests_total');
});
