<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FeatureTestCase;

class RootRouteTest extends FeatureTestCase
{
    public function test_without_login()
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_for_new_user()
    {
        $user = User::factory()->create();
        // first time competition and game are created, second time they are retrieved
        foreach (range(0, 1) as $attempt) {
            $response = $this
                ->actingAs($user)
                ->get('/');

            $response->assertOk();
            $response->assertJsonStructure($this->getFullJsonResponseStructure());
        }
    }
}
