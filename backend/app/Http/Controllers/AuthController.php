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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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
            'role' => 'user',
            'ip_user' => $request->ip(),
        ]);

        Log::info('Request IP: ' . request()->ip());
        Log::info('X-Forwarded-For: ' . request()->header('X-Forwarded-For'));

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

        $user = Auth::user();

        if ($user->status === 0) {
            return response()->json(['error' => 'Tài khoản đã bị khóa'], 403);
        }

        $user->update([
            'ip_user' => $request->ip()
        ]);
        Log::info('Request IP: ' . request()->ip());
        Log::info('X-Forwarded-For: ' . request()->header('X-Forwarded-For'));

        return response()->json(compact('token'));
    }

    public function me()
    {
        return response()->json([
            'id' => Auth::id(),
            'username' => Auth::user()->username,
            'email' => Auth::user()->email,
            'role' => Auth::user()->role,
            'avatar' => Auth::user()->avatar,
            'phone' => Auth::user()->phone,
            'gender' => Auth::user()->gender,
            'dateOfBirth' => Auth::user()->dateOfBirth,
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

            if ($user->wasRecentlyCreated) {
                Mail::to($user->email)->send(new WelcomeEmail($user));
            }

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

        $users = User::all()->map(function ($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->avatar ? url($user->avatar) : null,
                'role' => $user->role,
                'oauth_provider' => $user->oauth_provider,
                'oauth_id' => $user->oauth_id,
                'otp_expires_at' => $user->otp_expires_at,
                'status' => $user->status,
                'gender' => $user->gender,
                'dateOfBirth' => $user->dateOfBirth,
                'note' => $user->note,
            ];
        });

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
            $request->validate([
                'email' => 'required|email|exists:users,email',
                'otp' => 'required|array|size:6',
                'otp.*' => 'required|digits:1',
                'password' => 'required|min:6|confirmed',
            ], [
                'email.required' => 'Vui lòng nhập email',
                'email.email' => 'Email không đúng định dạng',
                'email.exists' => 'Email không tồn tại trong hệ thống',
                'otp.required' => 'Vui lòng nhập mã OTP',
                'otp.size' => 'Mã OTP phải có đúng 6 số',
                'otp.*.required' => 'Vui lòng nhập đầy đủ 6 số OTP',
                'otp.*.digits' => 'Mỗi ô OTP phải là 1 số',
                'password.required' => 'Vui lòng nhập mật khẩu mới',
                'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
                'password.confirmed' => 'Xác nhận mật khẩu không khớp',
            ]);

            $otp = implode('', $request->input('otp'));

            $user = User::where('email', $request->email)->first();

            if ($user->otp !== $otp || now()->gt($user->otp_expires_at)) {
                return response()->json([
                    'error' => 'Mã OTP không hợp lệ hoặc đã hết hạn!'
                ], 400);
            }

            $user->update([
                'password' => bcrypt($request->password),
                'otp' => null,
                'otp_expires_at' => null,
            ]);

            return response()->json([
                'message' => 'Mật khẩu đã được cập nhật thành công'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to reset password: ' . $e->getMessage());

            return response()->json([
                'error' => 'Có lỗi xảy ra khi cập nhật mật khẩu. Vui lòng thử lại.'
            ], 500);
        }
    }

    public function resetPasswordProfile(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'current_password' => 'required',
                'password' => 'required|min:6|confirmed',
            ], [
                'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại',
                'password.required' => 'Vui lòng nhập mật khẩu mới',
                'password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự',
                'password.confirmed' => 'Xác nhận mật khẩu không khớp',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'error' => 'Mật khẩu hiện tại không đúng'
                ], 400);
            }

            $user->update([
                'password' => bcrypt($request->password)
            ]);

            return response()->json([
                'message' => 'Cập nhật mật khẩu thành công'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to reset password: ' . $e->getMessage());

            return response()->json([
                'error' => 'Có lỗi xảy ra khi cập nhật mật khẩu. Vui lòng thử lại.'
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

    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'username' => 'sometimes|string|max:255',
                'phone' => 'sometimes|string|max:20|nullable',
                'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048|nullable',
                'gender' => 'sometimes|string|max:10|nullable',
                'dateOfBirth' => 'sometimes|date|nullable',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $updateData = [];

            if ($request->has('username')) {
                $updateData['username'] = $request->username;
            }

            if ($request->has('phone')) {
                $updateData['phone'] = $request->phone;
            }

            if ($request->has('gender')) {
                $updateData['gender'] = $request->gender;
            }

            if ($request->has('dateOfBirth')) {
                $updateData['dateOfBirth'] = $request->dateOfBirth;
            }

            if ($request->hasFile('avatar')) {
                if ($user->avatar && Storage::exists('public/avatars/' . basename($user->avatar))) {
                    Storage::delete('public/avatars/' . basename($user->avatar));
                }

                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $updateData['avatar'] = '/storage/' . $avatarPath;
            }

            $user->update($updateData);

            return response()->json([
                'message' => 'Cập nhật thông tin tài khoản thành công',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar,
                    'gender' => $user->gender,
                    'dateOfBirth' => $user->dateOfBirth,
                    'role' => $user->role,
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to update profile: ' . $e->getMessage());

            return response()->json([
                'error' => 'Có lỗi xảy ra khi cập nhật thông tin tài khoản. Vui lòng thử lại.'
            ], 500);
        }
    }

    public function updateUserByAdmin(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|min:6',
            'status' => 'required|in:0,1',
            'note' => 'nullable|string|max:500',
        ], [
            'username.required' => 'Tên người dùng là bắt buộc',
            'username.string' => 'Tên người dùng phải là chuỗi',
            'username.max' => 'Tên người dùng không được quá 255 ký tự',
            'email.required' => 'Email là bắt buộc',
            'email.email' => 'Email không đúng định dạng',
            'phone.string' => 'Số điện thoại phải là chuỗi',
            'phone.max' => 'Số điện thoại không được quá 20 ký tự',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
            'status.required' => 'Trạng thái là bắt buộc',
            'status.in' => 'Trạng thái không hợp lệ',
            'note.max' => 'Lý do ban không được quá 500 ký tự',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $user = User::findOrFail($id);

            $updateData = [
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'status' => $request->status,
            ];

            // Nếu ban user (status = 0) thì bắt buộc phải có lý do
            if ($request->status == 0 && empty($request->note)) {
                return response()->json([
                    'error' => 'Vui lòng nhập lý do khi khóa tài khoản'
                ], 422);
            }

            // Nếu mở khóa user (status = 1) thì xóa lý do ban
            if ($request->status == 1) {
                $updateData['note'] = null;
            } else {
                $updateData['note'] = $request->note;
            }

            if ($request->filled('password')) {
                $updateData['password'] = bcrypt($request->password);
            }

            $user->update($updateData);

            return response()->json([
                'message' => 'Cập nhật người dùng thành công',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar ? url($user->avatar) : null,
                    'role' => $user->role,
                    'status' => $user->status,
                    'gender' => $user->gender,
                    'dateOfBirth' => $user->dateOfBirth,
                    'note' => $user->note,
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to update user by admin: ' . $e->getMessage());
            return response()->json([
                'error' => 'Có lỗi xảy ra khi cập nhật người dùng. Vui lòng thử lại.'
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ((int) $id === (int) Auth::id()) {
            return response()->json(['error' => 'Không thể tự xoá tài khoản của chính bạn'], 422);
        }

        $user = User::findOrFail($id);

        try {
            DB::transaction(function () use ($user) {
                if ($user->avatar && Storage::exists('public/avatars/' . basename($user->avatar))) {
                    Storage::delete('public/avatars/' . basename($user->avatar));
                }

                $user->delete();
            });

            return response()->json(['message' => 'Đã xoá người dùng thành công'], 200);
        } catch (\Exception $e) {
            Log::error('Delete user failed: ' . $e->getMessage());
            return response()->json(['error' => 'Có lỗi khi xoá người dùng'], 500);
        }
    }
}
