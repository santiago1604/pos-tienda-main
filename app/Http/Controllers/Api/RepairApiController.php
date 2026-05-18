<?php

namespace App\Http\Controllers\Api;

use App\Models\Repair;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * API REST de Reparaciones.
 *
 * GET    /api/repairs             → listar (filtros: status, technician_id, per_page)
 * POST   /api/repairs             → registrar nueva reparación
 * GET    /api/repairs/{id}        → detalle
 * PATCH  /api/repairs/{id}/status → cambiar estado (admin / técnico)
 * DELETE /api/repairs/{id}        → eliminar [admin]
 */
class RepairApiController extends ApiController
{
    /**
     * Listar reparaciones.
     *
     * @queryParam status        string  Filtrar por estado: pending|in_progress|completed|delivered
     * @queryParam technician_id integer Filtrar por técnico asignado.
     * @queryParam per_page      integer Registros por página (default 20).
     */
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = Repair::with(['receivedBy', 'technician'])->orderByDesc('created_at');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($techId = $request->get('technician_id')) {
            $query->where('technician_id', $techId);
        }

        // Técnico solo ve sus reparaciones asignadas o las pendientes
        if ($user->role === 'technician') {
            $query->where(function ($q) use ($user) {
                $q->where('technician_id', $user->id)->orWhere('status', 'pending');
            });
        }

        $perPage  = (int) $request->get('per_page', 20);
        $repairs  = $query->paginate($perPage);

        return $this->paginated($repairs, 'Reparaciones obtenidas correctamente');
    }

    /**
     * Registrar una nueva reparación.
     *
     * @bodyParam customer_name      string required Nombre del cliente.
     * @bodyParam customer_phone     string          Teléfono del cliente.
     * @bodyParam device_description string required Descripción del equipo.
     * @bodyParam problem_description string          Descripción del problema.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_name'       => 'required|string|max:150',
            'customer_phone'      => 'nullable|string|max:30',
            'device_description'  => 'required|string|max:255',
            'problem_description' => 'nullable|string|max:1000',
        ]);

        $data['status']         = 'pending';
        $data['received_by_id'] = $request->user()->id;
        $data['received_at']    = now();

        $repair = Repair::create($data);
        $repair->load(['receivedBy']);

        return $this->created($repair, 'Reparación registrada correctamente');
    }

    /**
     * Detalle de una reparación.
     */
    public function show(int $id): JsonResponse
    {
        $repair = Repair::with(['receivedBy', 'technician'])->find($id);

        if (!$repair) {
            return $this->notFound('Reparación no encontrada');
        }

        return $this->success($repair);
    }

    /**
     * Cambiar estado de una reparación.
     *
     * @bodyParam status        string  required Nuevo estado: pending|in_progress|completed|delivered
     * @bodyParam technician_id integer          Asignar técnico (admin).
     * @bodyParam total_cost    numeric          Costo total (técnico / admin).
     * @bodyParam repair_description string      Descripción de la reparación realizada.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $repair = Repair::find($id);

        if (!$repair) {
            return $this->notFound('Reparación no encontrada');
        }

        $user = $request->user();
        $data = $request->validate([
            'status'             => 'nullable|in:pending,in_progress,completed,delivered',
            'technician_id'      => 'nullable|exists:users,id',
            'total_cost'         => 'nullable|numeric|min:0',
            'repair_description' => 'nullable|string|max:1000',
            'parts_cost'         => 'nullable|numeric|min:0',
        ]);

        if (!empty($data['technician_id']) && in_array($user->role, ['admin', 'seller'])) {
            $repair->technician_id = $data['technician_id'];
            $repair->status        = 'in_progress';
        }

        if (!empty($data['total_cost']) && in_array($user->role, ['admin', 'technician'])) {
            $repair->total_cost         = $data['total_cost'];
            $repair->parts_cost         = $data['parts_cost'] ?? $repair->parts_cost;
            $repair->repair_description = $data['repair_description'] ?? $repair->repair_description;
            $repair->status             = 'completed';
            if ($user->role === 'technician') {
                $repair->technician_id = $user->id;
            }
        }

        if (isset($data['status'])) {
            $repair->status = $data['status'];
            if ($data['status'] === 'delivered') {
                $repair->delivered_at = now();
            }
        }

        $repair->save();

        return $this->success($repair, 'Estado de reparación actualizado');
    }

    /**
     * Eliminar una reparación. [admin]
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return $this->forbidden('Solo el administrador puede eliminar reparaciones');
        }

        $repair = Repair::find($id);

        if (!$repair) {
            return $this->notFound('Reparación no encontrada');
        }

        $repair->delete();

        return $this->success(null, 'Reparación eliminada correctamente');
    }
}
