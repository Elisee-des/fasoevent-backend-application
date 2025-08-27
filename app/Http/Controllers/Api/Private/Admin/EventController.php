<?php

namespace App\Http\Controllers\Api\Private\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $events = Event::with('city')->get();
            return response()->json([
                'success' => true,
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'price' => 'nullable|numeric|min:0',
            'image' => 'nullable|string', // base64
            'is_active' => 'boolean',
            'city_id' => 'required|uuid|exists:cities,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $validated = $validator->validated();
            
            // Gestion de l'image base64
            if (!empty($validated['image'])) {
                $imageData = $validated['image'];
                $imagePath = $this->storeBase64Image($imageData);
                $validated['image'] = $imagePath;
            }

            $event = Event::create($validated);
            
            // Charger la relation city
            $event->load('city');

            return response()->json([
                'success' => true,
                'message' => 'Événement créé avec succès',
                'data' => $event
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'événement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $event = Event::with('city')->find($id);
            
            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Événement non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'price' => 'nullable|numeric|min:0',
            'image' => 'nullable|string', // base64
            'is_active' => 'boolean',
            'city_id' => 'sometimes|required|uuid|exists:cities,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $event = Event::find($id);
            
            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Événement non trouvé'
                ], 404);
            }

            $validated = $validator->validated();
            
            // Gestion de l'image base64
            if (!empty($validated['image'])) {
                // Supprimer l'ancienne image si elle existe
                if ($event->image) {
                    Storage::delete($event->image);
                }
                
                $imageData = $validated['image'];
                $imagePath = $this->storeBase64Image($imageData);
                $validated['image'] = $imagePath;
            }

            $event->update($validated);
            
            // Recharger les relations
            $event->load('city');

            return response()->json([
                'success' => true,
                'message' => 'Événement mis à jour avec succès',
                'data' => $event
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'événement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $event = Event::find($id);
            
            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Événement non trouvé'
                ], 404);
            }

            // Supprimer l'image si elle existe
            if ($event->image) {
                Storage::delete($event->image);
            }

            $event->delete();

            return response()->json([
                'success' => true,
                'message' => 'Événement supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'événement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle the active status of the event.
     */
    public function toggleStatus(string $id): JsonResponse
    {
        try {
            $event = Event::find($id);
            
            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Événement non trouvé'
                ], 404);
            }

            $event->update(['is_active' => !$event->is_active]);
            $event->load('city');

            return response()->json([
                'success' => true,
                'message' => 'Statut de l\'événement modifié avec succès',
                'data' => $event
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification du statut',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store base64 image and return the path.
     */
    private function storeBase64Image(string $base64Data): string
    {
        // Vérifier si c'est une image base64 valide
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
            $data = substr($base64Data, strpos($base64Data, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, gif
            
            if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                throw new \Exception('Type d\'image non supporté');
            }
            
            $data = base64_decode($data);
            
            if ($data === false) {
                throw new \Exception('Données base64 invalides');
            }
        } else {
            throw new \Exception('Format base64 invalide');
        }
        
        // Générer un nom de fichier unique
        $filename = 'events/' . Str::uuid() . '.' . $type;
        
        // Stocker l'image
        Storage::put($filename, $data);
        
        return $filename;
    }
}