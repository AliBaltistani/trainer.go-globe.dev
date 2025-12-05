<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\TrainerSubscription;
use App\Mail\ClientInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TrainerClientManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_trainer_can_add_client()
    {
        Mail::fake();

        $trainer = User::factory()->create(['role' => 'trainer']);
        $this->actingAs($trainer);

        $clientData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '1234567890',
            'fitness_level' => 'Beginner',
            'fitness_goals' => ['Lose weight', 'Build muscle'],
            'health_considerations' => 'None'
        ];

        $response = $this->postJson('/api/trainer/clients', $clientData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'status'
                ]
            ]);

        // Assert Database Records
        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
            'role' => 'client'
        ]);

        $client = User::where('email', 'john.doe@example.com')->first();

        $this->assertDatabaseHas('user_health_profiles', [
            'user_id' => $client->id,
            'fitness_level' => 'Beginner'
        ]);

        $this->assertDatabaseHas('goals', [
            'user_id' => $client->id,
            'name' => 'Lose weight'
        ]);

        $this->assertDatabaseHas('trainer_subscriptions', [
            'trainer_id' => $trainer->id,
            'client_id' => $client->id,
            'status' => 'active'
        ]);

        // Assert Email Sent
        Mail::assertSent(ClientInvitation::class, function ($mail) use ($client, $trainer) {
            return $mail->hasTo($client->email) &&
                   $mail->client->id === $client->id &&
                   $mail->trainer->id === $trainer->id;
        });
    }

    public function test_trainer_can_access_own_client_profile()
    {
        $trainer = User::factory()->create(['role' => 'trainer']);
        $client = User::factory()->create(['role' => 'client']);
        
        TrainerSubscription::create([
            'trainer_id' => $trainer->id,
            'client_id' => $client->id,
            'status' => 'active',
            'subscribed_at' => now()
        ]);

        $this->actingAs($trainer);

        $response = $this->getJson("/api/trainer/clients/{$client->id}/header");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name'
                ]
            ]);
    }

    public function test_trainer_cannot_access_other_trainers_client()
    {
        $trainer1 = User::factory()->create(['role' => 'trainer']);
        $trainer2 = User::factory()->create(['role' => 'trainer']);
        $client = User::factory()->create(['role' => 'client']);
        
        // Client subscribed to Trainer 1
        TrainerSubscription::create([
            'trainer_id' => $trainer1->id,
            'client_id' => $client->id,
            'status' => 'active',
            'subscribed_at' => now()
        ]);

        // Trainer 2 tries to access
        $this->actingAs($trainer2);

        $response = $this->getJson("/api/trainer/clients/{$client->id}/header");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Unauthorized access to client data'
            ]);
    }

    public function test_trainer_cannot_access_unsubscribed_client()
    {
        $trainer = User::factory()->create(['role' => 'trainer']);
        $client = User::factory()->create(['role' => 'client']);
        
        // Subscription is inactive
        TrainerSubscription::create([
            'trainer_id' => $trainer->id,
            'client_id' => $client->id,
            'status' => 'inactive',
            'subscribed_at' => now()->subMonth(),
            'unsubscribed_at' => now()
        ]);

        $this->actingAs($trainer);

        $response = $this->getJson("/api/trainer/clients/{$client->id}/header");

        $response->assertStatus(403);
    }
}