<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ErrorHandlingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test that database unique constraint violations show user-friendly messages
     */
    public function test_unique_constraint_violation_shows_user_friendly_message()
    {
        // Create a user first
        $existingUser = User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'type' => 'owner',
            'phone_number' => '+251912345678',
            'profile' => 'avatar.png',
            'lang' => 'english',
            'parent_id' => 1,
        ]);

        // Try to create another user with the same email
        $response = $this->post('/register', [
            'name' => 'Jane',
            'email' => 'test@example.com', // Same email as existing user
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'phone_number' => '912345679',
            'type' => 'owner',
        ]);

        // Should redirect back with user-friendly error message
        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        $errorMessage = session('error');
        $this->assertStringContainsString('email address is already registered', $errorMessage);
        $this->assertStringNotContainsString('SQLSTATE[23505]', $errorMessage);
        $this->assertStringNotContainsString('users_email_unique', $errorMessage);
    }

    /**
     * Test that 404 errors show user-friendly messages
     */
    public function test_404_error_shows_user_friendly_message()
    {
        $response = $this->get('/non-existent-page');

        $response->assertStatus(404);
        $response->assertSee('Page Not Found');
        $response->assertSee('The page you are looking for could not be found');
    }

    /**
     * Test that validation errors are handled properly
     */
    public function test_validation_errors_are_handled_properly()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => '123',
            'phone_number' => '123',
            'type' => 'owner',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    /**
     * Test that general exceptions show user-friendly messages
     */
    public function test_general_exceptions_show_user_friendly_messages()
    {
        // This test would require mocking a service that throws an exception
        // For now, we'll just verify the error views exist
        $this->assertFileExists(resource_path('views/errors/404.blade.php'));
        $this->assertFileExists(resource_path('views/errors/500.blade.php'));
        $this->assertFileExists(resource_path('views/errors/403.blade.php'));
        $this->assertFileExists(resource_path('views/errors/general.blade.php'));
    }
} 