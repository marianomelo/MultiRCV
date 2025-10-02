<?php

namespace App\Http\Controllers;

use App\Mail\TicketSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TicketController extends Controller
{
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        $user = $request->user();

        // Send ticket email to support
        Mail::to('soporte@tecnologicachile.cl')->send(
            new TicketSubmitted($user, $validated['subject'], $validated['message'])
        );

        return back()->with('success', 'Tu ticket ha sido enviado exitosamente. Te responderemos a la brevedad.');
    }
}
