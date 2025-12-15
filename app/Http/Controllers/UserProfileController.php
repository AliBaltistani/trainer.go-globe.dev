<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserLocation;

use \Illuminate\Support\Facades\Log;

/**
 * User Profile Controller
 * 
 * Handles user profile management functionality
 * Allows users to view and update their profile information
 * 
 * @package     Laravel CMS App
 * @subpackage  Controllers
 * @category    User Management
 * @author      Go Globe CMS Team
 * @since       1.0.0
 */
class UserProfileController extends Controller
{
    /**
     * Constructor - Apply auth middleware
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show user profile page
     * 
     * @param User|null $user Optional user parameter for admin viewing other users
     * @return \Illuminate\Contracts\View\View
     */
    public function index(User $user = null)
    {
        $currentUser = Auth::user();
        
        // If no user specified, show current user's profile
        if (!$user) {
            $user = $currentUser;
        }
        
        // Check permissions: users can view their own profile, admins can view any profile
        if ($user->id !== $currentUser->id && $currentUser->role !== 'admin') {
            abort(403, 'Unauthorized access to this profile.');
        }
        
        // Determine if viewing own profile and if can edit
        $isOwnProfile = ($user->id === $currentUser->id);
        $canEdit = $isOwnProfile || $currentUser->role === 'admin';
        
        // Load goals for clients
        if ($user->role === 'client') {
            $user->load('goals');
        }
        
        return view('profile.index', compact('user', 'isOwnProfile', 'canEdit'));
    }

