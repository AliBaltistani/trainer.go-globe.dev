<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\BookingSetting;
use App\Models\Availability;
use App\Models\BlockedTime;
use App\Models\SessionCapacity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TrainerBookingService
{
    /**
     * Get trainer's bookings with filters
     */
    public function getTrainerBookings($trainerId, $filters = [])
    {
        $query = Schedule::where('trainer_id', $trainerId)
            ->with(['client', 'trainer']);

        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('date', '<=', $filters['date_to']);
        }

        return $query->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(15);
    }

    /**
     * Get trainer's booking statistics
     */
    public function getTrainerStatistics($trainerId)
    {
        $today = now()->toDateString();
        $weekStart = now()->startOfWeek()->toDateString();
        $weekEnd = now()->endOfWeek()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        return [
            'today_bookings' => Schedule::where('trainer_id', $trainerId)
                ->where('date', $today)
                ->count(),
            
            'pending_bookings' => Schedule::where('trainer_id', $trainerId)
                ->where('status', 'pending')
                ->count(),
            
            'confirmed_bookings' => Schedule::where('trainer_id', $trainerId)
                ->where('status', 'confirmed')
                ->where('date', '>=', $today)
                ->count(),
            
            'week_bookings' => Schedule::where('trainer_id', $trainerId)
                ->whereBetween('date', [$weekStart, $weekEnd])
                ->count(),
            
            'month_bookings' => Schedule::where('trainer_id', $trainerId)
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->count(),
            
            'total_clients' => Schedule::where('trainer_id', $trainerId)
                ->distinct('client_id')
                ->count('client_id'),
        ];
    }

    /**
     * Update booking status
     */
    public function updateBookingStatus($bookingId, $status, $trainerId)
    {
        $booking = Schedule::where('id', $bookingId)
            ->where('trainer_id', $trainerId)
            ->first();

        if (!$booking) {
            return false;
        }

        $booking->status = $status;
        return $booking->save();
    }

    /**
     * Get trainer's availability
     */
    public function getTrainerAvailability($trainerId)
    {
        return Availability::where('trainer_id', $trainerId)->get();
    }

    /**
     * Update trainer's availability
     */
    public function updateTrainerAvailability($trainerId, $data)
    {
        try {
            DB::beginTransaction();

            if (isset($data['availability']) && is_array($data['availability'])) {
                foreach ($data['availability'] as $dayOfWeek => $slots) {
                    Availability::updateOrCreate(
                        [
                            'trainer_id' => $trainerId,
                            'day_of_week' => $dayOfWeek,
                        ],
                        [
                            'morning_available' => $slots['morning_available'] ?? 0,
                            'evening_available' => $slots['evening_available'] ?? 0,
                        ]
                    );
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Get trainer's blocked times
     */
    public function getBlockedTimes($trainerId)
    {
        return BlockedTime::where('trainer_id', $trainerId)
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
    }

    /**
     * Add blocked time
     */
    public function addBlockedTime($trainerId, $data)
    {
        try {
            BlockedTime::create([
                'trainer_id' => $trainerId,
                'date' => $data['date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'reason' => $data['reason'],
            ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Remove blocked time
     */
    public function removeBlockedTime($blockedTimeId, $trainerId)
    {
        $blockedTime = BlockedTime::where('id', $blockedTimeId)
            ->where('trainer_id', $trainerId)
            ->first();

        if (!$blockedTime) {
            return false;
        }

        return $blockedTime->delete();
    }

    /**
     * Get session capacity settings
     */
    public function getSessionCapacity($trainerId)
    {
        return SessionCapacity::firstOrCreate(
            ['trainer_id' => $trainerId],
            [
                'max_daily_sessions' => 8,
                'max_weekly_sessions' => 40,
                'session_duration_minutes' => 60,
                'break_between_sessions_minutes' => 15,
            ]
        );
    }

    /**
     * Update session capacity settings
     */
    public function updateSessionCapacity($trainerId, $data)
    {
        try {
            SessionCapacity::updateOrCreate(
                ['trainer_id' => $trainerId],
                [
                    'max_daily_sessions' => $data['max_daily_sessions'],
                    'max_weekly_sessions' => $data['max_weekly_sessions'],
                    'session_duration_minutes' => $data['session_duration_minutes'],
                    'break_between_sessions_minutes' => $data['break_between_sessions_minutes'],
                ]
            );

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get booking settings
     */
    public function getBookingSettings($trainerId)
    {
        return BookingSetting::firstOrCreate(
            ['trainer_id' => $trainerId],
            [
                'allow_self_booking' => true,
                'require_approval' => false,
                'allow_weekend_booking' => true,
            ]
        );
    }

    /**
     * Update booking settings
     */
    public function updateBookingSettings($trainerId, $data)
    {
        try {
            BookingSetting::updateOrCreate(
                ['trainer_id' => $trainerId],
                [
                    'allow_self_booking' => $data['allow_self_booking'] ?? false,
                    'require_approval' => $data['require_approval'] ?? false,
                    'allow_weekend_booking' => $data['allow_weekend_booking'] ?? true,
                ]
            );

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Export bookings to CSV
     */
    public function exportBookings($trainerId, $filters = [])
    {
        $query = Schedule::where('trainer_id', $trainerId)
            ->with(['client', 'trainer']);

        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('date', '<=', $filters['date_to']);
        }

        $bookings = $query->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();

        $filename = 'bookings_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($bookings) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'ID',
                'Trainer',
                'Client',
                'Date',
                'Start Time',
                'End Time',
                'Duration (minutes)',
                'Status',
                'Google Calendar',
                'Meet Link',
                'Notes',
                'Created At',
            ]);

            // Add data rows
            foreach ($bookings as $booking) {
                fputcsv($file, [
                    $booking->id,
                    $booking->trainer->name,
                    $booking->client->name,
                    $booking->date->format('Y-m-d'),
                    $booking->start_time->format('H:i'),
                    $booking->end_time->format('H:i'),
                    $booking->getDurationInMinutes(),
                    ucfirst($booking->status),
                    $booking->google_event_id ? 'Synced' : 'Not Synced',
                    $booking->meet_link ?? 'N/A',
                    $booking->notes ?? '',
                    $booking->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
