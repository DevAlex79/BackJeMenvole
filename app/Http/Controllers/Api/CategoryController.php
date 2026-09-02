<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;

/**
 * Catégories de produits (lecture publique uniquement pour l'instant).
 */
class CategoryController extends Controller
{
    /**
     * Liste toutes les catégories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(Category::orderBy('name')->get(), 200);
    }
}
