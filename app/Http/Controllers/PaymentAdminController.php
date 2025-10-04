<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Inertia\Inertia;

class PaymentAdminController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['user', 'plan'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Payments/Index', [
            'payments' => $payments,
        ]);
    }
}
