<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => User::ROLE_CUSTOMER,
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', 'Đăng ký tài khoản thành công! Chào mừng bạn đến với hệ thống.');
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // Check if account is active before or after attempt
        $user = User::where('email', $request->email)->first();
        if ($user && ! $user->is_active) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ ban quản trị.']);
        }

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            /** @var User $authUser */
            $authUser = Auth::user();

            if ($authUser->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'))->with('success', 'Đăng nhập thành công với quyền Quản trị viên.');
            }

            if ($authUser->isOrganizer()) {
                return redirect()->intended(route('organizer.dashboard'))->with('success', 'Đăng nhập thành công với quyền Ban tổ chức.');
            }

            return redirect()->intended(route('home'))->with('success', 'Đăng nhập thành công.');
        }

        return back()->withInput($request->only('email'))
            ->withErrors(['email' => 'Email hoặc mật khẩu không chính xác.']);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Bạn đã đăng xuất an toàn khỏi hệ thống.');
    }
}