    /**
     * Show profile edit form
     * 
     * @param User|null $user Optional user parameter for admin editing other users
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(User $user = null)
    {
        $currentUser = Auth::user();
        
        // If no user specified, edit current user's profile
        if (!$user) {
            $user = $currentUser;
        }
        
        // Check permissions: users can edit their own profile, admins can edit any profile
        if ($user->id !== $currentUser->id && $currentUser->role !== 'admin') {
            abort(403, 'Unauthorized access to edit this profile.');
        }
        
        // Determine if editing own profile and if can change role
        $isOwnProfile = ($user->id === $currentUser->id);
        $canChangeRole = !$isOwnProfile && $currentUser->role === 'admin';
        
        // Load goals for clients
        if ($user->role === 'client') {
            $user->load('goals');
        }
        
        return view('profile.edit', compact('user', 'isOwnProfile', 'canChangeRole'));
    }

    /**
     * Update user profile information
     *
     * @param Request $request
     * @param User|null $user Optional user parameter for admin editing other users
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $user = null)
    {
        $currentUser = Auth::user(); // The logged-in user making the request
        
        // If no user specified, update current user's profile
        if (!$user) {
            $user = $currentUser;
        }
        
        // Check permissions: users can update their own profile, admins can update any profile
        if ($user->id !== $currentUser->id && $currentUser->role !== 'admin') {
            abort(403, 'Unauthorized access to update this profile.');
        }
        
        $isOwnProfile = ($user->id === $currentUser->id);
        
        // Base validation rules for all users
        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20|unique:users,phone,' . $user->id,
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'timezone' => 'nullable|string|max:50'
        ];
        
        $validationMessages = [
            'name.required' => 'Name is required.',
            'name.max' => 'Name cannot exceed 255 characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already taken.',
            'phone.required' => 'Phone number is required.',
            'phone.unique' => 'This phone number is already taken.',
            'profile_image.image' => 'Profile image must be an image file.',
            'profile_image.mimes' => 'Profile image must be jpeg, png, jpg, or gif.',
            'profile_image.max' => 'Profile image size cannot exceed 2MB.',
            'timezone.max' => 'Timezone value is invalid.'
        ];
        
        // Add admin-specific validation rules - only admins can change roles when editing others
        if ($currentUser->role === 'admin' && !$isOwnProfile) {
            $validationRules['role'] = 'nullable|in:admin,trainer,client';
            $validationMessages['role.in'] = 'Please select a valid role.';
        }

        // Admin-only: allow status change
        if ($currentUser->role === 'admin') {
            $validationRules['status'] = 'nullable|boolean';
        }
        
        // Determine target role for validation (use requested role if admin is changing it, otherwise current role)
        $targetRole = $user->role;
        if ($currentUser->role === 'admin' && !$isOwnProfile && $request->has('role')) {
            $targetRole = $request->role;
        }
        
        // Add trainer-specific validation rules if user is or will be a trainer
        if ($targetRole === 'trainer') {
            $validationRules = array_merge($validationRules, [
                'designation' => 'nullable|string|max:255',
                'experience' => 'nullable|in:less_than_1_year,1_year,2_years,3_years,4_years,5_years,6_years,7_years,8_years,9_years,10_years,more_than_10_years',
                'about' => 'nullable|string|max:1000',
                'training_philosophy' => 'nullable|string|max:1000',
                'business_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
            ]);
            
            $validationMessages = array_merge($validationMessages, [
                'designation.max' => 'Designation cannot exceed 255 characters.',
                'experience.in' => 'Please select a valid experience level.',
                'about.max' => 'About section cannot exceed 1000 characters.',
                'training_philosophy.max' => 'Training philosophy cannot exceed 1000 characters.',
                'business_logo.image' => 'Business logo must be an image file.',
                'business_logo.mimes' => 'Business logo must be jpeg, png, jpg, gif, or webp.',
                'business_logo.max' => 'Business logo size cannot exceed 2MB.'
            ]);
        }
        
        // Validate input data
        $validator = Validator::make($request->all(), $validationRules, $validationMessages);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                // Delete old profile image if exists
                if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                    Storage::disk('public')->delete($user->profile_image);
                }
                
                // Store new profile image
                $imagePath = $request->file('profile_image')->store('profile-images', 'public');
                $user->profile_image = $imagePath;
            }

            // Handle business logo upload (trainers only)
            if ($targetRole === 'trainer' && $request->hasFile('business_logo')) {
                if ($user->business_logo && Storage::disk('public')->exists($user->business_logo)) {
                    Storage::disk('public')->delete($user->business_logo);
                }
                $logoPath = $request->file('business_logo')->store('business-logos', 'public');
                $user->business_logo = $logoPath;
            }

            // Update basic user information
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            
            // Update timezone if provided
            if ($request->has('timezone')) {
                $user->timezone = $request->timezone;
            }

            // Admin-only: update active/inactive using email_verified_at
            if ($currentUser->role === 'admin' && $request->has('status')) {
                $user->email_verified_at = $request->boolean('status') ? now() : null;
            }
            
            // SECURITY: Only admins can update role when editing other users
            if ($currentUser->role === 'admin' && !$isOwnProfile && $request->has('role')) {
                $user->role = $request->role;
            }
            
            // Update trainer-specific fields if user is or will be a trainer
            if ($targetRole === 'trainer') {
                $user->designation = $request->designation;
                $user->experience = $request->experience;
                $user->about = $request->about;
                $user->training_philosophy = $request->training_philosophy;
            } else {
                // Clear trainer-specific fields if role is changed from trainer to something else
                if ($user->role !== 'trainer' && $targetRole !== 'trainer') {
                    $user->designation = null;
                    $user->experience = null;
                    $user->about = null;
                    $user->training_philosophy = null;
                    $user->business_logo = null;
                }
            }
            
            $user->save();
            
            // Handle goals for clients
            if ($user->role === 'client') {
                // Handle goals removal
                if ($request->filled('goals_to_remove')) {
                    $goalsToRemove = $request->goals_to_remove;
                    
                    // Handle JSON string input
                    if (is_string($goalsToRemove)) {
                        $goalsToRemove = json_decode($goalsToRemove, true);
                    }
                    
                    if (is_array($goalsToRemove)) {
                        foreach ($goalsToRemove as $goalId) {
                            $goal = \App\Models\Goal::where('user_id', $user->id)->find($goalId);
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
                                // Check if goal already exists for this user
                                $existingGoal = \App\Models\Goal::where('user_id', $user->id)
                                    ->where('name', trim($goalName))
                                    ->first();
                                
                                if (!$existingGoal) {
                                    \App\Models\Goal::create([
                                        'name' => trim($goalName),
                                        'user_id' => $user->id,
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
                                    $existingGoal = \App\Models\Goal::where('user_id', $user->id)
                                        ->where('name', trim($goalName))
                                        ->first();
                                    
                                    if (!$existingGoal) {
                                        \App\Models\Goal::create([
                                            'name' => trim($goalName),
                                            'user_id' => $user->id,
                                            'status' => 1
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Redirect based on who is updating
            if ($isOwnProfile) {
                return redirect()->route('profile.index')
                    ->with('success', 'Profile updated successfully!');
            } else {
                // Admin editing another user - redirect to admin user view
                return redirect()->route('admin.users.show', $user->id)
                    ->with('success', 'User profile updated successfully!');
            }

        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Profile update failed: ' . $e->getMessage());
            
            return back()->withErrors(['error' => 'Profile update failed. Please try again.'])->withInput();
        }
    }

    public function deleteBusinessLogo(Request $request)
    {
        $user = Auth::user();
        
        try {
            if ($user->business_logo && Storage::disk('public')->exists($user->business_logo)) {
                Storage::disk('public')->delete($user->business_logo);
                $user->business_logo = null;
                $user->save();
                
                return back()->with('success', 'Business logo deleted successfully!');
            }
            
            return back()->withErrors(['error' => 'No business logo found to delete.']);

        } catch (\Exception $e) {
            Log::error('Business logo deletion failed: ' . $e->getMessage());
            
            return back()->withErrors(['error' => 'Failed to delete business logo. Please try again.']);
        }
    }

    /**
     * Show change password form
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showChangePasswordForm()
    {
        $user = Auth::user();
        return view('profile.change-password', compact('user'));
    }

    /**
     * Update user password
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();
        
        // Log the password change request for debugging
        Log::info('Password change request received', [
            'user_id' => $user->id
        ]);
        
        // Validate password input
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required'
        ], [
            'current_password.required' => 'Current password is required.',
            'password.required' => 'New password is required.',
            'password.min' => 'New password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password_confirmation.required' => 'Password confirmation is required.'
        ]);

        if ($validator->fails()) {
            Log::warning('Password change validation failed', [
                'user_id' => $user->id,
                'errors' => $validator->errors()->toArray()
            ]);
            return back()->withErrors($validator);
        }

        Log::info('Password change validation passed', ['user_id' => $user->id]);

        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Check if new password is different from current password
        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'New password must be different from current password.']);
        }

        try {
            // Update password
            $user->password = Hash::make($request->password);
            $user->save();

            return redirect()->route('profile.index')
                ->with('success', 'Password changed successfully!');

        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Password change failed: ' . $e->getMessage());
            
            return back()->withErrors(['error' => 'Password change failed. Please try again.']);
        }
    }

    /**
     * Delete user profile image
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteProfileImage(Request $request)
    {
        $user = Auth::user();
        
        try {
            // Delete profile image if exists
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
                $user->profile_image = null;
                $user->save();
                
                return back()->with('success', 'Profile image deleted successfully!');
            }
            
            return back()->withErrors(['error' => 'No profile image found to delete.']);

        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Profile image deletion failed: ' . $e->getMessage());
            
            return back()->withErrors(['error' => 'Failed to delete profile image. Please try again.']);
        }
    }

    /**
     * Show user activity log (if needed for future implementation)
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function activityLog()
    {
        $user = Auth::user();
        // This can be expanded to show user activity logs
        return view('profile.activity-log', compact('user'));
    }

    /**
     * Show user settings page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function settings()
    {
        $user = Auth::user();
        return view('profile.settings', compact('user'));
    }

    /**
     * Update or create user location
     * Allows users to manage their own location information
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function updateLocation(Request $request)
    {
        $user = Auth::user();
        
        // Validate location data
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'country' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'zipcode' => 'nullable|string|max:20',
        ]);

        // Security check: users can only update their own location
        if ($validated['user_id'] != $user->id && $user->role !== 'admin') {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access Denied. You can only update your own location.'
                ], 403);
            }
            abort(403, 'Access Denied. You can only update your own location.');
        }

        try {
            DB::beginTransaction();
            
            // Get or create location
            $location = UserLocation::firstOrNew(['user_id' => $validated['user_id']]);
            $location->country = $validated['country'] ?? null;
            $location->state = $validated['state'] ?? null;
            $location->city = $validated['city'] ?? null;
            $location->address = $validated['address'] ?? null;
            $location->zipcode = $validated['zipcode'] ?? null;
            $location->save();
            
            DB::commit();
            
            Log::info('User location updated successfully', [
                'location_id' => $location->id,
                'user_id' => $location->user_id
            ]);
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Location saved successfully',
                    'data' => [
                        'id' => $location->id,
                        'country' => $location->country,
                        'state' => $location->state,
                        'city' => $location->city,
                        'address' => $location->address,
                        'zipcode' => $location->zipcode
                    ]
                ]);
            }
            
            return redirect()->back()
                ->with('success', 'Location saved successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating user location: ' . $e->getMessage());
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save location. Please try again.',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()
                ->withInput()
                ->with('error', 'Failed to save location. Please try again.');
        }
    }
}
