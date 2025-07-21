<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserRole;
use DataResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserService
{
    public static function getAuthUser()
    {
        $user = JWTAuth::user();
        if ($user) {
            $hasUser = User::find($user->id);
            if ($hasUser) {
                return DataResponse::JsonRaw([
                    'error' => false,
                    'status_code' => 200,
                    'status' => 'OK',
                    'id' => $hasUser->id,
                    'name' => $hasUser->name,
                    'email' => $hasUser->email,
                    'info' => $hasUser,
                ]);
            }
        }
        Log::warning("Unauthenticated request to getAuthUser");
        return DataResponse::Unauthorized();
    }
    public static function getRolesByUsers($userId)
    {
        return UserRole::from('user_roles as ur')->where('ur.user_id', $userId)->join('roles as r', 'r.id', '=', 'ur.role_id')->selectRaw('r.name as role,ur.role_id')->get();
    }
}
