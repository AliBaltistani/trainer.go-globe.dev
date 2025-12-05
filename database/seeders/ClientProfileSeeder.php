<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\TrainerSubscription;
use App\Models\UserHealthProfile;
use App\Models\Goal;
use App\Models\ClientWeightLog;
use App\Models\ClientActivityLog;
use App\Models\Program;
use App\Models\Week;
use App\Models\Day;
use App\Models\Circuit;
use App\Models\Workout;
use App\Models\ProgramExercise;
use App\Models\ClientProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ClientProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure Client Exists
        $clientId = 22;
        $client = User::find($clientId);
        if (!$client) {
            $client = User::create([
                'id' => $clientId,
                'name' => 'Test Client 22',
                'email' => 'client22@example.com',
                'password' => Hash::make('password'),
                'role' => 'client',
                'created_at' => Carbon::now()->subMonths(4),
            ]);
            $this->command->info("Created User ID {$clientId}");
        } else {
            $this->command->info("User ID {$clientId} already exists");
        }

        // 2. Ensure a Trainer Exists
        $trainer = User::where('role', 'trainer')->first();
        if (!$trainer) {
            $trainer = User::create([
                'name' => 'Default Trainer',
                'email' => 'trainer@example.com',
                'password' => Hash::make('password'),
                'role' => 'trainer',
            ]);
            $this->command->info("Created Default Trainer");
        }

        // 3. Trainer Subscription
        TrainerSubscription::firstOrCreate(
            ['client_id' => $client->id, 'trainer_id' => $trainer->id],
            [
                'status' => 'active',
                'subscribed_at' => Carbon::now()->subMonths(3),
            ]
        );

        // 4. Health Profile
        UserHealthProfile::updateOrCreate(
            ['user_id' => $client->id],
            [
                'allergies' => ['Peanuts', 'Shellfish'],
                'chronic_conditions' => ['Asthma'],
                'fitness_level' => 'Intermediate',
            ]
        );

        // 5. Goals
        // Active Goal
        Goal::firstOrCreate(
            ['user_id' => $client->id, 'status' => 1],
            [
                'name' => 'Lose 10lbs',
                'target_value' => 170,
                'current_value' => 175,
                'metric_unit' => 'lbs',
                'deadline' => Carbon::now()->addMonth(),
            ]
        );
        // Completed Goal
        Goal::firstOrCreate(
            ['user_id' => $client->id, 'status' => 0],
            [
                'name' => 'Run 5k',
                'target_value' => 5,
                'current_value' => 5,
                'metric_unit' => 'km',
                'achieved_at' => Carbon::now()->subMonth(),
            ]
        );

        // 6. Weight Logs (Simulate progress over 3 months)
        $startWeight = 185;
        $currentWeight = 175;
        $totalLogs = 12; // Weekly logs roughly
        $weightDiff = ($startWeight - $currentWeight) / $totalLogs;

        for ($i = 0; $i < $totalLogs; $i++) {
            $date = Carbon::now()->subWeeks($totalLogs - $i);
            $weight = $startWeight - ($weightDiff * $i) + (rand(-5, 5) / 10); // Add some noise

            ClientWeightLog::firstOrCreate(
                ['user_id' => $client->id, 'logged_at' => $date->format('Y-m-d')],
                [
                    'weight' => round($weight, 1),
                    'unit' => 'lbs',
                    'notes' => 'Weekly check-in',
                ]
            );
        }

        // 7. Activity Logs
        ClientActivityLog::create([
            'user_id' => $client->id,
            'activity_type' => 'running',
            'duration_seconds' => 1800, // 30 mins
            'distance_meters' => 5000,
            'performed_at' => Carbon::now()->subDays(2),
        ]);

        // 8. Trainer Notes
        DB::table('trainer_client_notes')->insertOrIgnore([
            [
                'trainer_id' => $trainer->id,
                'client_id' => $client->id,
                'note' => 'Client is making great progress on form.',
                'created_at' => Carbon::now()->subWeek(),
                'updated_at' => Carbon::now()->subWeek(),
            ],
            [
                'trainer_id' => $trainer->id,
                'client_id' => $client->id,
                'note' => 'Discussed nutrition plan adjustments.',
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ]
        ]);

        // 9. Program Data for Overview Stats
        $program = Program::firstOrCreate(
            ['client_id' => $client->id],
            [
                'trainer_id' => $trainer->id,
                'name' => 'Weight Loss Phase 1',
                'duration' => 4, // 4 weeks
                'description' => 'Introductory weight loss program',
                'is_active' => true,
            ]
        );

        $week = Week::firstOrCreate(
            ['program_id' => $program->id, 'week_number' => 1],
            ['title' => 'Intro Week']
        );

        $day = Day::firstOrCreate(
            ['week_id' => $week->id, 'day_number' => 1],
            ['title' => 'Full Body A', 'cool_down' => 'Stretch 5 mins']
        );

        $circuit = Circuit::firstOrCreate(
            ['day_id' => $day->id, 'circuit_number' => 1],
            ['title' => 'Warmup']
        );

        // Ensure a Workout (Exercise) exists
        $workout = Workout::firstOrCreate(
            ['name' => 'Pushups'],
            [
                'user_id' => $trainer->id,
                'duration' => 5,
                'description' => 'Standard pushups'
            ]
        );

        $progExercise = ProgramExercise::firstOrCreate(
            ['circuit_id' => $circuit->id, 'workout_id' => $workout->id],
            [
                'order' => 1,
                'tempo' => '2-0-2-0',
                'rest_interval' => '60s',
                'notes' => 'Keep core tight'
            ]
        );

        // 10. Client Progress (Simulate completed workouts)
        // We'll create a few completion records for this exercise to show up in stats
        // Note: The unique constraint is ['client_id', 'program_exercise_id', 'set_number']
        // So we can log multiple sets.
        // BUT, `getOverview` counts DISTINCT `completed_at` for sessions.
        // So we need to simulate different days.
        
        // Session 1: 3 days ago
        $date1 = Carbon::now()->subDays(3);
        ClientProgress::updateOrCreate(
            ['client_id' => $client->id, 'program_exercise_id' => $progExercise->id, 'set_number' => 1],
            [
                'status' => 'completed',
                'logged_reps' => 10,
                'logged_weight' => 0,
                'completed_at' => $date1,
            ]
        );
        
        // Session 2: 1 day ago (We need another set or another exercise for a new session usually, 
        // but the constraint is on set_number. To simulate a new session for the *same* exercise 
        // in the DB structure usually implies a new assignment or we just use set_number 2 for now 
        // effectively, OR usually `completed_at` differentiates.
        // Wait, the unique key is client_id, prog_ex_id, set_number. 
        // This implies a program exercise is done once. 
        // Realistically, programs are assigned and done. If repeated, maybe reset? 
        // Or maybe `client_progress` tracks history?
        // If the table has unique constraint, it means for a specific ProgramExercise (which belongs to a specific Program->Week->Day),
        // you can only do Set 1 once.
        // So to have "multiple sessions", the user must have done different exercises or the same exercise in a different Week/Day.
        
        // Let's create Day 2 for another session count.
        $day2 = Day::firstOrCreate(
            ['week_id' => $week->id, 'day_number' => 2],
            ['title' => 'Full Body B']
        );
        $circuit2 = Circuit::firstOrCreate(
            ['day_id' => $day2->id, 'circuit_number' => 1],
            ['title' => 'Main']
        );
        $progExercise2 = ProgramExercise::firstOrCreate(
            ['circuit_id' => $circuit2->id, 'workout_id' => $workout->id],
            ['order' => 1]
        );

        ClientProgress::updateOrCreate(
            ['client_id' => $client->id, 'program_exercise_id' => $progExercise2->id, 'set_number' => 1],
            [
                'status' => 'completed',
                'logged_reps' => 12,
                'logged_weight' => 0,
                'completed_at' => Carbon::now()->subDay(),
            ]
        );

        $this->command->info("Seeded data for Client ID {$clientId}");
    }
}
