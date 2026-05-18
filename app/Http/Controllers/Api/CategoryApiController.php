<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API REST de Categorías.
 *
 * GET    /api/categories        → listar todas
 * POST   /api/categories        → crear  [admin]
 * PUT    /api/categories/{id}   → actualizar [admin]
 * DELETE /api/categories/{id}   → eliminar   [admin]
 */
class CategoryApiController extends ApiController
{
    /**
     * Listar todas las categorías.
     */
    public function index(): JsonResponse
    {
        $categories = Category::orderBy('name')->get();

        return $this->success($categories, 'Categorías obtenidas correctamente');
    }

    /**
     * Crear una categoría. [admin]
     *
     * @bodyParam name string required Nombre de la categoría.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
        ]);

        $category = Category::create($data);

        return $this->created($category, 'Categoría creada correctamente');
    }

    /**
     * Actualizar una categoría. [admin]
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return $this->notFound('Categoría no encontrada');
        }

        $data = $request->validate([
            'name' => "required|string|max:100|unique:categories,name,{$id}",
        ]);

        $category->update($data);

        return $this->success($category, 'Categoría actualizada correctamente');
    }

    /**
     * Eliminar una categoría. [admin]
     */
    public function destroy(int $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return $this->notFound('Categoría no encontrada');
        }

        $category->delete();

        return $this->success(null, 'Categoría eliminada correctamente');
    }
}
