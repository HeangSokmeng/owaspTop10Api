<?php

namespace App\Http\Controllers;

use ApiResponse;
use App\Models\User;
use App\Models\UserRole;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
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
        $authUser = UserService::getAuthUser();
        if ($authUser->id != $id)
            return ApiResponse::ValidateFail("You are not allowed to access this user's profile.");
        $user = User::select('id', 'name', 'email')->find($id);
        if ($user)
            $user->role_id = UserRole::where('user_id', $id)->value('role_id');
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


    public function registerInsecure(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string',
        ]);
        if ($validator->fails())
            return ApiResponse::ValidateFail(['errors' => $validator->errors()], 422);
        $user = User::create([
            'name' => $req->name,
            'email' => $req->email,
            'password' => Hash::make($req->password),
        ]);
        return ApiResponse::JsonResult($user, 'User registered successfully');
    }

    public function registerSecure(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&#]/',
                'confirmed'
            ],
        ]);
        if ($validator->fails())
            return ApiResponse::ValidateFail(['errors' => $validator->errors()], 422);
        $user = User::create([
            'name' => $req->name,
            'email' => $req->email,
            'password' => Hash::make($req->password),
        ]);
        return ApiResponse::JsonResult($user, 'User registered successfully');
    }

    public function loginInsecure(Request $req)
    {
        // Validate input
        $validate = Validator::make($req->only(['email', 'password']), [
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

    public function loginSecure(Request $req)
{
    $ip = $req->ip();

    // Validate input
    $validate = Validator::make($req->only(['email', 'password']), [
        'email' => 'required|string',
        'password' => [
            'required',
            // 'string',
            // 'min:8',
            // 'regex:/[a-z]/',
            // 'regex:/[A-Z]/',
            // 'regex:/[0-9]/',
            // 'regex:/[@$!%*?&#]/'
        ],
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

    // Check credentials
    if (!$user || !password_verify($password, $user->password)) {
        // FAILED LOGIN - just log it, rate limiter already incremented in middleware
        $key = "login_attempts:{$ip}";
        $attempts = RateLimiter::attempts($key);
        Log::warning("Failed login attempt #{$attempts} for {$account} from IP {$ip}");

        return ApiResponse::NotFound('Invalid email or password');
    }

    // SUCCESSFUL LOGIN - Clear rate limiter
    $key = "login_attempts:{$ip}";
    RateLimiter::clear($key);

    // Also clear any temporary blocks
    Cache::forget("blocked_ip:{$ip}");

    if (!$token = JWTAuth::fromUser($user)) {
        return ApiResponse::ValidateFail('Could not create token');
    }

    $user->roles = UserService::getRolesByUsers($user->id);

    $data = (object) [
        'email' => $user->email,
        'name' => $user->name,
        'roles' => $user->roles,
        'token' => $token,
    ];

    Log::info("Successful login for {$account} from IP {$ip}");

    return ApiResponse::JsonResult($data, 'Login successful.');
}
}
