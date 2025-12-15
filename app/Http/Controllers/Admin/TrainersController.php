<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLocation;
use App\Models\UserCertification;
use App\Models\Testimonial;
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
 * Admin Trainers Controller
 * 
 * Handles trainer-specific CRUD operations in admin panel
 * Manages trainer profiles, certifications, and testimonials
 * Provides both web and AJAX responses
 * 
 * @package     Laravel CMS App
 * @subpackage  Controllers\Admin
 * @category    Trainer Management
 * @author      Go Globe CMS Team
 * @since       1.0.0
 * @version     1.0.0
 */
class TrainersController extends Controller
{
    /**
     * Display a listing of trainers with their statistics
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
            $experience = $request->get('experience', 'all');
            
            // Build query for trainers only
            $query = User::where('role', 'trainer')
                        ->with(['receivedTestimonials', 'certifications'])
                        ->withCount([
                            'subscriptionsAsTrainer as active_subscribers_count' => function($q){
                                $q->where('status', 'active');
                            }
                        ]);
            
            // Apply status filter
            if ($status === 'active') {
                $query->whereNotNull('email_verified_at');
            } elseif ($status === 'inactive') {
                $query->whereNull('email_verified_at');
            }
            
            // Apply experience filter
            if ($experience !== 'all') {
                switch ($experience) {
                    case 'beginner':
                        $query->where('experience', '<=', 2);
                        break;
                    case 'intermediate':
                        $query->whereBetween('experience', [3, 7]);
                        break;
                    case 'expert':
                        $query->where('experience', '>=', 8);
                        break;
                }
            }
            
            // Get trainers with pagination
            $trainers = $query->latest()->paginate(20);
            
            // Get statistics for dashboard cards
            $stats = [
                'total_trainers' => User::where('role', 'trainer')->count(),
                'active_trainers' => User::where('role', 'trainer')->whereNotNull('email_verified_at')->count(),
                'inactive_trainers' => User::where('role', 'trainer')->whereNull('email_verified_at')->count(),
                'total_certifications' => UserCertification::count(),
                'total_testimonials' => Testimonial::count(),
                'avg_experience' => User::where('role', 'trainer')->avg('experience') ?? 0,
                'avg_rating' => Testimonial::avg('rate') ?? 0,
            ];
            
            return view('admin.trainers.index', compact('trainers', 'stats', 'status', 'experience'));
            
        } catch (\Exception $e) {
            Log::error('Failed to load trainers list: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Failed to load trainers: ' . $e->getMessage());
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
            $experience = $request->get('experience', 'all');
            
            // Build query for trainers only
            $query = User::where('role', 'trainer')
                        ->with(['receivedTestimonials', 'certifications'])
                        ->withCount([
                            'subscriptionsAsTrainer as active_subscribers_count' => function($q){
                                $q->where('status', 'active');
                            }
                        ]);
            
            // Apply status filter
            if ($status === 'active') {
                $query->whereNotNull('email_verified_at');
            } elseif ($status === 'inactive') {
                $query->whereNull('email_verified_at');
            }
            
            // Apply experience filter
            if ($experience !== 'all') {
                switch ($experience) {
                    case 'beginner':
                        $query->where('experience', '<=', 2);
                        break;
                    case 'intermediate':
                        $query->whereBetween('experience', [3, 7]);
                        break;
                    case 'expert':
                        $query->where('experience', '>=', 8);
                        break;
                }
            }
            
            // Apply search filter
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('designation', 'like', "%{$search}%")
                      ->orWhere('about', 'like', "%{$search}%");
                });
            }
            
            // Get total count before pagination
            $totalRecords = User::where('role', 'trainer')->count();
            $filteredRecords = $query->count();
            
            // Apply pagination
            $trainers = $query->skip($start)->take($length)->latest()->get();
            
            // Format data for DataTables
            $data = $trainers->map(function($trainer) {
                $avgRating = $trainer->receivedTestimonials->avg('rate') ?? 0;
                
                return [
                    'id' => $trainer->id,
                    'name' => $trainer->name,
                    'email' => $trainer->email,
                    'phone' => $trainer->phone ?? 'N/A',
                    'designation' => $trainer->designation ?? 'N/A',
                    'experience' => $trainer->experience ? str_replace('_', ' ', ucwords($trainer->experience)) : 'N/A',
                    'status' => $trainer->email_verified_at ? 'Active' : 'Inactive',
                    'profile_image' => $trainer->profile_image ? asset('storage/' . $trainer->profile_image) : null,
                    'certifications_count' => $trainer->certifications->count(),
                    'testimonials_count' => $trainer->receivedTestimonials->count(),
                    'average_rating' => round($avgRating, 1),
                    'active_subscribers_count' => $trainer->active_subscribers_count,
                    'total_likes' => $trainer->receivedTestimonials->sum('likes'),
                    'created_at' => $trainer->created_at->format('d-m-Y H:i'),
                    'updated_at' => $trainer->updated_at->format('d-m-Y H:i'),
                ];
            });
            
            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            Log::error('Trainers DataTables request failed: ' . $e->getMessage());
            
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
     * Show the form for creating a new trainer
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        try {
            return view('admin.trainers.create');
            
        } catch (\Exception $e) {
            Log::error('Failed to load trainer creation form: ' . $e->getMessage());
            return redirect()->route('admin.trainers.index')->with('error', 'Failed to load creation form');
        }
    }

    /**
     * Store a newly created trainer in storage
     * Supports both web and AJAX requests
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Define validation rules for trainer
            $rules = [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'phone' => [
                    'nullable',
                    'string',
                    'max:20',
                    'regex:/^[\+]?[0-9\s\-\(\)]+$/',
                    Rule::unique('users', 'phone')->where(function ($query) {
                        return $query->whereNotNull('phone');
                    }),
                ],
                'designation' => ['required', 'string', 'max:255'],
                'experience' => ['required', 'in:less_than_1_year,1_year,2_years,3_years,4_years,5_years,6_years,7_years,8_years,9_years,10_years,more_than_10_years'],
                'about' => ['required', 'string', 'max:1000'],
                'training_philosophy' => ['nullable', 'string', 'max:1000'],
                'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
                'business_logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
                'timezone' => ['nullable', 'string', 'max:50'],
                'status' => ['nullable', 'boolean'],
                'specializations' => ['nullable', 'exists:specializations,id'],
                // Location fields
                'country' => ['nullable', 'string', 'max:100'],
                'state' => ['nullable', 'string', 'max:100'],
                'city' => ['nullable', 'string', 'max:100'],
                'address' => ['nullable', 'string', 'max:255'],
                'zipcode' => ['nullable', 'string', 'max:20', 'regex:/^[A-Z0-9\s\-]+$/i'],
            ];
            
            // Custom validation messages
            $messages = [
                'name.required' => 'The trainer name field is required.',
                'name.max' => 'The trainer name may not be greater than 255 characters.',
                'email.required' => 'The email address field is required.',
                'email.email' => 'Please provide a valid email address.',
                'email.unique' => 'This email address is already registered.',
                'password.required' => 'The password field is required.',
                'password.min' => 'The password must be at least 8 characters.',
                'password.confirmed' => 'The password confirmation does not match.',
                'phone.regex' => 'Please provide a valid phone number.',
                'phone.unique' => 'This phone number is already registered.',
                'designation.required' => 'The designation field is required.',
                'designation.max' => 'The designation may not be greater than 255 characters.',
                'experience.required' => 'Please select an experience level.',
                'experience.in' => 'Please select a valid experience level.',
                'about.required' => 'The about field is required.',
                'about.max' => 'The about field may not be greater than 1000 characters.',
                'training_philosophy.max' => 'The training philosophy may not be greater than 1000 characters.',
                'profile_image.image' => 'The profile image must be an image file.',
                'profile_image.mimes' => 'The profile image must be a JPEG, PNG, JPG, GIF, or WEBP file.',
                'profile_image.max' => 'The profile image size must not exceed 5MB.',
                'business_logo.image' => 'The business logo must be an image file.',
                'business_logo.mimes' => 'The business logo must be a JPEG, PNG, JPG, GIF, or WEBP file.',
                'business_logo.max' => 'The business logo size must not exceed 5MB.',
                'specializations.exists' => 'The selected specialization is invalid.',
                'zipcode.regex' => 'Please provide a valid zip/postal code.',
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
            
            // Prepare trainer data
            $trainerData = [
                'name' => trim($request->name),
                'email' => strtolower(trim($request->email)),
                'password' => Hash::make($request->password),
                'role' => 'trainer',
                'designation' => trim($request->designation),
                'experience' => $request->experience,
                'about' => trim($request->about),
                'training_philosophy' => $request->filled('training_philosophy') ? trim($request->training_philosophy) : null,
                'timezone' => $request->filled('timezone') ? $request->timezone : 'UTC',
            ];
            
            // Handle phone - generate unique placeholder if not provided
            if (!empty($phoneValue)) {
                $trainerData['phone'] = $phoneValue;
            } else {
                // Generate a unique placeholder phone number
                do {
                    $placeholderPhone = '+000' . rand(1000000000, 9999999999);
                } while (User::where('phone', $placeholderPhone)->exists());
                
                $trainerData['phone'] = $placeholderPhone;
            }
            
            // Handle account status (email_verified_at)
            if ($request->has('status')) {
                $trainerData['email_verified_at'] = $request->boolean('status') ? now() : null;
            } else {
                // Default to active for admin-created trainers
                $trainerData['email_verified_at'] = now();
            }
            
            $trainer = User::create($trainerData);
            
            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $imagePath = $request->file('profile_image')->store('profile-images', 'public');
                $trainer->update(['profile_image' => $imagePath]);
            }
            
            // Handle business logo upload
            if ($request->hasFile('business_logo')) {
                $logoPath = $request->file('business_logo')->store('business-logos', 'public');
                $trainer->update(['business_logo' => $logoPath]);
            }
            
            // Handle specialization assignment
            if ($request->filled('specializations')) {
                $trainer->specializations()->sync([$request->specializations]);
            }
            
            // Handle location information
            if ($request->filled('country') || $request->filled('state') || $request->filled('city') || 
                $request->filled('address') || $request->filled('zipcode')) {
                UserLocation::create([
                    'user_id' => $trainer->id,
                    'country' => $request->country,
                    'state' => $request->state,
                    'city' => $request->city,
                    'address' => $request->address,
                    'zipcode' => $request->zipcode,
                ]);
            }
            
            // Log trainer creation
            Log::info('New trainer created by admin', [
                'admin_id' => Auth::id(),
                'trainer_id' => $trainer->id,
                'trainer_email' => $trainer->email,
                'specialization_id' => $request->specializations
            ]);
            
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => 'Trainer created successfully',
                    'redirect' => route('admin.trainers.index'),
                    'trainer' => $trainer->load('location', 'specializations')
                ]);
            }
            
            return redirect()->route('admin.trainers.index')
                           ->with('success', 'Trainer created successfully');
                           
        } catch (\Exception $e) {
            Log::error('Failed to create trainer: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'request_data' => $request->except(['password', 'password_confirmation']),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create trainer: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Failed to create trainer: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified trainer with detailed information
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

    public function subscribers(Request $request, $id)
    {
        try {
            $trainer = User::where('role', 'trainer')->findOrFail($id);

            $subscriptions = \App\Models\TrainerSubscription::where('trainer_id', $trainer->id)
                ->with(['client:id,name,email,phone,profile_image,created_at'])
                ->orderBy('subscribed_at', 'desc')
                ->paginate(20);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $subscriptions
                ]);
            }

            return view('admin.trainers.subscribers', compact('trainer', 'subscriptions'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to load trainer subscribers: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load subscribers'
                ], 500);
            }
            return redirect()->route('admin.trainers.index')->with('error', 'Failed to load subscribers');
        }
    }

    /**
     * Show the form for editing the specified trainer
     * redirects to unified user edit
     * 
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        return redirect()->route('admin.users.edit', $id);
    }

    /**
     * Update the specified trainer in storage
     * Supports both web and AJAX requests
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $trainer = User::where('role', 'trainer')->findOrFail($id);
            
            // Define validation rules for trainer update
            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $trainer->id,
                'phone' => 'nullable|string|max:20|unique:users,phone,' . $trainer->id,
                'designation' => 'required|string|max:255',
                'experience' => 'required|in:less_than_1_year,1_year,2_years,3_years,4_years,5_years,6_years,7_years,8_years,9_years,10_years,more_than_10_years',
                'about' => 'required|string|max:1000',
                'training_philosophy' => 'nullable|string|max:1000',
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'specializations' => 'nullable|exists:specializations,id'
            ];
            
            // Add password validation if provided
            if ($request->filled('password')) {
                $rules['password'] = 'string|min:8|confirmed';
            }
            
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
            
            // Update trainer data
            $trainerData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'designation' => $request->designation,
                'experience' => $request->experience,
                'about' => $request->about,
                'training_philosophy' => $request->training_philosophy
            ];
            
            // Update password if provided
            if ($request->filled('password')) {
                $trainerData['password'] = Hash::make($request->password);
            }
            
            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                // Delete old image
                if ($trainer->profile_image && Storage::disk('public')->exists($trainer->profile_image)) {
                    Storage::disk('public')->delete($trainer->profile_image);
                }
                
                $imagePath = $request->file('profile_image')->store('profile-images', 'public');
                $trainerData['profile_image'] = $imagePath;
            }
            
            $trainer->update($trainerData);
            
            // Handle specialization assignment
            if ($request->filled('specializations')) {
                $trainer->specializations()->sync([$request->specializations]);
            } else {
                // If no specialization selected, remove all existing specializations
                $trainer->specializations()->detach();
            }
            
            // Log trainer update
            Log::info('Trainer updated by admin', [
                'admin_id' => Auth::id(),
                'trainer_id' => $trainer->id,
                'trainer_email' => $trainer->email,
                'changes' => $request->except(['password', 'password_confirmation']),
                'specialization_id' => $request->specializations
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Trainer updated successfully',
                    'trainer' => $trainer->fresh()
                ]);
            }
            
            return redirect()->route('admin.trainers.index')
                           ->with('success', 'Trainer updated successfully');
                           
        } catch (\Exception $e) {
            Log::error('Failed to update trainer: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'trainer_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update trainer: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Failed to update trainer: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified trainer from storage
     * Supports both web and AJAX requests
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            $trainer = User::where('role', 'trainer')->findOrFail($id);
            
            // Delete profile image if exists
            if ($trainer->profile_image && Storage::disk('public')->exists($trainer->profile_image)) {
                Storage::disk('public')->delete($trainer->profile_image);
            }
            
            // Log trainer deletion before deleting
            Log::info('Trainer deleted by admin', [
                'admin_id' => Auth::id(),
                'deleted_trainer_id' => $trainer->id,
                'deleted_trainer_email' => $trainer->email
            ]);
            
            $trainer->delete();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Trainer deleted successfully'
                ]);
            }
            
            return redirect()->route('admin.trainers.index')
                           ->with('success', 'Trainer deleted successfully');
                           
        } catch (\Exception $e) {
            Log::error('Failed to delete trainer: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'trainer_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete trainer: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Failed to delete trainer: ' . $e->getMessage());
        }
    }
    
    /**
     * Manage trainer certifications
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $trainerId
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function certifications(Request $request, $trainerId)
    {
        try {
            $trainer = User::where('role', 'trainer')
                          ->with('certifications')
                          ->findOrFail($trainerId);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'certifications' => $trainer->certifications
                ]);
            }
            
            return view('trainer.certifications.index', compact('trainer'));
            
        } catch (\Exception $e) {
            Log::error('Failed to load trainer certifications: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trainer not found'
                ], 404);
            }
            
            return redirect()->route('admin.trainers.index')->with('error', 'Trainer not found');
        }
    }
    
    /**
     * Store a new certification for trainer
     * AJAX only endpoint
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $trainerId
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeCertification(Request $request, $trainerId): JsonResponse
    {
        try {
            $trainer = User::where('role', 'trainer')->findOrFail($trainerId);
            
            // Validate certification data
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'issuing_organization' => 'required|string|max:255',
                'issue_date' => 'required|date',
                'expiry_date' => 'nullable|date|after:issue_date',
                'credential_id' => 'nullable|string|max:255',
                'credential_url' => 'nullable|url|max:500'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Create certification
            $certification = UserCertification::create([
                'user_id' => $trainer->id,
                'certificate_name' => $request->name,
                'issuing_organization' => $request->issuing_organization,
                'issue_date' => $request->issue_date,
                'expiry_date' => $request->expiry_date,
                'credential_id' => $request->credential_id,
                'credential_url' => $request->credential_url
            ]);
            
            Log::info('Certification added to trainer by admin', [
                'admin_id' => Auth::id(),
                'trainer_id' => $trainer->id,
                'certification_id' => $certification->id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Certification added successfully',
                'certification' => $certification
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to add certification: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to add certification: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete trainer certification
     * AJAX only endpoint
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $trainerId
     * @param  int  $certificationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCertification(Request $request, $trainerId, $certificationId): JsonResponse
    {
        try {
            $trainer = User::where('role', 'trainer')->findOrFail($trainerId);
            $certification = UserCertification::where('user_id', $trainer->id)
                                            ->findOrFail($certificationId);
            
            Log::info('Certification deleted by admin', [
                'admin_id' => Auth::id(),
                'trainer_id' => $trainer->id,
                'certification_id' => $certification->id
            ]);
            
            $certification->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Certification deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to delete certification: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete certification'
            ], 500);
        }
    }
    
    /**
     * Get trainer testimonials with statistics
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $trainerId
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function testimonials(Request $request, $trainerId)
    {
        try {
            $trainer = User::where('role', 'trainer')
                          ->with(['receivedTestimonials.client'])
                          ->findOrFail($trainerId);
            
            $testimonialStats = [
                'total_testimonials' => $trainer->receivedTestimonials->count(),
                'average_rating' => $trainer->receivedTestimonials->avg('rate') ?? 0,
                'total_likes' => $trainer->receivedTestimonials->sum('likes'),
                'rating_distribution' => $trainer->receivedTestimonials->groupBy('rate')
                    ->map(function($group) {
                        return $group->count();
                    })->toArray()
            ];
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'testimonials' => $trainer->receivedTestimonials,
                    'stats' => $testimonialStats
                ]);
            }
            
            return view('admin.trainers.testimonials', compact('trainer', 'testimonialStats'));
            
        } catch (\Exception $e) {
            Log::error('Failed to load trainer testimonials: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trainer not found'
                ], 404);
            }
            
            return redirect()->route('admin.trainers.index')->with('error', 'Trainer not found');
        }
    }
    
    /**
     * Toggle trainer status (activate/deactivate)
     * AJAX only endpoint
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus(Request $request, $id): JsonResponse
    {
        try {
            $trainer = User::where('role', 'trainer')->findOrFail($id);
            
            // Toggle email verification status
            $trainer->email_verified_at = $trainer->email_verified_at ? null : now();
            $trainer->save();
            
            Log::info('Trainer status toggled by admin', [
                'admin_id' => Auth::id(),
                'trainer_id' => $trainer->id,
                'new_status' => $trainer->email_verified_at ? 'active' : 'inactive'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Trainer status updated successfully',
                'status' => $trainer->email_verified_at ? 'active' : 'inactive'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to toggle trainer status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update trainer status'
            ], 500);
        }
    }
}