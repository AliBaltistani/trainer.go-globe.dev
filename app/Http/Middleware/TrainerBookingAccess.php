<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\BlockedTime;
use Symfony\Component\HttpFoundation\Response;

class TrainerBookingAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        // Ensure user is authenticated and is a trainer
        if (!$user || $user->role !== 'trainer') {
            abort(403, 'Unauthorized access. Trainer role required.');
        }

        // Check booking access if booking ID is in route
        $bookingId = $request->route('id');
        
        if ($bookingId && $request->is('trainer/bookings/*/show') || $request->is('trainer/bookings/*/sync-google-calendar')) {
            $booking = Schedule::find($bookingId);
            
            if (!$booking || $booking->trainer_id !== $user->id) {
                abort(403, 'Unauthorized access to this booking.');
            }
        }

        // Check blocked time access if blocked time ID is in route
        if ($request->is('trainer/bookings/blocked-times/*') && $request->isMethod('delete')) {
            $blockedTimeId = $request->route('id');
            $blockedTime = BlockedTime::find($blockedTimeId);
            
            if (!$blockedTime || $blockedTime->trainer_id !== $user->id) {
                abort(403, 'Unauthorized access to this blocked time.');
            }
        }

        return $next($request);
    }
}
