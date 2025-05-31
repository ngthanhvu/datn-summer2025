<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;
use App\Mail\WelcomeEmail;
use App\Mail\OtpEmail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'role' => 'required',
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'user'
        ]);

        Mail::to($user->email)->send(new WelcomeEmail($user));

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('token', 'user'));
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json(compact('token'));
    }

    public function me()
    {
        return response()->json([
            'id' => Auth::id(),
            'username' => Auth::user()->username,
            'email' => Auth::user()->email,
            'role' => Auth::user()->role
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json(['message' => 'Logged out']);
    }

    public function refresh()
    {
        return response()->json([
            'token' => Auth::refresh(),
        ]);
    }

    public function redirectToGoogle()
    {
        $url = Socialite::driver('google')
            ->stateless()
            ->redirectUrl(config('services.google.redirect'))
            ->redirect()
            ->getTargetUrl();

        return response()->json(['url' => $url]);
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->redirectUrl(config('services.google.redirect'))
                ->user();

            $user = User::updateOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'username' => $googleUser->getName(),
                    'password' => Hash::make(uniqid()),
                    'oauth_provider' => 'google',
                    'oauth_id' => $googleUser->getId(),
                ]
            );

            Mail::to($user->email)->send(new WelcomeEmail($user));

            $token = JWTAuth::fromUser($user);

            $frontendUrl = config('app.frontend_url') . '/auth/google/callback?token=' . urlencode($token) . '&user=' . urlencode(json_encode($user));

            return redirect($frontendUrl);
        } catch (\Exception $e) {
            Log::error('Google login failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $frontendUrl = config('app.frontend_url') . '?error=' . urlencode('Đăng nhập Google thất bại.');
            return redirect($frontendUrl);
        }
    }

    public function listUser(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $users = User::all();

        return response()->json([
            'users' => $users
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.required' => 'Email là bắt buộc',
            'email.email' => 'Email không đúng định dạng',
            'email.exists' => 'Email này không tồn tại trong hệ thống'
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $user->setOtp($otp, 10);

            Mail::to($user->email)->send(new OtpEmail($otp, $user));

            return response()->json([
                'message' => 'Mã OTP đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư.',
                'expires_in' => '10 phút'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send OTP: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Có lỗi xảy ra khi gửi mã OTP. Vui lòng thử lại.'
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'otp' => 'required|string|size:6',
                'password' => 'required|string|min:6|confirmed',
            ], [
                'email.required' => 'Email là bắt buộc',
                'email.email' => 'Email không đúng định dạng',
                'otp.required' => 'Mã OTP là bắt buộc',
                'otp.size' => 'Mã OTP phải có 6 số',
                'password.required' => 'Mật khẩu mới là bắt buộc',
                'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
                'password.confirmed' => 'Xác nhận mật khẩu không khớp'
            ]);

            if ($validator->fails()) {
                Log::warning('Reset password validation failed', [
                    'email' => $request->email,
                    'errors' => $validator->errors()->toArray()
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            try {
                $user = User::where('email', $request->email)->first();
                
                if (!$user) {
                    Log::warning('Reset password: User not found', ['email' => $request->email]);
                    
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Người dùng không tồn tại'
                    ], 404);
                }
                
                Log::info('User found for password reset', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
                
            } catch (\Exception $e) {
                Log::error('Database error when finding user: ' . $e->getMessage(), [
                    'email' => $request->email,
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lỗi truy vấn cơ sở dữ liệu'
                ], 500);
            }

            try {
                if (!$user->hasValidOtp($request->otp)) {
                    Log::warning('Invalid or expired OTP', [
                        'user_id' => $user->id,
                        'provided_otp' => $request->otp,
                        'stored_otp' => $user->otp,
                        'otp_expires_at' => $user->otp_expires_at
                    ]);
                    
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Mã OTP không hợp lệ hoặc đã hết hạn'
                    ], 400);
                }
                
                Log::info('OTP validation successful', ['user_id' => $user->id]);
                
            } catch (\Exception $e) {
                Log::error('Error during OTP validation: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lỗi xác thực OTP'
                ], 500);
            }

            try {
                $hashedPassword = Hash::make($request->password);
                
                $user->update([
                    'password' => $hashedPassword
                ]);
                
                Log::info('Password updated successfully', ['user_id' => $user->id]);
                
            } catch (\Exception $e) {
                Log::error('Error updating password: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lỗi khi cập nhật mật khẩu'
                ], 500);
            }

            try {
                $user->clearOtp();
                Log::info('OTP cleared successfully', ['user_id' => $user->id]);
                
            } catch (\Exception $e) {
                Log::warning('Error clearing OTP (non-critical): ' . $e->getMessage(), [
                    'user_id' => $user->id
                ]);
            }

            try {
                $token = JWTAuth::fromUser($user);
                
                Log::info('JWT token generated successfully', ['user_id' => $user->id]);
                
            } catch (\Exception $e) {
                Log::error('Error generating JWT token: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lỗi tạo token xác thực'
                ], 500);
            }

            Log::info('Password reset completed successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Mật khẩu đã được đặt lại thành công',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role
                ]
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Unexpected error in resetPassword: ' . $e->getMessage(), [
                'email' => $request->email ?? 'unknown',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi không mong muốn xảy ra. Vui lòng thử lại sau.',
                'debug' => config('app.debug') ? [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }

     public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'error' => 'Người dùng không tồn tại'
            ], 404);
        }

        if (!$user->hasValidOtp($request->otp)) {
            return response()->json([
                'error' => 'Mã OTP không hợp lệ hoặc đã hết hạn'
            ], 400);
        }

        return response()->json([
            'message' => 'Mã OTP hợp lệ',
            'expires_at' => $user->otp_expires_at->format('Y-m-d H:i:s')
        ]);
    }
}
