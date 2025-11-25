<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\BookingSetting;
use App\Models\Availability;
use App\Models\BlockedTime;
use App\Models\User;
use App\Services\TrainerBookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\AvailabilityService;
use App\Services\GoogleCalendarService;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(TrainerBookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Display trainer's booking dashboard
     */
    public function dashboard()
    {
        $trainerId = Auth::id();
        $stats = $this->bookingService->getTrainerStatistics($trainerId);
        
        $recentBookings = Schedule::where('trainer_id', $trainerId)
            ->with(['client', 'trainer'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        $upcomingBookings = Schedule::where('trainer_id', $trainerId)
            ->where('date', '>=', now()->toDateString())
            ->where('status', '!=', 'cancelled')
            ->with(['client', 'trainer'])
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->limit(5)
            ->get();

        return view('trainer.bookings.dashboard', compact('stats', 'recentBookings', 'upcomingBookings'));
    }

    /**
     * Display trainer's bookings list
     */
    public function index(Request $request)
    {
        $trainerId = Auth::id();
        
        $filters = [
            'status' => $request->input('status'),
            'client_id' => $request->input('client_id'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $bookings = $this->bookingService->getTrainerBookings($trainerId, $filters);
        
        $statuses = [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'cancelled' => 'Cancelled',
        ];
        
        $clients = User::where('role', 'client')
            ->whereHas('clientSchedules', function($query) use ($trainerId) {
                $query->where('trainer_id', $trainerId);
            })
            ->get();

        return view('trainer.bookings.index', compact('bookings', 'statuses', 'clients'));
    }

    /**
     * Display specific booking details
     */
    public function show($id)
    {
        $trainerId = Auth::id();
        
        $booking = Schedule::where('id', $id)
            ->where('trainer_id', $trainerId)
            ->with(['client', 'trainer'])
            ->firstOrFail();

        return view('trainer.bookings.show', compact('booking'));
    }

    /**
     * Update booking status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
        ]);

        $trainerId = Auth::id();
        
        $result = $this->bookingService->updateBookingStatus($id, $request->status, $trainerId);

        if ($result) {
            return redirect()->back()->with('success', 'Booking status updated successfully.');
        }

        return redirect()->back()->with('error', 'Failed to update booking status.');
    }

    /**
     * Display calendar/schedule view
     */
    public function schedule()
    {
        $trainerId = Auth::id();
        
        $clients = User::where('role', 'client')
            ->whereHas('clientSchedules', function($query) use ($trainerId) {
                $query->where('trainer_id', $trainerId);
            })
            ->get();

        return view('trainer.bookings.schedule', compact('clients'));
    }

    /**
     * Get calendar events (API endpoint)
     */
    public function getEvents(Request $request)
    {
        $trainerId = Auth::id();
        
        $start = $request->input('start');
        $end = $request->input('end');

        $bookings = Schedule::where('trainer_id', $trainerId)
            ->whereBetween('date', [
                Carbon::parse($start)->toDateString(),
                Carbon::parse($end)->toDateString()
            ])
            ->with(['client', 'trainer'])
            ->get();

        $events = $bookings->map(function ($booking) {
            $color = match($booking->status) {
                'pending' => '#ffc107',
                'confirmed' => '#28a745',
                'cancelled' => '#dc3545',
                default => '#6c757d',
            };

            return [
                'id' => $booking->id,
                'title' => $booking->client->name,
                'start' => $booking->date->format('Y-m-d') . 'T' . $booking->start_time->format('H:i:s'),
                'end' => $booking->date->format('Y-m-d') . 'T' . $booking->end_time->format('H:i:s'),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'trainer_id' => $booking->trainer_id,
                    'client_id' => $booking->client_id,
                    'trainer_name' => $booking->trainer->name,
                    'client_name' => $booking->client->name,
                    'status' => $booking->status,
                    'notes' => $booking->notes,
                ],
            ];
        });

        return response()->json($events);
    }

    /**
     * Display scheduling settings menu
     */
    public function settings()
    {
        $trainer = Auth::user();
        $trainers = User::where('role', 'trainer')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        if ($trainer && $trainer->role === 'trainer') {
            $trainers = $trainers->where('id', $trainer->id)->values();
        }

        return view('trainer.bookings.settings', compact('trainer', 'trainers'));
    }

    /**
     * Display weekly availability settings
     */
    public function availability()
    {
        $trainer = Auth::user();
        $trainerId = Auth::id();
        
        $availabilities = Availability::where('trainer_id', $trainerId)->get();

        return view('trainer.bookings.availability', compact('trainer', 'availabilities'));
    }

    /**
     * Update weekly availability settings
     */
    public function updateAvailability(Request $request)
    {
        $trainerId = Auth::id();
        
        $result = $this->bookingService->updateTrainerAvailability($trainerId, $request->all());

        if ($result) {
            return redirect()->back()->with('success', 'Availability updated successfully.');
        }

        return redirect()->back()->with('error', 'Failed to update availability.');
    }

    /**
     * Display blocked times
     */
    public function blockedTimes(Request $request)
    {
        $trainer = Auth::user();
        $trainerId = Auth::id();
        
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $currentMonth = Carbon::create($year, $month, 1);
        
        $blockedTimes = BlockedTime::where('trainer_id', $trainerId)
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        return view('trainer.bookings.blocked-times', compact('trainer', 'blockedTimes', 'currentMonth'));
    }

    /**
     * Store new blocked time
     */
    public function storeBlockedTime(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'required|string|max:255',
        ]);

        $trainerId = Auth::id();
        
        $result = $this->bookingService->addBlockedTime($trainerId, $request->all());

        if ($result) {
            return redirect()->back()->with('success', 'Blocked time added successfully.');
        }

        return redirect()->back()->with('error', 'Failed to add blocked time.');
    }

    /**
     * Delete blocked time
     */
    public function destroyBlockedTime($id)
    {
        $trainerId = Auth::id();
        
        $result = $this->bookingService->removeBlockedTime($id, $trainerId);

        if ($result) {
            return redirect()->back()->with('success', 'Blocked time removed successfully.');
        }

        return redirect()->back()->with('error', 'Failed to remove blocked time.');
    }

    /**
     * Display session capacity settings
     */
    public function sessionCapacity()
    {
        $trainer = Auth::user();
        $trainerId = Auth::id();
        
        $sessionCapacity = $this->bookingService->getSessionCapacity($trainerId);

        return view('trainer.bookings.session-capacity', compact('trainer', 'sessionCapacity'));
    }

    /**
     * Update session capacity settings
     */
    public function updateSessionCapacity(Request $request)
    {
        $request->validate([
            'max_daily_sessions' => 'required|integer|min:1|max:20',
            'max_weekly_sessions' => 'required|integer|min:1|max:100',
            'session_duration_minutes' => 'required|integer|min:15|max:180',
            'break_between_sessions_minutes' => 'required|integer|min:0|max:60',
        ]);

        $trainerId = Auth::id();
        
        $result = $this->bookingService->updateSessionCapacity($trainerId, $request->all());

        if ($result) {
            return redirect()->back()->with('success', 'Session capacity updated successfully.');
        }

        return redirect()->back()->with('error', 'Failed to update session capacity.');
    }

    /**
     * Display booking approval settings
     */
    public function bookingApproval()
    {
        $trainer = Auth::user();
        $trainerId = Auth::id();
        
        $bookingSettings = $this->bookingService->getBookingSettings($trainerId);

        return view('trainer.bookings.booking-approval', compact('trainer', 'bookingSettings'));
    }

    /**
     * Update booking approval settings
     */
    public function updateBookingApproval(Request $request)
    {
        $request->validate([
            'allow_self_booking' => 'required|boolean',
            'require_approval' => 'required|boolean',
        ]);

        $trainerId = Auth::id();
        
        $result = $this->bookingService->updateBookingSettings($trainerId, $request->all());

        if ($result) {
            return redirect()->back()->with('success', 'Booking approval settings updated successfully.');
        }

        return redirect()->back()->with('error', 'Failed to update booking approval settings.');
    }

    /**
     * Export trainer's bookings
     */
    public function export(Request $request)
    {
        $trainerId = Auth::id();
        
        $filters = [
            'status' => $request->input('status'),
            'client_id' => $request->input('client_id'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        return $this->bookingService->exportBookings($trainerId, $filters);
    }

    public function create()
    {
        $trainer = Auth::user();
        $trainers = User::where('role', 'trainer')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
        if ($trainer && $trainer->role === 'trainer') {
            $trainers = $trainers->where('id', $trainer->id)->values();
        }
        $clients = User::where('role', 'client')
            ->whereHas('clientSchedules', function($query) {
                $query->where('trainer_id', Auth::id());
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
        return view('trainer.bookings.create', compact('trainer', 'trainers', 'clients'));
    }

    public function store(Request $request)
    {
        $trainerId = Auth::id();
        $data = $request->validate([
            'trainer_id' => 'required|integer',
            'client_id' => 'required|exists:users,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'required|in:pending,confirmed,cancelled',
            'notes' => 'nullable|string',
        ]);
        if ((int)$data['trainer_id'] !== (int)$trainerId) {
            return redirect()->back()->withErrors(['trainer_id' => 'Invalid trainer selection.'])->withInput();
        }
        $booking = Schedule::create([
            'trainer_id' => $trainerId,
            'client_id' => $data['client_id'],
            'date' => $data['date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ]);
        return redirect()->route('trainer.bookings.show', $booking->id)->with('success', 'Booking created successfully.');
    }

    public function edit($id)
    {
        $trainerId = Auth::id();
        $booking = Schedule::where('id', $id)
            ->where('trainer_id', $trainerId)
            ->with(['client', 'trainer'])
            ->firstOrFail();
        $trainer = Auth::user();
        $trainers = User::where('role', 'trainer')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
        if ($trainer && $trainer->role === 'trainer') {
            $trainers = $trainers->where('id', $trainer->id)->values();
        }
        $clients = User::where('role', 'client')
            ->whereHas('clientSchedules', function($query) use ($trainerId) {
                $query->where('trainer_id', $trainerId);
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
        $statuses = Schedule::getStatuses();
        return view('trainer.bookings.edit', compact('booking', 'trainer', 'trainers', 'clients', 'statuses'));
    }

    public function update(Request $request, $id)
    {
        $trainerId = Auth::id();
        $data = $request->validate([
            'trainer_id' => 'required|integer',
            'client_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'required|in:pending,confirmed,cancelled',
            'notes' => 'nullable|string',
        ]);
        if ((int)$data['trainer_id'] !== (int)$trainerId) {
            return redirect()->back()->withErrors(['trainer_id' => 'Invalid trainer selection.'])->withInput();
        }
        $booking = Schedule::where('id', $id)
            ->where('trainer_id', $trainerId)
            ->firstOrFail();
        $booking->update([
            'trainer_id' => $trainerId,
            'client_id' => $data['client_id'],
            'date' => $data['date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ]);
        return redirect()->route('trainer.bookings.show', $booking->id)->with('success', 'Booking updated successfully.');
    }

    public function destroy($id)
    {
        $trainerId = Auth::id();
        $booking = Schedule::where('id', $id)
            ->where('trainer_id', $trainerId)
            ->firstOrFail();
        if ($booking->google_event_id) {
            $booking->deleteGoogleCalendarEvent();
        }
        $booking->delete();
        return redirect()->route('trainer.bookings.index')->with('success', 'Booking deleted successfully.');
    }

    /**
     * Sync booking with Google Calendar
     */
    public function syncWithGoogleCalendar($id)
    {
        $trainerId = Auth::id();
        
        $booking = Schedule::where('id', $id)
            ->where('trainer_id', $trainerId)
            ->firstOrFail();

        try {
            $googleService = new GoogleCalendarService();
            if (!$googleService->isTrainerConnected($booking->trainer)) {
                return response()->json([
                    'success' => false,
                    'message' => "Trainer's Google Calendar is not connected. Please connect Google Calendar first."
                ], 400);
            }

            $result = $googleService->createEvent($booking);

            if ($result['success'] ?? false) {
                $booking->update([
                    'google_event_id' => $result['event_id'] ?? $booking->google_event_id,
                    'meet_link' => $result['meet_link'] ?? $booking->meet_link,
                    'status' => 'confirmed'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Booking successfully synced with Google Calendar!',
                    'data' => [
                        'event_id' => $result['event_id'] ?? null,
                        'meet_link' => $result['meet_link'] ?? null
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to sync with Google Calendar. Please try again.'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Google Calendar sync failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while syncing with Google Calendar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function googleCalendarBooking()
    {
        $currentUser = Auth::user();
        $trainers = User::where('role', 'trainer')->select('id', 'name', 'email')->get();
        if ($currentUser && $currentUser->role === 'trainer') {
            $trainers = $trainers->where('id', $currentUser->id)->values();
        }
        $clients = User::where('role', 'client')->select('id', 'name', 'email')->get();
        $timezones = timezone_identifiers_list();
        return view('trainer.bookings.google-calendar-booking', compact('trainers', 'clients', 'currentUser', 'timezones'));
    }

    public function editGoogleCalendarBooking(int $id)
    {
        $trainerId = Auth::id();
        $booking = Schedule::where('id', $id)->where('trainer_id', $trainerId)->with(['trainer:id,name,email', 'client:id,name,email'])->firstOrFail();
        $currentUser = Auth::user();
        $trainers = User::where('role', 'trainer')->select('id', 'name', 'email')->get();
        if ($currentUser && $currentUser->role === 'trainer') {
            $trainers = $trainers->where('id', $currentUser->id)->values();
        }
        $clients = User::where('role', 'client')->select('id', 'name', 'email')->get();
        $timezones = timezone_identifiers_list();
        return view('trainer.bookings.google-calendar-booking', compact('trainers', 'clients', 'booking', 'currentUser', 'timezones'));
    }

    public function checkTrainerGoogleConnection($trainerId)
    {
        try {
            $trainer = User::findOrFail($trainerId);
            if ($trainer->role !== 'trainer') {
                return response()->json(['success' => false, 'message' => 'User is not a trainer'], 400);
            }
            $googleService = new GoogleCalendarService();
            $connected = $googleService->isTrainerConnected($trainer);
            return response()->json(['success' => true, 'connected' => $connected]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error checking Google Calendar connection: ' . $e->getMessage()], 500);
        }
    }

    public function getTrainerAvailableSlots(Request $request)
    {
        $request->validate([
            'trainer_id' => 'required|exists:users,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'slot_duration' => 'nullable|integer|min:15|max:240'
        ]);

        try {
            $trainer = User::with(['availabilities', 'sessionCapacity', 'bookingSettings', 'blockedTimes' => function ($q) use ($request) {
                $q->whereBetween('date', [$request->start_date, $request->end_date])->orWhere('is_recurring', true);
            }])->findOrFail($request->trainer_id);
            if ($trainer->role !== 'trainer') {
                return response()->json(['success' => false, 'message' => 'User is not a trainer'], 400);
            }
            if ($trainer->availabilities->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Trainer has not configured their availability schedule yet.'], 400);
            }
            $slotDuration = $request->slot_duration ?? 60;
            if ($trainer->sessionCapacity && $trainer->sessionCapacity->session_duration_minutes) {
                $slotDuration = $trainer->sessionCapacity->session_duration_minutes;
            }
            $availabilityService = new AvailabilityService();
            $googleService = new GoogleCalendarService();
            $source = 'local';
            $availableSlots = [];
            if ($googleService->isTrainerConnected($trainer)) {
                try {
                    $availableSlots = $googleService->getAvailableSlots($trainer, $request->start_date, $request->end_date);
                    $source = 'google_calendar';
                } catch (\Exception $e) {
                    Log::warning('Google Calendar failed, falling back to local availability', ['trainer_id' => $trainer->id, 'error' => $e->getMessage()]);
                    $availableSlots = $availabilityService->getAvailableSlots($trainer, $request->start_date, $request->end_date, $slotDuration);
                    $source = 'local_fallback';
                }
            } else {
                $availableSlots = $availabilityService->getAvailableSlots($trainer, $request->start_date, $request->end_date, $slotDuration);
                $source = 'local';
            }
            return response()->json(['success' => true, 'data' => ['available_slots' => $availableSlots, 'source' => $source]]);
        } catch (\Exception $e) {
            Log::error('Error fetching trainer available slots', ['trainer_id' => $request->trainer_id, 'start_date' => $request->start_date, 'end_date' => $request->end_date, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error fetching available slots. Please try again later.'], 500);
        }
    }

    public function storeGoogleCalendarBooking(Request $request)
    {
        $request->validate([
            'trainer_id' => 'required|exists:users,id',
            'client_id' => 'required|exists:users,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'session_type' => 'required|string|in:personal_training,consultation,assessment,follow_up',
            'notes' => 'nullable|string|max:500',
            'meeting_agenda' => 'nullable|string|max:255',
            'timezone' => 'required|string|in:' . implode(',', timezone_identifiers_list()),
            'status' => 'nullable|in:pending,confirmed,cancelled'
        ]);

        try {
            $currentTrainerId = Auth::id();
            if ((int)$request->trainer_id !== (int)$currentTrainerId) {
                return redirect()->back()->withErrors(['trainer_id' => 'Selected trainer is invalid.']);
            }
            $trainer = User::findOrFail($request->trainer_id);
            $client = User::findOrFail($request->client_id);
            if ($trainer->role !== 'trainer') {
                return redirect()->back()->withErrors(['trainer_id' => 'Selected user is not a trainer.']);
            }
            if ($client->role !== 'client') {
                return redirect()->back()->withErrors(['client_id' => 'Selected user is not a client.']);
            }
            $availabilityService = new AvailabilityService();
            $availabilityCheck = $availabilityService->checkAvailability($trainer, $request->booking_date, $request->start_time, $request->end_time);
            $bookingSettings = BookingSetting::where('trainer_id', $trainer->id)->first();
            $initialStatus = $request->status ?? 'confirmed';
            if ($bookingSettings && $bookingSettings->require_approval) {
                $initialStatus = 'pending';
            }
            $schedule = Schedule::create([
                'trainer_id' => $trainer->id,
                'client_id' => $client->id,
                'date' => $request->booking_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'status' => $initialStatus,
                'notes' => $request->notes,
                'session_type' => $request->session_type,
                'meeting_agenda' => $request->meeting_agenda,
                'timezone' => $request->timezone
            ]);
            $googleMessage = '';
            $googleService = new GoogleCalendarService();
            if ($initialStatus === 'confirmed' && $googleService->isTrainerConnected($trainer)) {
                try {
                    $googleService->createEvent($schedule);
                    $googleMessage = ' with Google Calendar event and Meet link';
                } catch (\Exception $e) {
                    Log::error('Failed to create Google Calendar event for booking ' . $schedule->id . ': ' . $e->getMessage());
                    $googleMessage = ' (Google Calendar event could not be created: ' . $e->getMessage() . ')';
                }
            }
            $message = $initialStatus === 'confirmed' ? 'Booking created successfully' . $googleMessage : 'Booking created and is pending trainer approval';
            return redirect()->route('trainer.bookings.show', $schedule->id)->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error creating Google Calendar booking: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An error occurred while creating the booking: ' . $e->getMessage()]);
        }
    }

    public function updateGoogleCalendarBooking(Request $request, int $id)
    {
        $request->validate([
            'trainer_id' => 'required|exists:users,id',
            'client_id' => 'required|exists:users,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'session_type' => 'required|string|in:personal_training,consultation,assessment,follow_up',
            'notes' => 'nullable|string|max:500',
            'meeting_agenda' => 'nullable|string|max:255',
            'timezone' => 'required|string|in:' . implode(',', timezone_identifiers_list()),
            'status' => 'nullable|in:pending,confirmed,cancelled'
        ]);

        try {
            $currentTrainerId = Auth::id();
            if ((int)$request->trainer_id !== (int)$currentTrainerId) {
                return redirect()->back()->withErrors(['trainer_id' => 'Selected trainer is invalid.']);
            }
            $booking = Schedule::where('id', $id)->where('trainer_id', $currentTrainerId)->firstOrFail();
            $trainer = User::findOrFail($request->trainer_id);
            $client = User::findOrFail($request->client_id);
            if ($trainer->role !== 'trainer') {
                return redirect()->back()->withErrors(['trainer_id' => 'Selected user is not a trainer.']);
            }
            if ($client->role !== 'client') {
                return redirect()->back()->withErrors(['client_id' => 'Selected user is not a client.']);
            }
            $availabilityService = new AvailabilityService();
            $availabilityService->checkAvailability($trainer, $request->booking_date, $request->start_time, $request->end_time, $id);
            $bookingSettings = BookingSetting::where('trainer_id', $trainer->id)->first();
            $status = $request->status ?? $booking->status;
            if ($bookingSettings && $bookingSettings->require_approval) {
                $status = 'pending';
            }
            $booking->update([
                'trainer_id' => $trainer->id,
                'client_id' => $client->id,
                'date' => $request->booking_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'notes' => $request->notes,
                'session_type' => $request->session_type,
                'meeting_agenda' => $request->meeting_agenda,
                'timezone' => $request->timezone,
                'status' => $status
            ]);
            $googleMessage = '';
            $googleService = new GoogleCalendarService();
            if ($googleService->isTrainerConnected($trainer)) {
                try {
                    if ($booking->hasGoogleCalendarEvent()) {
                        $googleService->updateCalendarEvent($booking);
                        $googleMessage = ' and Google Calendar event updated';
                    } else {
                        $googleService->createEvent($booking);
                        $googleMessage = ' with new Google Calendar event and Meet link';
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to update Google Calendar event for booking ' . $booking->id . ': ' . $e->getMessage());
                    $googleMessage = ' (Google Calendar event could not be updated: ' . $e->getMessage() . ')';
                }
            }
            $message = 'Booking updated successfully' . $googleMessage;
            return redirect()->route('trainer.bookings.show', $booking->id)->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error updating Google Calendar booking: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An error occurred while updating the booking: ' . $e->getMessage()]);
        }
    }

    public function destroyGoogleCalendarBooking(int $id)
    {
        try {
            $trainerId = Auth::id();
            $booking = Schedule::where('id', $id)->where('trainer_id', $trainerId)->firstOrFail();
            $googleMessage = '';
            if ($booking->hasGoogleCalendarEvent()) {
                try {
                    $googleService = new GoogleCalendarService();
                    $googleService->deleteCalendarEvent($booking);
                    $googleMessage = ' and Google Calendar event deleted';
                } catch (\Exception $e) {
                    Log::error('Failed to delete Google Calendar event for booking ' . $booking->id . ': ' . $e->getMessage());
                    $googleMessage = ' (Google Calendar event could not be deleted: ' . $e->getMessage() . ')';
                }
            }
            $booking->delete();
            return redirect()->route('trainer.bookings.index')->with('success', 'Booking deleted successfully' . $googleMessage);
        } catch (\Exception $e) {
            Log::error('Error deleting Google Calendar booking: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An error occurred while deleting the booking: ' . $e->getMessage()]);
        }
    }
}
