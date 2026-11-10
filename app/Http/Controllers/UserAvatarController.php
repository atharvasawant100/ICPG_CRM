<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserAvatarController extends Controller
{
    /**
     * Upload or update a user's avatar photo.
     */
    public function update(Request $request, User $user)
    {
        abort_unless($user->canManageAvatar($request->user()), 403, 'You do not have permission to update this profile photo.');

        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'], // Max 5MB
        ]);

        // Delete existing avatar file if present
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile photo updated successfully.',
                'avatar_url' => asset('storage/' . $path),
            ]);
        }

        return back()->with('success', 'Profile photo updated successfully.');
    }

    /**
     * Remove a user's avatar photo.
     */
    public function destroy(Request $request, User $user)
    {
        abort_unless($user->canManageAvatar($request->user()), 403, 'You do not have permission to remove this profile photo.');

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile photo removed successfully.',
            ]);
        }

        return back()->with('success', 'Profile photo removed successfully.');
    }
}
