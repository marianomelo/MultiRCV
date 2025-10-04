<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $plans = Plan::orderBy('price')->get();
        $user = $request->user()->load('plan');

        return Inertia::render('Plans/Index', [
            'plans' => $plans,
            'currentPlan' => $user->plan,
        ]);
    }

    public function changePlan(Request $request, Plan $plan)
    {
        $user = $request->user();

        // Check if user is downgrading and has more companies than new plan allows
        $currentCompanyCount = $user->companies()->count();
        if ($currentCompanyCount > $plan->company_limit) {
            return redirect()->back()
                ->with('error', "No puedes cambiar a este plan. Tienes {$currentCompanyCount} empresas pero este plan solo permite {$plan->company_limit}. Por favor elimina algunas empresas primero.");
        }

        // Only handle free plans here (paid plans go through payment flow)
        if ($plan->price != 0) {
            return redirect()->back()
                ->with('error', "Este plan requiere pago. Por favor usa el botón de pago.");
        }

        $user->plan_id = $plan->id;
        $user->save();

        return redirect()->route('plans.index')
            ->with('success', "Plan actualizado a {$plan->name} exitosamente.");
    }
}
