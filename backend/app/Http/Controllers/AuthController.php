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

        Mail::to($user->email)->queue(new WelcomeEmail($user));

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
                Mail::to($user->email)->queue(new WelcomeEmail($user));
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

    public function createUserByAdmin(Request $request)
    {
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'master_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|min:6',
            'role' => 'required|in:user,admin,master_admin',
            'gender' => 'nullable|string|max:10',
            'dateOfBirth' => 'nullable|date',
        ], [
            'username.required' => 'Tên người dùng là bắt buộc',
            'username.string' => 'Tên người dùng phải là chuỗi',
            'username.max' => 'Tên người dùng không được quá 255 ký tự',
            'email.required' => 'Email là bắt buộc',
            'email.email' => 'Email không đúng định dạng',
            'email.unique' => 'Email này đã tồn tại trong hệ thống',
            'phone.string' => 'Số điện thoại phải là chuỗi',
            'phone.max' => 'Số điện thoại không được quá 20 ký tự',
            'password.required' => 'Mật khẩu là bắt buộc',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
            'role.required' => 'Vai trò là bắt buộc',
            'role.in' => 'Vai trò không hợp lệ',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Kiểm tra quyền tạo role
        $currentUser = Auth::user();
        if ($request->role === 'master_admin' && $currentUser->role !== 'master_admin') {
            return response()->json([
                'error' => 'Chỉ Master Admin mới có quyền tạo tài khoản Master Admin'
            ], 403);
        }

        if ($request->role === 'admin' && $currentUser->role === 'admin') {
            return response()->json([
                'error' => 'Admin thường không thể tạo tài khoản Admin khác'
            ], 403);
        }

        try {
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'phone' => $request->phone,
                'role' => $request->role,
                'gender' => $request->gender,
                'dateOfBirth' => $request->dateOfBirth,
                'status' => 1, // Mặc định là hoạt động
                'ip_user' => $request->ip(),
            ]);

            // Gửi email chào mừng
            Mail::to($user->email)->queue(new WelcomeEmail($user));

            return response()->json([
                'message' => 'Tạo người dùng thành công',
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
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create user by admin: ' . $e->getMessage());
            return response()->json([
                'error' => 'Có lỗi xảy ra khi tạo người dùng. Vui lòng thử lại.'
            ], 500);
        }
    }

    public function listUser(Request $request)
    {
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'master_admin') {
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

            Mail::to($user->email)->queue(new OtpEmail($otp, $user));

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
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'master_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|min:6',
            'role' => 'required|in:user,admin,master_admin',
            'status' => 'required|in:0,1',
            'note' => 'nullable|string|max:500',
            'gender' => 'nullable|string|max:10',
            'dateOfBirth' => 'nullable|date',
        ], [
            'username.required' => 'Tên người dùng là bắt buộc',
            'username.string' => 'Tên người dùng phải là chuỗi',
            'username.max' => 'Tên người dùng không được quá 255 ký tự',
            'email.required' => 'Email là bắt buộc',
            'email.email' => 'Email không đúng định dạng',
            'phone.string' => 'Số điện thoại phải là chuỗi',
            'phone.max' => 'Số điện thoại không được quá 20 ký tự',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
            'role.required' => 'Vai trò là bắt buộc',
            'role.in' => 'Vai trò không hợp lệ',
            'status.required' => 'Trạng thái là bắt buộc',
            'status.in' => 'Trạng thái không hợp lệ',
            'note.max' => 'Lý do ban không được quá 500 ký tự',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $user = User::findOrFail($id);
            $currentUser = Auth::user();

            // Kiểm tra quyền thay đổi role
            if ($request->role === 'master_admin' && $currentUser->role !== 'master_admin') {
                return response()->json([
                    'error' => 'Chỉ Master Admin mới có quyền thay đổi role thành Master Admin'
                ], 403);
            }

            if ($request->role === 'admin' && $currentUser->role === 'admin') {
                return response()->json([
                    'error' => 'Admin thường không thể thay đổi role thành Admin'
                ], 403);
            }

            // Kiểm tra không cho phép thay đổi role của chính mình
            if ((int) $id === (int) Auth::id()) {
                if ($request->role !== $currentUser->role) {
                    return response()->json([
                        'error' => 'Bạn không thể thay đổi vai trò của chính bản thân'
                    ], 422);
                }
            }

            // Kiểm tra không cho phép thay đổi role của master admin (trừ khi là master admin khác)
            if ($user->role === 'master_admin' && $currentUser->role !== 'master_admin') {
                return response()->json([
                    'error' => 'Không thể thay đổi thông tin của Master Admin'
                ], 403);
            }

            $updateData = [
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
                'status' => $request->status,
                'gender' => $request->gender,
                'dateOfBirth' => $request->dateOfBirth,
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
        try {
            // Kiểm tra quyền admin
            if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'master_admin') {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $currentUser = Auth::user();

            // Kiểm tra không cho phép tự xóa tài khoản của chính mình
            if ((int) $id === (int) Auth::id()) {
                return response()->json(['error' => 'Bạn không thể xóa chính bản thân'], 422);
            }

            // Tìm user để xóa
            $userToDelete = User::findOrFail($id);

            Log::info('Delete user attempt', [
                'current_user_id' => $currentUser->id,
                'current_user_role' => $currentUser->role,
                'user_to_delete_id' => $userToDelete->id,
                'user_to_delete_role' => $userToDelete->role
            ]);

            // Kiểm tra quyền xóa Master Admin
            if ($userToDelete->role === 'master_admin') {
                if ($currentUser->role !== 'master_admin') {
                    return response()->json(['error' => 'Chỉ Master Admin mới có quyền xóa Master Admin khác'], 403);
                }

                // Kiểm tra không cho phép xóa master admin cuối cùng
                $masterAdminCount = User::where('role', 'master_admin')->count();
                if ($masterAdminCount <= 1) {
                    return response()->json(['error' => 'Không thể xóa Master Admin cuối cùng trong hệ thống'], 422);
                }
            }

            // Kiểm tra quyền xóa Admin (chỉ Master Admin mới có thể xóa Admin)
            if ($userToDelete->role === 'admin' && $currentUser->role === 'admin') {
                return response()->json(['error' => 'Admin thường không thể xóa Admin khác'], 403);
            }

            // Thực hiện xóa user
            DB::transaction(function () use ($userToDelete) {
                // Xóa avatar nếu có
                if ($userToDelete->avatar && Storage::exists('public/avatars/' . basename($userToDelete->avatar))) {
                    Storage::delete('public/avatars/' . basename($userToDelete->avatar));
                }

                // Xóa relationships trước khi xóa user
                $userToDelete->stockMovements()->delete();
                $userToDelete->coupons()->detach();
                $userToDelete->addresses()->delete();
                $userToDelete->cartItems()->delete();
                $userToDelete->favoriteProducts()->delete();
                $userToDelete->productReviews()->delete();

                // Đối với orders, chỉ xóa nếu không có dữ liệu quan trọng
                // Hoặc có thể set user_id = null thay vì xóa
                $userToDelete->orders()->update(['user_id' => null]);

                // Xóa user
                $userToDelete->delete();
            });

            Log::info('User deleted successfully', [
                'deleted_user_id' => $id,
                'deleted_by' => $currentUser->id
            ]);

            return response()->json(['message' => 'Đã xoá người dùng thành công'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('User not found for deletion: ' . $e->getMessage());
            return response()->json(['error' => 'Không tìm thấy người dùng'], 404);
        } catch (\Exception $e) {
            Log::error('Delete user failed: ' . $e->getMessage(), [
                'user_id' => $id,
                'current_user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Có lỗi khi xoá người dùng: ' . $e->getMessage()], 500);
        }
    }

    // Test method để debug
    public function testDelete(Request $request, $id)
    {
        try {
            Log::info('Test delete method called', [
                'user_id' => $id,
                'current_user' => Auth::user() ? Auth::user()->id : 'not authenticated',
                'current_user_role' => Auth::user() ? Auth::user()->role : 'not authenticated'
            ]);

            $user = User::find($id);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            return response()->json([
                'message' => 'Test successful',
                'user_found' => true,
                'user_id' => $user->id,
                'user_role' => $user->role,
                'current_user_id' => Auth::id(),
                'current_user_role' => Auth::user()->role
            ]);
        } catch (\Exception $e) {
            Log::error('Test delete failed: ' . $e->getMessage());
            return response()->json(['error' => 'Test failed: ' . $e->getMessage()], 500);
        }
    }

    // Simple delete method for testing
    public function simpleDelete(Request $request, $id)
    {
        try {
            // Kiểm tra quyền admin
            if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'master_admin') {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Kiểm tra không cho phép tự xóa
            if ((int) $id === (int) Auth::id()) {
                return response()->json(['error' => 'Bạn không thể xóa chính bản thân'], 422);
            }

            $user = User::findOrFail($id);

            // Xóa user đơn giản
            $user->delete();

            return response()->json(['message' => 'Đã xoá người dùng thành công'], 200);
        } catch (\Exception $e) {
            Log::error('Simple delete failed: ' . $e->getMessage());
            return response()->json(['error' => 'Có lỗi khi xoá người dùng: ' . $e->getMessage()], 500);
        }
    }
}
