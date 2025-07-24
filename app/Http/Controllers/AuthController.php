<?php

namespace App\Http\Controllers;

use ApiResponse;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function update(Request $request)
    {
        // Log::info("Test update");

        $user_auth = UserService::getAuthUser();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            // 'email' => 'sometimes|required|email|unique:users,email,' . $user_auth->id,
            'password' => 'sometimes|required|string|min:6',
            'role_id' => 'sometimes|required|exists:roles,id',
        ]);
        // Log::info($validated);
        $user = User::find($user_auth->id);
        Log::info($user_auth->id);
        // if (!$user) {
        //     return response()->json(['message' => 'User not found'], 404);
        // }

        // if (isset($validated['password'])) {
        //     $validated['password'] = Hash::make($validated['password']);
        // }

        // $user->update($validated);

        // if (isset($validated['role_id'])) {
        //     $user->roles()->sync([$validated['role_id']]);
        // }

        return ApiResponse::JsonResult($user, 'Success');
    }
}
