<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        // In Vavuniya branch, public ecommerce is disabled, root redirects to dashboard/login
        $response->assertRedirect(route('dashboard'));
    }
}
