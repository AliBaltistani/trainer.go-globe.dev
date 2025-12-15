<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLocation;
use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

/**
 * Admin Trainees Controller
 * 
 * Handles complete CRUD operations for trainee (client role) management in admin panel
 * Provides specialized functionality for managing users with 'client' role
 * Supports both web and AJAX responses with DataTables integration
 * 
 * @package     Laravel CMS App
 * @subpackage  Controllers\Admin
 * @category    Trainee Management
 * @author      Go Globe CMS Team
 * @since       1.0.0
 * @version     1.0.0
 */
class TraineesController extends Controller
{
    /**
     * Display a listing of trainees (users with client role)
     * Supports AJAX DataTables requests
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Check if this is an AJAX DataTables request
            if ($request->ajax()) {
                return $this->getDataTablesData($request);
            }
            
            // Get filter parameters
            $status = $request->get('status', 'all');
            
            // Build query for trainees only (client role)
            $query = User::with(['receivedTestimonials', 'goals'])
                        ->where('role', 'client');
            
            // Apply status filter
            if ($status !== 'all') {
                if ($status === 'active') {
                    $query->whereNotNull('email_verified_at');
                } else {
                    $query->whereNull('email_verified_at');
                }
            }
            
            // Get trainees with pagination
            $trainees = $query->latest()->paginate(20);
            
            // Get statistics for dashboard cards
            $stats = [
                'total_trainees' => User::where('role', 'client')->count(),
                'active_trainees' => User::where('role', 'client')->whereNotNull('email_verified_at')->count(),
                'inactive_trainees' => User::where('role', 'client')->whereNull('email_verified_at')->count(),
                'trainees_with_goals' => User::where('role', 'client')->whereHas('goals')->count(),
            ];
            
            return view('admin.trainees.index', compact('trainees', 'stats', 'status'));
            
        } catch (\Exception $e) {
            Log::error('Failed to load trainees list: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Failed to load trainees: ' . $e->getMessage());
        }
    }
    
    /**
     * Get DataTables formatted data for AJAX requests
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    private function getDataTablesData(Request $request): JsonResponse
    {
        try {
            $draw = $request->get('draw');
            $start = $request->get('start', 0);
            $length = $request->get('length', 10);
            $search = $request->get('search')['value'] ?? '';
            $status = $request->get('status', 'all');
            
            // Build query for trainees only
            $query = User::with(['receivedTestimonials', 'goals'])
                        ->where('role', 'client');
            
            // Apply status filter
            if ($status !== 'all') {
                if ($status === 'active') {
                    $query->whereNotNull('email_verified_at');
                } else {
                    $query->whereNull('email_verified_at');
                }
            }
            
            // Apply search filter
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }
            
            // Get total count before pagination
            $totalRecords = User::where('role', 'client')->count();
            $filteredRecords = $query->count();
            
            // Apply pagination - ensure we get fresh data from database
            $trainees = $query->orderBy('created_at', 'desc')
                             ->skip($start)
                             ->take($length)
                             ->get();
            
            // Format data for DataTables
            $data = $trainees->map(function($trainee) {
                return [
                    'id' => $trainee->id,
                    'name' => $trainee->name,
                    'email' => $trainee->email,
                    'phone' => $trainee->phone ?? 'N/A',
                    'status' => $trainee->email_verified_at ? 'Active' : 'Inactive',
                    'profile_image' => $trainee->profile_image ? asset('storage/' . $trainee->profile_image) : null,
                    'goals_count' => $trainee->goals->count(),
                    'testimonials_count' => $trainee->receivedTestimonials->count(),
                    'created_at' => $trainee->created_at->format('d-m-Y H:i'),
                    'updated_at' => $trainee->updated_at->format('d-m-Y H:i'),
                ];
            });
            
            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            Log::error('DataTables request failed: ' . $e->getMessage());
            
            return response()->json([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Failed to load data'
            ], 500);
        }
    }

    /**
     * Show the form for creating a new trainee
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        try {
            return view('admin.trainees.create');
            
        } catch (\Exception $e) {
            Log::error('Failed to load trainee creation form: ' . $e->getMessage());
            return redirect()->route('admin.trainees.index')->with('error', 'Failed to load creation form');
        }
    }

    /**
     * Store a newly created trainee in storage
     * Supports both web and AJAX requests
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Define validation rules for trainee
            $rules = [
                'name' => ['required', 'string', 'min:2', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
                'password_confirmation' => ['required', 'string', 'min:8'],
                'phone' => [
                    'nullable',
                    'string',
                    'max:20',
                    'regex:/^[\+]?[0-9\s\-\(\)]+$/',
                    Rule::unique('users', 'phone')->where(function ($query) {
                        return $query->whereNotNull('phone');
                    }),
                ],
                'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
                'timezone' => ['nullable', 'string', 'max:50'],
                'status' => ['nullable', 'boolean', 'in:0,1'],
                // Location fields
                'country' => ['nullable', 'string', 'max:100'],
                'state' => ['nullable', 'string', 'max:100'],
                'city' => ['nullable', 'string', 'max:100'],
                'address' => ['nullable', 'string', 'max:255'],
                'zipcode' => ['nullable', 'string', 'max:20', 'regex:/^[A-Z0-9\s\-]+$/i'],
            ];
            
            // Custom validation messages
            $messages = [
                'name.required' => 'The trainee name field is required.',
                'name.min' => 'The trainee name must be at least 2 characters.',
                'name.max' => 'The trainee name may not be greater than 255 characters.',
                'name.string' => 'The trainee name must be a valid text.',
                'email.required' => 'The email address field is required.',
                'email.email' => 'Please provide a valid email address.',
                'email.max' => 'The email address may not be greater than 255 characters.',
                'email.unique' => 'This email address is already registered.',
                'password.required' => 'The password field is required.',
                'password.min' => 'The password must be at least 8 characters.',
                'password.max' => 'The password may not be greater than 255 characters.',
                'password.confirmed' => 'The password confirmation does not match.',
                'password_confirmation.required' => 'The password confirmation field is required.',
                'password_confirmation.min' => 'The password confirmation must be at least 8 characters.',
                'phone.string' => 'The phone number must be a valid text.',
                'phone.max' => 'The phone number may not be greater than 20 characters.',
                'phone.regex' => 'Please provide a valid phone number format.',
                'phone.unique' => 'This phone number is already registered.',
                'profile_image.image' => 'The profile image must be an image file.',
                'profile_image.mimes' => 'The profile image must be a JPEG, PNG, JPG, GIF, or WEBP file.',
                'profile_image.max' => 'The profile image size must not exceed 5MB.',
                'timezone.string' => 'The timezone must be a valid text.',
                'timezone.max' => 'The timezone may not be greater than 50 characters.',
                'status.boolean' => 'The status must be either active or inactive.',
                'status.in' => 'The status must be either active (1) or inactive (0).',
                'country.string' => 'The country must be a valid text.',
                'country.max' => 'The country may not be greater than 100 characters.',
                'state.string' => 'The state must be a valid text.',
                'state.max' => 'The state may not be greater than 100 characters.',
                'city.string' => 'The city must be a valid text.',
                'city.max' => 'The city may not be greater than 100 characters.',
                'address.string' => 'The address must be a valid text.',
                'address.max' => 'The address may not be greater than 255 characters.',
                'zipcode.string' => 'The zip code must be a valid text.',
                'zipcode.max' => 'The zip code may not be greater than 20 characters.',
                'zipcode.regex' => 'Please provide a valid zip/postal code format.',
            ];
            
            // Validate input
            $validator = Validator::make($request->all(), $rules, $messages);
            
            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed. Please check the form for errors.',
                        'errors' => $validator->errors()
                    ], 422);
                }
                return back()->withErrors($validator)->withInput();
            }
            
            // Double-check phone uniqueness before insertion (safety measure)
            $phoneValue = $request->filled('phone') ? trim($request->phone) : null;
            if (!empty($phoneValue)) {
                if (User::where('phone', $phoneValue)->exists()) {
                    if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                        return response()->json([
                            'success' => false,
                            'message' => 'Validation failed. Please check the form for errors.',
                            'errors' => ['phone' => ['This phone number is already registered.']]
                        ], 422);
                    }
                    return back()->withErrors(['phone' => 'This phone number is already registered.'])->withInput();
                }
            }
            
            // Prepare trainee data
            $traineeData = [
                'name' => trim($request->name),
                'email' => strtolower(trim($request->email)),
                'password' => Hash::make($request->password),
                'role' => 'client', // Force client role for trainees
                'timezone' => $request->filled('timezone') ? $request->timezone : 'UTC',
            ];
            
            // Handle phone - generate unique placeholder if not provided
            if (!empty($phoneValue)) {
                $traineeData['phone'] = $phoneValue;
            } else {
                // Generate a unique placeholder phone number
                do {
                    $placeholderPhone = '+000' . rand(1000000000, 9999999999);
                } while (User::where('phone', $placeholderPhone)->exists());
                
                $traineeData['phone'] = $placeholderPhone;
            }
            
            // Handle account status (email_verified_at)
            if ($request->has('status')) {
                $traineeData['email_verified_at'] = $request->boolean('status') ? now() : null;
            } else {
                // Default to active for admin-created trainees
                $traineeData['email_verified_at'] = now();
            }
            
            $trainee = User::create($traineeData);
            
            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $imagePath = $request->file('profile_image')->store('profile-images', 'public');
                $trainee->update(['profile_image' => $imagePath]);
            }
            
            // Handle location information
            if ($request->filled('country') || $request->filled('state') || $request->filled('city') || 
                $request->filled('address') || $request->filled('zipcode')) {
                UserLocation::create([
                    'user_id' => $trainee->id,
                    'country' => $request->country,
                    'state' => $request->state,
                    'city' => $request->city,
                    'address' => $request->address,
                    'zipcode' => $request->zipcode,
                ]);
            }
            
            // Handle fitness goals assignment
            if ($request->filled('fitness_goals')) {
                $goals = $request->fitness_goals;
                
                // Handle array input
                if (is_array($goals)) {
                    foreach ($goals as $goalName) {
                        if (!empty(trim($goalName))) {
                            \App\Models\Goal::create([
                                'name' => trim($goalName),
                                'user_id' => $trainee->id,
                                'status' => 1
                            ]);
                        }
                    }
                } elseif (is_string($goals)) {
                    // Handle JSON string input
                    $decodedGoals = json_decode($goals, true);
                    if (is_array($decodedGoals)) {
                        foreach ($decodedGoals as $goalName) {
                            if (!empty(trim($goalName))) {
                                \App\Models\Goal::create([
                                    'name' => trim($goalName),
                                    'user_id' => $trainee->id,
                                    'status' => 1
                                ]);
                            }
                        }
                    } else {
                        // Single goal string
                        if (!empty(trim($goals))) {
                            \App\Models\Goal::create([
                                'name' => trim($goals),
                                'user_id' => $trainee->id,
                                'status' => 1
                            ]);
                        }
                    }
                }
            }
            
            // Log trainee creation
            Log::info('New trainee created by admin', [
                'admin_id' => Auth::id(),
                'trainee_id' => $trainee->id,
                'trainee_email' => $trainee->email,
                'goals_count' => $trainee->goals()->count()
            ]);
            
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => 'Trainee created successfully',
                    'redirect' => route('admin.trainees.index'),
                    'trainee' => $trainee->load('location')
                ]);
            }
            
            return redirect()->route('admin.trainees.index')
                           ->with('success', 'Trainee created successfully');
                           
        } catch (\Exception $e) {
            Log::error('Failed to create trainee: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'request_data' => $request->except(['password', 'password_confirmation']),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create trainee: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Failed to create trainee: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified trainee
     * redirects to unified user profile
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show(Request $request, $id)
    {
        return redirect()->route('admin.users.show', $id);
    }

    /**
     * Show the form for editing the specified trainee
     * 
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        try {
            $trainee = User::where('role', 'client')
                ->with('goals', 'location')
                ->findOrFail($id);
            
            return view('admin.trainees.edit', compact('trainee'));
            
        } catch (\Exception $e) {
            Log::error('Failed to load trainee edit form: ' . $e->getMessage());
            return redirect()->route('admin.trainees.index')->with('error', 'Trainee not found');
        }
    }

    /**
     * Update the specified trainee in storage
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            // Find trainee with client role only
            $trainee = User::where('role', 'client')->findOrFail($id);
            
            // Define validation rules
            $rules = [
                'name' => 'required|string|max:255',
                'email' => ['required', 'email', Rule::unique('users')->ignore($trainee->id)],
                'password' => 'nullable|string|min:8|confirmed',
                'phone' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($trainee->id)],
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ];
            
            // Validate input
            $validator = Validator::make($request->all(), $rules);
            
            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $validator->errors()
                    ], 422);
                }
                return back()->withErrors($validator)->withInput();
            }
            
            // Prepare update data
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
            ];
            
            // Update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }
            
            // Update trainee
            $trainee->update($updateData);
            
            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                // Delete old image if exists
                if ($trainee->profile_image) {
                    Storage::disk('public')->delete($trainee->profile_image);
                }
                
                $imagePath = $request->file('profile_image')->store('profile-images', 'public');
                $trainee->update(['profile_image' => $imagePath]);
            }
            
            // Handle goals removal
            if ($request->filled('goals_to_remove')) {
                $goalsToRemove = $request->goals_to_remove;
                
                // Handle JSON string input
                if (is_string($goalsToRemove)) {
                    $goalsToRemove = json_decode($goalsToRemove, true);
                }
                
                if (is_array($goalsToRemove)) {
                    foreach ($goalsToRemove as $goalId) {
                        $goal = Goal::where('user_id', $trainee->id)->find($goalId);
                        if ($goal) {
                            $goal->delete();
                        }
                    }
                }
            }
            
            // Handle fitness goals assignment (add new goals)
            if ($request->filled('fitness_goals')) {
                $goals = $request->fitness_goals;
                
                // Handle array input
                if (is_array($goals)) {
                    foreach ($goals as $goalName) {
                        if (!empty(trim($goalName))) {
                            // Check if goal already exists for this trainee
                            $existingGoal = Goal::where('user_id', $trainee->id)
                                ->where('name', trim($goalName))
                                ->first();
                            
                            if (!$existingGoal) {
                                Goal::create([
                                    'name' => trim($goalName),
                                    'user_id' => $trainee->id,
                                    'status' => 1
                                ]);
                            }
                        }
                    }
                } elseif (is_string($goals)) {
                    // Handle JSON string input
                    $decodedGoals = json_decode($goals, true);
                    if (is_array($decodedGoals)) {
                        foreach ($decodedGoals as $goalName) {
                            if (!empty(trim($goalName))) {
                                $existingGoal = Goal::where('user_id', $trainee->id)
                                    ->where('name', trim($goalName))
                                    ->first();
                                
                                if (!$existingGoal) {
                                    Goal::create([
                                        'name' => trim($goalName),
                                        'user_id' => $trainee->id,
                                        'status' => 1
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
            
            // Log trainee update
            Log::info('Trainee updated by admin', [
                'admin_id' => Auth::id(),
                'trainee_id' => $trainee->id,
                'trainee_email' => $trainee->email,
                'goals_count' => $trainee->goals()->count()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Trainee updated successfully',
                    'trainee' => $trainee->fresh()
                ]);
            }
            
            return redirect()->route('admin.trainees.index')
                           ->with('success', 'Trainee updated successfully');
                           
        } catch (\Exception $e) {
            Log::error('Failed to update trainee: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'trainee_id' => $id,
                'request_data' => $request->except(['password', 'password_confirmation']),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update trainee: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Failed to update trainee: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified trainee from storage
     * 
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            // Find trainee with client role only
            $trainee = User::where('role', 'client')->findOrFail($id);
            
            // Delete profile image if exists
            if ($trainee->profile_image) {
                Storage::disk('public')->delete($trainee->profile_image);
            }
            
            // Log trainee deletion
            Log::info('Trainee deleted by admin', [
                'admin_id' => Auth::id(),
                'trainee_id' => $trainee->id,
                'trainee_email' => $trainee->email
            ]);
            
            // Delete trainee
            $trainee->delete();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Trainee deleted successfully'
                ]);
            }
            
            return redirect()->route('admin.trainees.index')
                           ->with('success', 'Trainee deleted successfully');
                           
        } catch (\Exception $e) {
            Log::error('Failed to delete trainee: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'trainee_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete trainee: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Failed to delete trainee: ' . $e->getMessage());
        }
    }

    /**
     * Toggle trainee status (active/inactive)
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            // Find trainee with client role only
            $trainee = User::where('role', 'client')->findOrFail($id);
            
            // Toggle email verification status
            $trainee->email_verified_at = $trainee->email_verified_at ? null : now();
            $trainee->save();
            
            // Refresh the model to ensure we have the latest data
            $trainee->refresh();
            
            // Log status change
            Log::info('Trainee status toggled by admin', [
                'admin_id' => Auth::id(),
                'trainee_id' => $trainee->id,
                'new_status' => $trainee->email_verified_at ? 'active' : 'inactive',
                'email_verified_at' => $trainee->email_verified_at
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Trainee status updated successfully',
                'status' => $trainee->email_verified_at ? 'Active' : 'Inactive',
                'email_verified_at' => $trainee->email_verified_at ? $trainee->email_verified_at->toDateTimeString() : null
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to toggle trainee status: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'trainee_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete trainee profile image
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteImage(Request $request, $id)
    {
        try {
            // Find trainee with client role only
            $trainee = User::where('role', 'client')->findOrFail($id);
            
            if ($trainee->profile_image) {
                // Delete image file
                Storage::disk('public')->delete($trainee->profile_image);
                
                // Update database
                $trainee->update(['profile_image' => null]);
                
                // Log image deletion
                Log::info('Trainee profile image deleted by admin', [
                    'admin_id' => Auth::id(),
                    'trainee_id' => $trainee->id
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Profile image deleted successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No profile image found'
            ], 404);
            
        } catch (\Exception $e) {
            Log::error('Failed to delete trainee profile image: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'trainee_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image: ' . $e->getMessage()
            ], 500);
        }
    }

    public function subscriptions(Request $request, $id)
    {
        try {
            $trainee = User::where('role', 'client')->findOrFail($id);

            $subscriptions = \App\Models\TrainerSubscription::where('client_id', $trainee->id)
                ->with(['trainer:id,name,email,phone,profile_image,designation'])
                ->orderBy('subscribed_at', 'desc')
                ->paginate(20);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $subscriptions
                ]);
            }

            return view('admin.trainees.subscriptions', compact('trainee', 'subscriptions'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to load trainee subscriptions: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load subscriptions'
                ], 500);
            }
            return redirect()->route('admin.trainees.index')->with('error', 'Failed to load subscriptions');
        }
    }
}