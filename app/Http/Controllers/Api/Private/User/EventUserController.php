<?php

namespace App\Http\Controllers\Api\Private\User;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class EventUserController extends Controller
{
    /**
     * Display a listing of the user's event reservations.
     */
    public function index(): JsonResponse
    {
        try {
            /** @var \App\Models\User $currentUser */
            $user = Auth::user();
            
            $reservations = $user->events()
                ->with('city')
                ->orderBy('event_user.created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Réservations récupérées avec succès',
                'data' => $reservations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des réservations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new event reservation for the authenticated user.
     */
    public function store(Request $request, string $eventId): JsonResponse
    {
        try {
            /** @var \App\Models\User $currentUser */
            $user = Auth::user();
            $event = Event::where('is_active', true)->find($eventId);

            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Événement non trouvé ou non actif'
                ], 404);
            }

            // Vérifier si l'utilisateur est déjà inscrit
            if ($user->events()->where('event_id', $eventId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous êtes déjà inscrit à cet événement'
                ], 409);
            }

            // Ajouter la réservation
            $user->events()->attach($eventId);

            // Recharger l'événement avec les relations
            $event->load('city');

            return response()->json([
                'success' => true,
                'message' => 'Réservation effectuée avec succès',
                'data' => $event
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réservation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel an event reservation for the authenticated user.
     */
    public function destroy(string $eventId): JsonResponse
    {
        try {
            $user = Auth::user();
            $event = Event::find($eventId);

            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Événement non trouvé'
                ], 404);
            }

            // Vérifier si l'utilisateur est inscrit
            if (!$user->events()->where('event_id', $eventId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas inscrit à cet événement'
                ], 404);
            }

            // Supprimer la réservation
            $user->events()->detach($eventId);

            return response()->json([
                'success' => true,
                'message' => 'Réservation annulée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation de la réservation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if the authenticated user is registered to an event.
     */
    public function checkRegistration(string $eventId): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $isRegistered = $user->events()
                ->where('event_id', $eventId)
                ->exists();

            return response()->json([
                'success' => true,
                'message' => 'Statut de réservation vérifié',
                'data' => [
                    'is_registered' => $isRegistered
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification de la réservation',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}