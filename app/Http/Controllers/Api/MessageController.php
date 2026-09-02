<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MessageController extends Controller
{
    /**
     * Enregistre un message du formulaire de contact et prévient l'admin.
     *
     * Les données sont stockées telles quelles (brutes). L'échappement
     * contre le XSS se fait à l'AFFICHAGE (Blade/JSON), jamais au
     * stockage — sinon on enregistre définitivement des « &amp; », etc.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'prenom'  => 'required|string|max:35',
            'email'   => 'required|email|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $message = Message::create($validated);

        try {
            Mail::raw(
                "Nouveau message de {$message->prenom} ({$message->email}) :\n\n{$message->message}",
                fn ($mail) => $mail->to(config('mail.admin_address'))
                    ->replyTo($message->email)
                    ->subject('Nouveau message de contact')
            );
        } catch (\Throwable $e) {
            Log::warning('Email de contact non envoyé', ['error' => $e->getMessage()]);
        }

        return response()->json(['message' => 'Message envoyé avec succès !'], 201);
    }
}
