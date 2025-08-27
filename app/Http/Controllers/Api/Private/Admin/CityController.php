<?php

namespace App\Http\Controllers\Api\Private\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $cities = City::all();
            return response()->json([
                'success' => true,
                'data' => $cities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des villes',
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
            'name' => 'required|string|max:255|unique:cities,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $city = City::create($validator->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Ville créée avec succès',
                'data' => $city
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la ville',
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
            $city = City::find($id);
            
            if (!$city) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ville non trouvée'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $city
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la ville',
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
            'name' => 'required|string|max:255|unique:cities,name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $city = City::find($id);
            
            if (!$city) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ville non trouvée'
                ], 404);
            }

            $city->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Ville mise à jour avec succès',
                'data' => $city
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la ville',
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
            $city = City::find($id);
            
            if (!$city) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ville non trouvée'
                ], 404);
            }

            $city->delete();

            return response()->json([
                'success' => true,
                'message' => 'Ville supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la ville',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}