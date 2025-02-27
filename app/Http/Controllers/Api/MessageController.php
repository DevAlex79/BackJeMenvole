<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use Illuminate\Support\Facades\Mail;

class MessageController extends Controller {
    public function store(Request $request) {
        $validated = $request->validate([
            'prenom' => 'required|string|max:35',
            'email' => 'required|email|max:50',
            'message' => 'required|string',
        ]);

        $message = Message::create($validated);

        // Envoyer un email à l'admin
        Mail::raw("Nouveau message de {$message->prenom} ({$message->email}):\n\n{$message->message}", function ($mail) {
            $mail->to('admin@example.com')->subject('Nouveau message de contact');
        });

        return response()->json(['message' => 'Message envoyé avec succès !'], 201);
    }
}
