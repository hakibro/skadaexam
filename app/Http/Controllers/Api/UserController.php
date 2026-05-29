<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with(['roles:id,name,guard_name', 'guru:id,user_id,nama,nip,email']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', fn($q) => $q->where('name', $request->role));
        }

        $perPage = min((int) $request->get('per_page', 50), 200);
        $users = $query->orderBy('name')->paginate($perPage)->appends($request->query());

        $users->getCollection()->transform(fn($user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->map(fn($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
            ])->values(),
            'guru' => $user->guru ? [
                'id' => $user->guru->id,
                'nama' => $user->guru->nama,
                'nip' => $user->guru->nip,
                'email' => $user->guru->email,
            ] : null,
        ]);

        return response()->json([
            'success' => true,
            'meta' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ],
            'data' => $users->items(),
        ]);
    }
}
