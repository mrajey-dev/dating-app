<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    // Get Profile
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    // Update Profile
    public function update(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'name' => 'string|max:255',
            'bio' => 'string|max:1000',
            'profile_picture' => 'image|max:2048',
            'gender' => 'string',
            'sexual_preference' => 'string',
            'status' => 'string',
        ]);

        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profiles', 'public');
            $user->profile_picture = $path;
        }

        $user->update($request->only(['name','bio','gender','sexual_preference','status']));
        return response()->json($user);
    }
}
