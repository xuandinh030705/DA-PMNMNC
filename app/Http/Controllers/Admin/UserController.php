<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->latest();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        $users = $query->paginate(10)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        $currentAdmin = $request->user();

        // Prevent self-lock and self-demote
        if ($currentAdmin->id === $user->id) {
            if (! $request->boolean('is_active')) {
                return back()->with('error', 'Bạn không thể tự khóa tài khoản quản trị viên của chính mình.');
            }
            if ($request->role !== User::ROLE_ADMIN) {
                return back()->with('error', 'Bạn không thể tự hạ quyền quản trị viên của chính mình.');
            }
        }

        $user->update($request->validated());

        return redirect()->route('admin.users.index')
            ->with('success', "Cập nhật thông tin người dùng '{$user->name}' thành công.");
    }

    public function toggle(Request $request, User $user): RedirectResponse
    {
        $currentAdmin = $request->user();

        if ($currentAdmin->id === $user->id) {
            return back()->with('error', 'Bạn không thể tự khóa tài khoản của chính mình.');
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        $actionText = $user->is_active ? 'kích hoạt' : 'khóa';

        return back()->with('success', "Đã {$actionText} tài khoản của '{$user->name}' thành công.");
    }
}
