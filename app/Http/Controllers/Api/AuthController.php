<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Models\PhotographerProfile;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * POST /api/v1/auth/register
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'email'       => 'required|email|max:150|unique:users,email',
            'phone'       => 'nullable|string|max:20',
            'password'    => 'required|string|min:6',
            'country_id'  => 'required|exists:countries,id',
            'state_id'    => 'required|exists:states,id',
            'city_id'     => 'required|exists:cities,id',
            'device_name' => 'required|string|max:100',
            'device_type' => 'required|in:android,ios,web',
        ]);

        // Create user
        $user = User::create([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'phone'      => $validated['phone'] ?? null,
            'password'   => $validated['password'],
            'role'       => 'photographer',
            'country_id' => $validated['country_id'],
            'state_id'   => $validated['state_id'],
            'city_id'    => $validated['city_id'],
            'status'     => 'active',
        ]);

        // Create empty photographer profile
        PhotographerProfile::create(['user_id' => $user->id]);

        // Create Sanctum token
        $token = $user->createToken($validated['device_name']);

        // Create device token entry
        DeviceToken::create([
            'user_id'         => $user->id,
            'device_name'     => $validated['device_name'],
            'device_type'     => $validated['device_type'],
            'access_token_id' => $token->accessToken->id,
        ]);

        return $this->created([
            'user'  => $user->load(['country', 'state', 'city']),
            'token' => $token->plainTextToken,
        ], 'Registration successful');
    }

    /**
     * POST /api/v1/auth/login
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'login'       => 'required|string',
            'password'    => 'required|string',
            'device_name' => 'required|string|max:100',
            'device_type' => 'required|in:android,ios,web',
        ]);

        // Allow login via email or phone number
        $loginField = filter_var($validated['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $user = User::where($loginField, $validated['login'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return $this->error('Invalid credentials', 401);
        }

        if ($user->status === 'blocked') {
            return $this->error('Your account has been blocked. Please contact admin.', 403);
        }

        // Create Sanctum token (one per device)
        $token = $user->createToken($validated['device_name']);

        // Create device token entry
        DeviceToken::create([
            'user_id'         => $user->id,
            'device_name'     => $validated['device_name'],
            'device_type'     => $validated['device_type'],
            'access_token_id' => $token->accessToken->id,
        ]);

        return $this->success([
            'user'  => $user->load(['country', 'state', 'city']),
            'token' => $token->plainTextToken,
        ], 'Login successful');
    }

    /**
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        $accessTokenId = $user->currentAccessToken()->id;

        // Delete device token for this session
        DeviceToken::where('user_id', $user->id)
                   ->where('access_token_id', $accessTokenId)
                   ->delete();

        // Revoke Sanctum token
        $user->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully');
    }

    /**
     * POST /api/v1/fcm-token
     */
    public function updateFcmToken(Request $request)
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = $request->user();
        $accessTokenId = $user->currentAccessToken()->id;

        // Use updateOrCreate to ensure FCM token is always saved
        // Even if DeviceToken wasn't created during login for some reason
        DeviceToken::updateOrCreate(
            [
                'user_id'         => $user->id,
                'access_token_id' => $accessTokenId,
            ],
            [
                'fcm_token'      => $validated['fcm_token'],
                'device_name'    => $request->header('User-Agent', 'Unknown'),
                'device_type'    => 'android',
                'last_active_at' => now(),
            ]
        );

        \Illuminate\Support\Facades\Log::info('FCM token saved', [
            'user_id'   => $user->id,
            'token_preview' => substr($validated['fcm_token'], 0, 20) . '...',
        ]);

        return $this->success(null, 'FCM token updated');
    }

    /**
     * POST /api/v1/auth/forgot-password
     * Sends a 6-digit OTP to the user's email
     */
    public function forgotPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return $this->error('No account found with this email address', 404);
        }

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store in password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $validated['email']],
            [
                'token'      => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        // Send OTP via email
        try {
            Mail::raw(
                "Your password reset OTP is: {$otp}\n\nThis code expires in 15 minutes.\n\nIf you didn't request this, please ignore this email.",
                function ($message) use ($user) {
                    $message->to($user->email)
                            ->subject('Password Reset OTP - Freelancer Photographers');
                }
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Password reset email failed: ' . $e->getMessage());
            return $this->error('Failed to send email. Please try again later.', 500);
        }

        return $this->success(null, 'OTP sent to your email address');
    }

    /**
     * POST /api/v1/auth/reset-password
     * Verify OTP and reset password
     */
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email'                 => 'required|email',
            'otp'                   => 'required|string|size:6',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')
                    ->where('email', $validated['email'])
                    ->first();

        if (! $record) {
            return $this->error('Invalid or expired OTP', 422);
        }

        // Check expiry (15 minutes)
        if (now()->diffInMinutes($record->created_at) > 15) {
            DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();
            return $this->error('OTP has expired. Please request a new one.', 422);
        }

        // Verify OTP
        if (! Hash::check($validated['otp'], $record->token)) {
            return $this->error('Invalid OTP', 422);
        }

        // Update password
        $user = User::where('email', $validated['email'])->first();
        $user->update(['password' => Hash::make($validated['password'])]);

        // Delete the token
        DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

        return $this->success(null, 'Password reset successfully');
    }

    /**
     * PUT /api/v1/auth/change-password (Authenticated)
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return $this->error('Current password is incorrect', 422);
        }

        $user->update(['password' => Hash::make($validated['password'])]);

        return $this->success(null, 'Password changed successfully');
    }
}
