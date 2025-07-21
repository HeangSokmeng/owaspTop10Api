<?php

namespace App\Http\Controllers;

use ApiResponse;
use App\Models\User;
use App\Models\UserRole;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function getProfile(Request $req, $id)
    {
        // $authUser = UserService::getAuthUser();
        // $id = $authUser->id;
        $user = User::selectRaw('id, name,email')->find($id);
        if ($user)
            $user->role_id = UserRole::where('user_id', $id)->take(1)->value('role_id');
        return ApiResponse::JsonResult($user);
    }
    public function getProfileSecureID(Request $req, $id)
    {
        $user = User::selectRaw('id, name,email')->find($id);
        if ($user)
            $user->role_id = UserRole::where('user_id', $id)->take(1)->value('role_id');
        return ApiResponse::JsonResult($user);
    }

    public function getProfileSecure()
    {
        $authUser = UserService::getAuthUser();
        $id = $authUser->id;
        $user = User::selectRaw('id, name,email')->find($id);
        if ($user)
            $user->role_id = UserRole::where('user_id', $id)->take(1)->value('role_id');
        return ApiResponse::JsonResult($user);
    }


    public function login(Request $request)
    {
        // Validate input
        $validate = Validator::make($request->only(['email', 'password']), [
            'email' => 'required|string',
            'password' => 'required|string|min:6|max:16',
        ]);
        if ($validate->fails())
            return ApiResponse::ValidateFail($validate->errors()->all());
        $credentials = $validate->validated();
        $account = strtolower($credentials['email']);
        $password = $credentials['password'];
        $user = User::where(function ($q) use ($account) {
            $q->whereRaw('LOWER(email) = ?', [$account])
                ->orWhereRaw('LOWER(name) = ?', [$account]);
        })->first();
        if (!$user || !password_verify($password, $user->password))
            return ApiResponse::NotFound('Invalid email or password');
        if (!$token = JWTAuth::fromUser($user))
            return ApiResponse::ValidateFail('Could not create token');
        $user->roles = UserService::getRolesByUsers($user->id);
        $data = (object) [
            'email' => $user->email,
            'name' => $user->name,
            'roles' => $user->roles,
            'token' => $token,
        ];
        return ApiResponse::JsonResult($data, 'Login successful.');
    }
}
