<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index()
    {
        $user    = auth()->user();
        $profile = $user->adminProfile ?? new AdminProfile(['user_id' => $user->id]);

        return view('admin.profile', compact('user', 'profile'));
    }

    /**
     * Update login_id (username) and admin_profiles.full_name + email.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'login_id'  => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('users', 'login_id')->ignore($user->id),
            ],
            'full_name' => 'required|string|max:255',
            'email'     => 'nullable|email|max:255',
        ]);

        // Update users.login_id
        $user->update([
            'login_id' => $data['login_id'],
        ]);

        // Update or create the admin_profiles row
        AdminProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => $data['full_name'],
                'email'     => $data['email'],
            ]
        );

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Upload a new profile picture (requires the migration above).
     */
    public function updatePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $user    = auth()->user();
        $profile = AdminProfile::firstOrCreate(['user_id' => $user->id]);

        if ($profile->profile_picture && Storage::disk('public')->exists($profile->profile_picture)) {
            Storage::disk('public')->delete($profile->profile_picture);
        }

        $path = $request->file('profile_picture')->store('profile_pictures', 'public');
        $profile->update(['profile_picture' => $path]);

        return back()->with('success', 'Profile picture updated.');
    }

    /**
     * Change password. Login uses login_id, so we only need current_password here.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }
}