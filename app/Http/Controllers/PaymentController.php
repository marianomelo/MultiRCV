<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Plan;
use App\Services\FlowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PaymentController extends Controller
{
    protected $flowService;

    public function __construct(FlowService $flowService)
    {
        $this->flowService = $flowService;
    }

    /**
     * Show billing form
     */
    public function create(Request $request, Plan $plan)
    {
        $user = $request->user();

        // Validate user doesn't already have this plan
        if ($user->plan_id === $plan->id) {
            return redirect()->route('plans.index')
                ->with('error', 'Ya tienes este plan activo.');
        }

        // Check if user has pending payment for this plan
        $pendingPayment = Payment::where('user_id', $user->id)
            ->where('plan_id', $plan->id)
            ->whereIn('status', ['pending'])
            ->where('created_at', '>', now()->subHours(2))
            ->first();

        if ($pendingPayment) {
            return redirect()->route('plans.index')
                ->with('error', 'Ya tienes un pago pendiente para este plan. Por favor completa o cancela el pago anterior.');
        }

        return Inertia::render('Payment/BillingForm', [
            'plan' => $plan,
        ]);
    }

    /**
     * Process payment with billing info and redirect to Flow
     */
    public function process(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'billing_rut' => 'required|string|max:12',
            'billing_name' => 'required|string|max:255',
            'billing_type' => 'required|in:empresa,persona_natural',
            'document_type' => 'required|in:factura,boleta',
            'billing_address' => 'required|string|max:500',
        ]);

        $user = $request->user();

        // Re-validate user doesn't already have this plan
        if ($user->plan_id === $plan->id) {
            return redirect()->route('plans.index')
                ->with('error', 'Ya tienes este plan activo.');
        }

        // Check if plan is free (shouldn't go through payment)
        if ($plan->price == 0) {
            return redirect()->route('plans.index')
                ->with('error', 'Este plan es gratuito, no requiere pago.');
        }

        // Check company limit
        $currentCompanyCount = $user->companies()->count();
        if ($currentCompanyCount > $plan->company_limit) {
            return redirect()->route('plans.index')
                ->with('error', "No puedes cambiar a este plan. Tienes {$currentCompanyCount} empresas pero este plan solo permite {$plan->company_limit}.");
        }

        // Check for recent pending payments
        $pendingPayment = Payment::where('user_id', $user->id)
            ->where('plan_id', $plan->id)
            ->whereIn('status', ['pending'])
            ->where('created_at', '>', now()->subHours(2))
            ->first();

        if ($pendingPayment) {
            return redirect()->route('plans.index')
                ->with('error', 'Ya tienes un pago pendiente para este plan.');
        }

        // Generate unique commerce order
        $commerceOrder = 'PLAN-' . $plan->id . '-' . $user->id . '-' . time();

        // Create payment record
        $payment = Payment::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'commerce_order' => $commerceOrder,
            'amount' => $plan->price,
            'description' => 'Plan ' . $plan->name . ' - MultiRCV',
            'payer_email' => $user->email,
            'billing_rut' => $validated['billing_rut'],
            'billing_name' => $validated['billing_name'],
            'billing_type' => $validated['billing_type'],
            'document_type' => $validated['document_type'],
            'billing_address' => $validated['billing_address'],
            'status' => 'pending',
        ]);

        try {
            // Create payment in Flow
            $flowResponse = $this->flowService->createPayment([
                'commerce_order' => $commerceOrder,
                'subject' => 'Plan ' . $plan->name,
                'amount' => $plan->price,
                'email' => $user->email,
                'optional' => json_encode([
                    'payment_id' => $payment->id,
                    'user_id' => $user->id,
                ]),
            ]);

            // Update payment with Flow data
            $payment->update([
                'flow_token' => $flowResponse['token'],
                'flow_order' => $flowResponse['flowOrder'] ?? null,
            ]);

            // Redirect to Flow payment page
            return Inertia::location($flowResponse['url'] . '?token=' . $flowResponse['token']);

        } catch (\Exception $e) {
            Log::error('Flow payment creation failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            $payment->update(['status' => 'error']);

            // Extract clean error message from Flow response
            $errorMessage = $e->getMessage();
            if (preg_match('/"message":"([^"]+)"/', $errorMessage, $matches)) {
                $errorMessage = $matches[1];
            }

            return redirect()->route('plans.index')
                ->with('error', 'Error al crear el pago: ' . $errorMessage);
        }
    }

    /**
     * Flow confirmation callback (webhook)
     */
    public function confirm(Request $request)
    {
        // Log webhook receipt immediately
        Log::info('Flow webhook received', [
            'params' => $request->all(),
            'ip' => $request->ip(),
        ]);

        try {
            $params = $request->all();

            $token = $params['token'] ?? null;

            if (!$token) {
                Log::error('No token in Flow callback', ['params' => $params]);
                return response('OK', 200);
            }

            // Validate Flow signature (only if signature is present)
            if (isset($params['s'])) {
                if (!$this->flowService->validateCallback($params)) {
                    Log::error('Invalid Flow signature', ['params' => $params]);
                    // ALWAYS respond 200 to Flow, even with errors
                    return response('OK', 200);
                }
            } else {
                Log::warning('Flow webhook without signature', [
                    'token' => $token,
                    'params' => $params
                ]);
            }

            // Get payment status from Flow
            $flowStatus = $this->flowService->getPaymentStatus($token);

            // Find payment by token
            $payment = Payment::where('flow_token', $token)->first();

            if (!$payment) {
                Log::error('Payment not found for token', ['token' => $token]);
                return response('OK', 200);
            }

            // Update payment with Flow response
            $payment->update([
                'flow_order' => $flowStatus['flowOrder'] ?? null,
                'payment_data' => $flowStatus,
            ]);

            // Check payment status (ONLY process if status = 2)
            if (isset($flowStatus['status']) && $flowStatus['status'] == 2) {

                // Verificar idempotencia (no procesar si ya está confirmado)
                if ($payment->status !== 'paid') {

                    // Verificar que el monto coincida
                    if ($payment->amount == $flowStatus['amount']) {

                        // Status 2 = Payment successful
                        $payment->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                            'confirmed_at' => now(),
                        ]);

                        // Update user's plan
                        $payment->user->update([
                            'plan_id' => $payment->plan_id,
                        ]);

                        Log::info('Payment confirmed successfully', [
                            'payment_id' => $payment->id,
                            'user_id' => $payment->user_id,
                            'plan_id' => $payment->plan_id,
                            'commerce_order' => $flowStatus['commerceOrder'] ?? null,
                            'flow_order' => $flowStatus['flowOrder'] ?? null,
                            'amount' => $flowStatus['amount'] ?? null,
                        ]);

                    } else {
                        Log::warning('Amount mismatch', [
                            'expected' => $payment->amount,
                            'received' => $flowStatus['amount'] ?? null,
                        ]);
                    }

                } else {
                    Log::info('Payment already processed (idempotency)', [
                        'payment_id' => $payment->id,
                    ]);
                }

            } else {
                // Payment failed or rejected
                $payment->update([
                    'status' => 'rejected',
                ]);

                Log::warning('Payment rejected', [
                    'payment_id' => $payment->id,
                    'flow_status' => $flowStatus,
                ]);
            }

            // ALWAYS respond OK with 200
            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error('Flow confirmation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'params' => $request->all(),
            ]);

            // Even with error, respond 200 to avoid retries
            return response('OK', 200);
        }
    }

    /**
     * Flow return callback (user redirect)
     */
    public function return(Request $request)
    {
        $token = $request->input('token');

        try {
            // Find payment by token
            $payment = Payment::where('flow_token', $token)->first();

            if (!$payment) {
                return redirect()->route('login')
                    ->with('error', 'Pago no encontrado');
            }

            // Log in the user if not authenticated
            if (!auth()->check()) {
                auth()->login($payment->user);
            }

            // Check payment status
            if ($payment->status === 'paid') {
                return redirect()->route('plans.index')
                    ->with('success', '¡Pago exitoso! Tu plan ha sido actualizado.');
            } elseif ($payment->status === 'rejected') {
                return redirect()->route('plans.index')
                    ->with('error', 'El pago fue rechazado. Por favor intenta nuevamente.');
            } else {
                // Payment is still pending (confirmation webhook may not have been called yet)
                return redirect()->route('plans.index')
                    ->with('warning', 'Tu pago está siendo procesado. Te notificaremos cuando se confirme.');
            }

        } catch (\Exception $e) {
            Log::error('Flow return error', [
                'error' => $e->getMessage(),
                'token' => $token,
            ]);

            return redirect()->route('login')
                ->with('error', 'Error al procesar el pago');
        }
    }
}
