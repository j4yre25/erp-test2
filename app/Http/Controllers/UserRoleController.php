<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRoleRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserRoleController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        return Inertia::render('master-data/user-management/index', [
            'users' => User::query()
                ->with('role')
                ->orderBy('name')
                ->get()
                ->map(fn (User $user): array => $this->userPayload($user))
                ->values(),
            'roles' => Role::query()->orderBy('role_name')->get(['id', 'role_name']),
        ]);
    }

    public function update(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        $user->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User role updated.')]);

        return to_route('user-roles.index');
    }

    /**
     * @return array<string, mixed>
     */
    protected function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role_id' => $user->role_id,
            'role_name' => $user->role?->role_name,
        ];
    }
}
