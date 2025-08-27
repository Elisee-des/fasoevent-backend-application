<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EventPublicController extends Controller
{
    /**
     * Display a listing of active events with their cities.
     * Ordered by creation date descending.
     */
    public function index(): JsonResponse
    {
        try {
            $events = Event::with('city')
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Liste des événements actifs récupérée avec succès',
                'data' => $events
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des événements',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified event with all details and city information.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $event = Event::with('city')
                ->where('is_active', true)
                ->find($id);

            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Événement non trouvé ou non actif'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Événement récupéré avec succès',
                'data' => $event
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'événement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Optional: Get events by city
     */
    public function byCity(string $cityId): JsonResponse
    {
        try {
            // Vérifier que la ville existe
            $city = City::find($cityId);
            if (!$city) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ville non trouvée'
                ], 404);
            }

            $events = Event::with('city')
                ->where('is_active', true)
                ->where('city_id', $cityId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Événements de la ville récupérés avec succès',
                'data' => $events,
                'city' => $city
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des événements par ville',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Optional: Get upcoming events
     */
    public function upcoming(): JsonResponse
    {
        try {
            $events = Event::with('city')
                ->where('is_active', true)
                ->where('start_date', '>=', now()->toDateString())
                ->orderBy('start_date', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Événements à venir récupérés avec succès',
                'data' => $events
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des événements à venir',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}