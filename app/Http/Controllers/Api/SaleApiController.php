<?php

namespace App\Http\Controllers\Api;

use App\Models\Sale;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * API REST de Ventas.
 *
 * GET    /api/sales              → listar ventas (filtros: date, user_id, per_page)
 * GET    /api/sales/{id}         → detalle de una venta con sus ítems
 * DELETE /api/sales/{id}         → eliminar venta y reponer stock [admin]
 * GET    /api/sales/summary      → resumen del día (totales por método de pago)
 */
class SaleApiController extends ApiController
{
    /**
     * Listar ventas con filtros opcionales.
     *
     * @queryParam date     string  Fecha (Y-m-d) para filtrar. Defecto: hoy.
     * @queryParam user_id  integer Filtrar por vendedor.
     * @queryParam per_page integer Registros por página (default 30).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Sale::with(['items.product', 'user'])
            ->orderByDesc('created_at');

        if ($date = $request->get('date')) {
            $query->whereDate('created_at', $date);
        } else {
            $query->whereDate('created_at', now()->toDateString());
        }

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        $perPage = (int) $request->get('per_page', 30);
        $sales   = $query->paginate($perPage);

        return $this->paginated($sales, 'Ventas obtenidas correctamente');
    }

    /**
     * Resumen del día actual: totales de efectivo, virtual y global.
     */
    public function summary(): JsonResponse
    {
        $today = now()->toDateString();

        $totals = Sale::whereDate('created_at', $today)
            ->selectRaw('
                COUNT(*)                      AS total_ventas,
                COALESCE(SUM(total), 0)       AS total_general,
                COALESCE(SUM(payment_cash),0) AS total_efectivo,
                COALESCE(SUM(payment_virtual),0) AS total_virtual
            ')
            ->first();

        return $this->success([
            'fecha'           => $today,
            'total_ventas'    => (int) $totals->total_ventas,
            'total_general'   => (float) $totals->total_general,
            'total_efectivo'  => (float) $totals->total_efectivo,
            'total_virtual'   => (float) $totals->total_virtual,
        ], 'Resumen del día');
    }

    /**
     * Detalle de una venta con todos sus ítems.
     */
    public function show(int $id): JsonResponse
    {
        $sale = Sale::with(['items.product.category', 'user'])->find($id);

        if (!$sale) {
            return $this->notFound('Venta no encontrada');
        }

        return $this->success($sale);
    }

    /**
     * Eliminar una venta y reponer el stock. [admin]
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return $this->forbidden('Solo el administrador puede eliminar ventas');
        }

        $sale = Sale::with('items')->find($id);

        if (!$sale) {
            return $this->notFound('Venta no encontrada');
        }

        DB::transaction(function () use ($sale) {
            foreach ($sale->items as $item) {
                if ($product = Product::find($item->product_id)) {
                    $product->increment('stock_qty', (int) $item->quantity);
                }
            }
            $sale->items()->delete();
            $sale->delete();
        });

        return $this->success(null, 'Venta eliminada y stock repuesto correctamente');
    }
}
