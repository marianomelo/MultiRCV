<?php

namespace App\Http\Controllers;

use App\Mail\UserApproved;
use App\Models\ActivityLog;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('plan')->get();
        $plans = Plan::orderBy('price')->get();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'plans' => $plans,
        ]);
    }

    public function create()
    {
        return Inertia::render('Users/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:user,super_admin',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        ActivityLog::log('create', 'User', $user->id, "Usuario '{$user->name}' creado");

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    public function edit(User $user)
    {
        return Inertia::render('Users/Edit', [
            'user' => $user
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:user,super_admin',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        ActivityLog::log('update', 'User', $user->id, "Usuario '{$user->name}' actualizado");

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $user)
    {
        $userName = $user->name;
        $userId = $user->id;

        $user->delete();

        ActivityLog::log('delete', 'User', $userId, "Usuario '{$userName}' eliminado");

        return redirect()->route('users.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }

    public function approve(User $user)
    {
        if ($user->is_approved) {
            return redirect()->route('users.index')
                ->with('error', 'Este usuario ya está aprobado.');
        }

        $user->update([
            'is_approved' => true,
            'approved_at' => now(),
        ]);

        ActivityLog::log('update', 'User', $user->id, "Usuario '{$user->name}' aprobado");

        // Send approval email
        Mail::to($user->email)->send(new UserApproved($user));

        return redirect()->route('users.index')
            ->with('success', 'Usuario aprobado exitosamente. Se ha enviado un correo de notificación.');
    }

    public function changePlan(Request $request, User $user)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);

        // Check if user has more companies than new plan allows
        $currentCompanyCount = $user->companies()->count();
        if ($currentCompanyCount > $plan->company_limit) {
            return redirect()->back()
                ->with('error', "No se puede asignar este plan. El usuario tiene {$currentCompanyCount} empresas pero este plan solo permite {$plan->company_limit}.");
        }

        $oldPlan = $user->plan ? $user->plan->name : 'Sin plan';
        $user->plan_id = $validated['plan_id'];
        $user->save();

        ActivityLog::log('update', 'User', $user->id, "Plan del usuario '{$user->name}' cambiado de '{$oldPlan}' a '{$plan->name}'");

        return redirect()->route('users.index')
            ->with('success', "Plan del usuario actualizado a {$plan->name} exitosamente.");
    }
}
