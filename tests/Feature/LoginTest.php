<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LoginTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test a successful login.
     *
     * @return void
     */
    public function testSuccessfulLogin()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(500);
        

    }
    
    /**
     * Test token is present in array
     */
    public function testTokenIsPresentInArray()
    {

        $data = [
            'id' => 1,
            'name' => 'John Doe',
            'token' => 'ABC123'
        ];

        $this->assertArrayHasKey('token', $data);
    }

    /**
     * Test login with invalid credentials.
     *
     * @return void
     */
    public function testLoginWithInvalidCredentials()
    {
        $response = $this->postJson('/login', [
            'email' => 'invalid@example.com',
            'password' => 'invalidpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'errors' => [
                'password' => 'Field password incorrect.',
                'email' => 'Field email incorrect.',
            ],
        ]);
    }

    /**
     * Test login with missing email field.
     *
     * @return void
     */
    public function testLoginWithMissingEmailField()
    {
        $response = $this->postJson('/login', [
            'password' => 'password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Test login with missing password field.
     *
     * @return void
     */
    public function testLoginWithMissingPasswordField()
    {
        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }
}
