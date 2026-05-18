<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API REST de Productos.
 *
 * GET    /api/products           → listar (con filtros search, category, stock)
 * POST   /api/products           → crear  [admin]
 * GET    /api/products/{id}      → detalle
 * PUT    /api/products/{id}      → actualizar [admin]
 * DELETE /api/products/{id}      → eliminar   [admin]
 * GET    /api/products/low-stock → productos con stock crítico
 */
class ProductApiController extends ApiController
{
    /**
     * Listar productos con filtros opcionales.
     *
     * @queryParam search    string  Buscar por descripción o código de barras.
     * @queryParam category  integer Filtrar por ID de categoría.
     * @queryParam stock     integer Mostrar productos con stock <= valor.
     * @queryParam per_page  integer Registros por página (default 20).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category')
            ->where(function ($q) {
                $q->where('barcode', '!=', 'RECHARGE')->orWhereNull('barcode');
            })
            ->orderBy('description');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        if ($categoryId = $request->get('category')) {
            $query->where('category_id', $categoryId);
        }

        if (!is_null($stock = $request->get('stock'))) {
            $query->where('stock_qty', '<=', (int) $stock);
        }

        $perPage = (int) $request->get('per_page', 20);
        $products = $query->paginate($perPage);

        return $this->paginated($products, 'Productos obtenidos correctamente');
    }

    /**
     * Productos con stock crítico (0 o ≤ 2 unidades).
     */
    public function lowStock(): JsonResponse
    {
        $outOfStock = Product::with('category')
            ->where('stock_qty', 0)
            ->where(fn($q) => $q->where('barcode', '!=', 'RECHARGE')->orWhereNull('barcode'))
            ->orderBy('description')
            ->get();

        $lowStock = Product::with('category')
            ->whereBetween('stock_qty', [1, 2])
            ->where(fn($q) => $q->where('barcode', '!=', 'RECHARGE')->orWhereNull('barcode'))
            ->orderBy('stock_qty')
            ->get();

        return $this->success([
            'out_of_stock' => $outOfStock,
            'low_stock'    => $lowStock,
        ], 'Reporte de stock crítico');
    }

    /**
     * Crear un producto nuevo. [admin]
     *
     * @bodyParam category_id integer required ID de categoría.
     * @bodyParam description string  required Descripción del producto.
     * @bodyParam stock_qty   integer required Cantidad inicial en stock.
     * @bodyParam unit_cost   numeric required Costo unitario.
     * @bodyParam sale_price  numeric required Precio de venta.
     * @bodyParam barcode     string          Código de barras (opcional).
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string|max:255',
            'stock_qty'   => 'required|integer|min:0',
            'unit_cost'   => 'required|numeric|min:0',
            'sale_price'  => 'required|numeric|min:0',
            'barcode'     => 'nullable|string|max:100',
        ]);

        $product = Product::create($data);
        $product->load('category');

        return $this->created($product, 'Producto creado correctamente');
    }

    /**
     * Detalle de un producto.
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::with('category')->find($id);

        if (!$product) {
            return $this->notFound('Producto no encontrado');
        }

        return $this->success($product);
    }

    /**
     * Actualizar un producto. [admin]
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->notFound('Producto no encontrado');
        }

        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string|max:255',
            'stock_qty'   => 'required|integer|min:0',
            'unit_cost'   => 'required|numeric|min:0',
            'sale_price'  => 'required|numeric|min:0',
            'barcode'     => 'nullable|string|max:100',
        ]);

        $product->update($data);
        $product->load('category');

        return $this->success($product, 'Producto actualizado correctamente');
    }

    /**
     * Eliminar un producto. [admin]
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->notFound('Producto no encontrado');
        }

        $product->saleItems()->delete();
        $product->delete();

        return $this->success(null, 'Producto eliminado correctamente');
    }
}
