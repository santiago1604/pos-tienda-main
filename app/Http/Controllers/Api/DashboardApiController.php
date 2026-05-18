<?php

namespace App\Http\Controllers\Api;

use App\Models\{Sale, CashSession, CashMovement, Product, Repair};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * API REST de Dashboard.
 *
 * GET /api/dashboard/summary        → resumen del periodo (today|week|month)
 * GET /api/dashboard/top-products   → top 10 productos del mes
 * GET /api/dashboard/cash-session   → estado de la sesión de caja actual
 */
class DashboardApiController extends ApiController
{
    /**
     * Resumen de ventas según periodo.
     *
     * @queryParam period string Periodo: today|week|month (default: today)
     * @queryParam from   string Fecha inicio (Y-m-d) si period=custom
     * @queryParam to     string Fecha fin   (Y-m-d) si period=custom
     */
    public function summary(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return $this->forbidden('Solo el administrador puede ver el dashboard');
        }

        $period = $request->get('period', 'today');
        [$from, $to] = $this->resolveDateRange($period, $request->get('from'), $request->get('to'));

        $query = Sale::query();
        if ($from) $query->whereDate('created_at', '>=', $from);
        if ($to)   $query->whereDate('created_at', '<=', $to);

        $totals = (clone $query)->selectRaw('
            COUNT(*)                         AS total_ventas,
            COALESCE(SUM(total),0)           AS total_general,
            COALESCE(SUM(payment_cash),0)    AS total_efectivo,
            COALESCE(SUM(payment_virtual),0) AS total_virtual
        ')->first();

        $ticketPromedio = $totals->total_ventas > 0
            ? round($totals->total_general / $totals->total_ventas, 2)
            : 0;

        $totalProductosSinStock = Product::where('stock_qty', 0)
            ->where(fn($q) => $q->where('barcode', '!=', 'RECHARGE')->orWhereNull('barcode'))
            ->count();

        $reparacionesPendientes = Repair::where('status', 'pending')->count();

        return $this->success([
            'periodo'                    => $period,
            'desde'                      => $from,
            'hasta'                      => $to,
            'total_ventas'               => (int) $totals->total_ventas,
            'total_general'              => (float) $totals->total_general,
            'total_efectivo'             => (float) $totals->total_efectivo,
            'total_virtual'              => (float) $totals->total_virtual,
            'ticket_promedio'            => $ticketPromedio,
            'productos_sin_stock'        => $totalProductosSinStock,
            'reparaciones_pendientes'    => $reparacionesPendientes,
        ], 'Resumen del dashboard');
    }

    /**
     * Top 10 productos más vendidos del mes actual.
     */
    public function topProducts(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return $this->forbidden('Solo el administrador puede ver este reporte');
        }

        $top = DB::table('sale_items')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereDate('sales.created_at', '>=', now()->startOfMonth()->toDateString())
            ->whereDate('sales.created_at', '<=', now()->endOfMonth()->toDateString())
            ->select('products.id', 'products.description', DB::raw('SUM(sale_items.quantity) as cantidad_vendida'), DB::raw('SUM(sale_items.subtotal) as ingresos_total'))
            ->groupBy('products.id', 'products.description')
            ->orderByDesc('cantidad_vendida')
            ->limit(10)
            ->get();

        return $this->success([
            'mes'      => now()->format('Y-m'),
            'productos' => $top,
        ], 'Top 10 productos del mes');
    }

    /**
     * Estado de la sesión de caja activa hoy.
     */
    public function cashSession(Request $request): JsonResponse
    {
        $session = CashSession::whereDate('date', now()->toDateString())
            ->orderByDesc('id')
            ->first();

        if (!$session) {
            return $this->success(null, 'No hay sesión de caja activa hoy');
        }

        $ventas = Sale::where('cash_session_id', $session->id)
            ->selectRaw('
                COUNT(*) AS n,
                COALESCE(SUM(payment_cash),0)    AS efectivo,
                COALESCE(SUM(payment_virtual),0) AS virtual,
                COALESCE(SUM(total),0)           AS total
            ')
            ->first();

        $movimientos = CashMovement::where('cash_session_id', $session->id)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type='ingreso' THEN amount END),0) AS ingresos,
                COALESCE(SUM(CASE WHEN type='egreso'  THEN amount END),0) AS egresos
            ")
            ->first();

        $enCaja = $session->base_amount
            + $ventas->efectivo
            + $movimientos->ingresos
            - $movimientos->egresos;

        return $this->success([
            'sesion_id'    => $session->id,
            'fecha'        => $session->date,
            'estado'       => $session->close_at ? 'cerrada' : 'abierta',
            'monto_base'   => (float) $session->base_amount,
            'total_ventas' => (int) $ventas->n,
            'efectivo'     => (float) $ventas->efectivo,
            'virtual'      => (float) $ventas->virtual,
            'ingresos'     => (float) $movimientos->ingresos,
            'egresos'      => (float) $movimientos->egresos,
            'en_caja'      => (float) $enCaja,
        ], 'Estado de caja actual');
    }

    // ─── helpers privados ───────────────────────────────────────────────────

    private function resolveDateRange(string $period, ?string $from, ?string $to): array
    {
        return match ($period) {
            'week'   => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
            'month'  => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
            'custom' => [$from ?? now()->subDays(7)->toDateString(), $to ?? now()->toDateString()],
            default  => [now()->toDateString(), now()->toDateString()], // today
        };
    }
}
