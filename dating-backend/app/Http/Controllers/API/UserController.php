<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
public function toggleLike($id)
{
    $userId = auth()->id();

    $like = DB::table('user_likes')
        ->where('user_id', $userId)
        ->where('liked_user_id', $id)
        ->first();

    if ($like) {
        // UNLIKE
        DB::table('user_likes')
            ->where('user_id', $userId)
            ->where('liked_user_id', $id)
            ->delete();

        DB::table('users')
            ->where('id', $id)
            ->decrement('like_count');

        return response()->json(['liked' => false]);
    }

    // LIKE
    DB::table('user_likes')->insert([
        'user_id' => $userId,
        'liked_user_id' => $id,
        'created_at' => now(),
        'updated_at' => now()
    ]);

    DB::table('users')
        ->where('id', $id)
        ->increment('like_count');

    return response()->json(['liked' => true]);
}
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        return response()->json($user);
    }
    // Return all users
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }
    public function register(Request $request) {

    $data = $request->all();
$validator = \Validator::make($request->all(), [
    'firstName' => 'required|string|max:50',
    'lastName' => 'required|string|max:50',
    'email' => 'required|email|unique:users,email',
    'mobile' => 'required|string',
    'gender' => 'required|in:Male,Female,Other',
    'dob' => 'required|date',
    'genderPreference' => 'nullable|array',
    'status' => 'nullable|in:Single,Married,Divorced,Separated,Widowed',
    'rate' => 'nullable|numeric',
    'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
]);

if ($validator->fails()) {
    return response()->json(['errors' => $validator->errors()], 422);
}



    $user = new User();
    $user->first_name = $data['firstName'];
    $user->last_name = $data['lastName'];
    $user->email = $data['email'];
    $user->phone_number = $data['countryCode'].' '.$data['mobile'];
    $user->gender = $data['gender'];
    $user->dob = $data['dob'] ?? null;
    $user->gender_preference = json_encode($data['genderPreference'] ?? []); // store as JSON
    $user->status = $data['status'];
    $user->subtitle = $data['subtitle'] ?? null;
    $user->city = $data['city'] ?? null;
    $user->address = $data['address'] ?? null;
    $user->state = $data['state'] ?? null;
    $user->country = $data['country'] ?? 'India';
    $user->pin_code = $data['pincode'] ?? null;
    $user->rate = $data['rate'] ?? 0;
    $user->verified_badge = $data['verified'] ?? 0;
    $user->habits = json_encode(explode(',', $data['habits'] ?? ''));

    // Profile photo upload
    if ($request->hasFile('profile_photo')) {
        $path = $request->file('profile_photo')->store('profiles', 'public');
        $user->profile_photo = $path;
    }

    // Gallery upload
    if ($request->hasFile('gallery')) {
        $gallery = [];
        foreach ($request->file('gallery') as $file) {
            $gallery[] = $file->store('gallery', 'public');
        }
        $user->photo_gallery = json_encode($gallery);
    }

    // Password hashing
   $user->password = Hash::make($data['password'] ?? '123456'); // default password if not provided

    $user->save();

    return response()->json(['success' => true, 'user' => $user]);
}

}
