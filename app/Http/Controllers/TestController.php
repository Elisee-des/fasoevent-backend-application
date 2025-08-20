<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function hello()
    {
        return response()->json([
            'message' => 'Hello World, tout marche très bien !',
            'status' => 'success'
        ]);
    }

    /**
     * Retourne le texte reçu en paramètre
     */
    public function echo(Request $request)
    {
        $text = $request->input('text', ''); // Récupère le texte ou une chaîne vide par défaut
        return response()->json([
            'message' => 'Vous avez envoyé ce texte au serveur : ' . $text,
            'original_text' => $text,
            'status' => 'success'
        ]);
    }

    // Retourne le text contenu dans l'url
    public function echoUrl($text)
    {
        return response()->json([
            'message' => 'Texte reçu via URL : ' . $text,
            'status' => 'success'
        ]);
    }
}
