<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB; // Added DB facade import

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->canManageUsers()) {
                abort(403, '权限不足');
            }
            return $next($request);
        });
    }

    public function index()
    {
        // 使用缓存优化查询
        $cacheKey = 'users_stats_' . auth()->id();
        $stats = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () {
            return [
                'totalUsers' => User::count(),
                'activeUsers' => User::where('is_active', true)->count(),
                'adminUsers' => User::whereHas('role', function ($query) {
                    $query->whereIn('code', ['admin', 'super_admin']);
                })->count(),
                'newUsersThisMonth' => User::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ];
        });

        // 使用 Eloquent 模型查询，但优化关系加载
        $users = User::with(['role:id,name,code', 'stores:id,name'])
            ->paginate(10);

        $stores = \App\Models\Store::where('is_active', true)->get();
        $roles = \App\Models\Role::all();
        
        return view('users.index', compact('users', 'stores', 'roles'))
            ->with([
                'totalUsers' => $stats['totalUsers'],
                'activeUsers' => $stats['activeUsers'],
                'adminUsers' => $stats['adminUsers'],
                'newUsersThisMonth' => $stats['newUsersThisMonth']
            ]);
    }

    public function create()
    {
        $roles = \App\Models\Role::all();
        $stores = \App\Models\Store::where('is_active', true)->get();
        return view('users.create', compact('roles', 'stores'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|unique:users',
            'password' => 'required',
            'real_name' => 'required',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
            'store_ids' => 'array',
            'store_ids.*' => 'exists:stores,id',
        ]);
        
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        
        // 分配仓库
        if ($request->has('store_ids')) {
            $user->stores()->attach($request->store_ids);
        }
        
        // 清除用户统计缓存
        $this->clearUserStatsCache();
        
        return redirect()->route('users.index')->with('success', '用户创建成功');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        try {
            // 检查当前用户权限
            $currentUser = auth()->user();
            \Log::info('User edit access', [
                'current_user_id' => $currentUser->id,
                'current_user_role' => $currentUser->role->code ?? 'no_role',
                'can_manage_users' => $currentUser->canManageUsers(),
                'target_user_id' => $user->id
            ]);
            
            $roles = \App\Models\Role::all();
            $stores = \App\Models\Store::where('is_active', true)->get();
            $userStores = $user->stores->pluck('id')->toArray();
            return view('users.edit', compact('user', 'roles', 'stores', 'userStores'))
                ->with('userStores', $userStores); // 明确覆盖全局变量
        } catch (\Exception $e) {
            \Log::error('User edit error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', '加载用户编辑页面失败：' . $e->getMessage());
        }
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'username' => 'required|unique:users,username,' . $user->id,
            'real_name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
            'store_ids' => 'array',
            'store_ids.*' => 'exists:stores,id',
        ]);
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        
        $user->update($data);
        
        // 更新仓库分配
        $user->stores()->sync($request->store_ids ?? []);
        
        // 清除用户统计缓存
        $this->clearUserStatsCache();
        
        return redirect()->route('users.index')->with('success', '用户更新成功');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', '不能删除当前登录用户！');
        }

        $user->delete();

        // 清除用户统计缓存
        $this->clearUserStatsCache();

        return redirect()->route('users.index')
            ->with('success', '用户删除成功！');
    }

    /**
     * 清除用户统计缓存
     */
    private function clearUserStatsCache()
    {
        $cacheKey = 'users_stats_' . auth()->id();
        \Illuminate\Support\Facades\Cache::forget($cacheKey);
    }
} 