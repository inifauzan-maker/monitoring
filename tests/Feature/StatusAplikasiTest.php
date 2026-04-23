<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatusAplikasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_endpoint_health_mengembalikan_status_ok(): void
    {
        $response = $this->get(route('health'));

        $response
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
            ])
            ->assertJsonStructure([
                'status',
                'aplikasi',
                'waktu_server',
                'dependensi' => ['database', 'storage'],
            ]);
    }
}
