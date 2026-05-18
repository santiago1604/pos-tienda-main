@extends('layouts.app')
@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
  <div>
    <h1 class="text-xl font-bold text-slate-900">Usuarios</h1>
    <p class="text-sm text-slate-500 mt-0.5">Gestión de accesos y roles del sistema</p>
  </div>
  <button onclick="document.getElementById('createPanel').scrollIntoView({behavior:'smooth'})"
    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition sm:hidden">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Nuevo usuario
  </button>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

  {{-- ── Panel crear usuario ── --}}
  <div class="lg:col-span-4" id="createPanel">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden sticky top-20">
      <div class="px-5 py-4 border-b border-slate-100">
        <h2 class="font-semibold text-slate-800 text-sm">Crear usuario</h2>
      </div>
      @if($errors->any())
      <div class="mx-5 mt-4 bg-red-50 border border-red-200 text-red-700 px-3 py-2.5 rounded-xl text-sm">
        <ul class="list-disc pl-4 space-y-0.5">
          @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
      </div>
      @endif
      <form method="POST" action="{{ route('admin.users.store') }}" class="p-5 space-y-4">
        @csrf
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1.5">Nombre completo</label>
          <input name="name" value="{{ old('name') }}" required
            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Nombre del usuario"/>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1.5">Correo electrónico</label>
          <input name="email" type="email" value="{{ old('email') }}" required
            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="correo@ejemplo.com"/>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1.5">Contraseña</label>
          <input name="password" type="password" required
            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Mínimo 8 caracteres"/>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1.5">Rol</label>
          <select name="role"
            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="seller"     {{ old('role')==='seller'     ? 'selected':'' }}>Vendedor</option>
            <option value="admin"      {{ old('role')==='admin'      ? 'selected':'' }}>Administrador</option>
            <option value="technician" {{ old('role')==='technician' ? 'selected':'' }}>Técnico</option>
          </select>
        </div>
        <button type="submit"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl text-sm font-semibold transition">
          Crear usuario
        </button>
      </form>
    </div>
  </div>

  {{-- ── Lista de usuarios ── --}}
  <div class="lg:col-span-8">

    {{-- Filtros --}}
    <form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-4">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1.5">Buscar</label>
          <input name="q" value="{{ $q ?? '' }}" placeholder="Nombre o email"
            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"/>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1.5">Rol</label>
          <select name="role"
            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Todos</option>
            <option value="seller"     {{ ($role??'')==='seller'?'selected':'' }}>Vendedor</option>
            <option value="admin"      {{ ($role??'')==='admin'?'selected':'' }}>Administrador</option>
            <option value="technician" {{ ($role??'')==='technician'?'selected':'' }}>Técnico</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1.5">Estado</label>
          <select name="status"
            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="all"     {{ ($status??'all')==='all'?'selected':'' }}>Todos</option>
            <option value="active"  {{ ($status??'')==='active'?'selected':'' }}>Activos</option>
            <option value="blocked" {{ ($status??'')==='blocked'?'selected':'' }}>Bloqueados</option>
          </select>
        </div>
      </div>
      <div class="mt-3">
        <button class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition">Filtrar</button>
      </div>
    </form>

    {{-- Tabla desktop --}}
    <div class="hidden sm:block bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-100">
            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Usuario</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Rol</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Creado</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          @forelse($users as $u)
          <tr class="hover:bg-slate-50 transition">
            <td class="px-5 py-3.5">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                     style="background:{{ $u->role==='admin' ? '#3b82f6' : ($u->role==='seller' ? '#10b981' : '#f59e0b') }}">
                  {{ strtoupper(substr($u->name,0,1)) }}
                </div>
                <div>
                  <div class="font-semibold text-slate-800">{{ $u->name }}</div>
                  <div class="text-xs text-slate-400">{{ $u->email }}</div>
                </div>
              </div>
            </td>
            <td class="px-4 py-3.5">
              @php
                $roleLabels = ['admin'=>['Administrador','bg-blue-100 text-blue-700'],'seller'=>['Vendedor','bg-emerald-100 text-emerald-700'],'technician'=>['Técnico','bg-amber-100 text-amber-700']];
                [$roleLabel, $roleClass] = $roleLabels[$u->role] ?? ['—','bg-slate-100 text-slate-600'];
              @endphp
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $roleClass }}">
                {{ $roleLabel }}
              </span>
            </td>
            <td class="px-4 py-3.5">
              @if($u->blocked)
                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-red-600">
                  <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Bloqueado
                </span>
              @else
                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-600">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Activo
                </span>
              @endif
            </td>
            <td class="px-4 py-3.5 text-xs text-slate-400">{{ $u->created_at->format('d/m/Y') }}</td>
            <td class="px-4 py-3.5">
              <div class="flex items-center justify-end gap-2">
                <button onclick="editUser({{ $u->id }},'{{ addslashes($u->name) }}','{{ $u->email }}','{{ $u->role }}')"
                  class="text-xs text-blue-600 hover:text-blue-800 font-medium px-2.5 py-1.5 rounded-lg hover:bg-blue-50 transition">
                  Editar
                </button>
                <form method="POST" action="{{ route('admin.users.toggle', $u->id) }}" class="inline">
                  @csrf
                  <button class="text-xs font-medium px-2.5 py-1.5 rounded-lg transition
                    {{ $u->blocked ? 'text-emerald-600 hover:bg-emerald-50' : 'text-amber-600 hover:bg-amber-50' }}">
                    {{ $u->blocked ? 'Activar' : 'Bloquear' }}
                  </button>
                </form>
                @if($u->id !== auth()->id())
                <form method="POST" action="{{ route('admin.users.destroy', $u->id) }}" class="inline"
                  onsubmit="return confirm('¿Eliminar a {{ addslashes($u->name) }}?')">
                  @csrf @method('DELETE')
                  <button class="text-xs text-red-400 hover:text-red-600 font-medium px-2.5 py-1.5 rounded-lg hover:bg-red-50 transition">
                    Eliminar
                  </button>
                </form>
                @endif
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="px-5 py-12 text-center text-slate-400 text-sm">Sin usuarios que coincidan.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Cards móvil --}}
    <div class="sm:hidden space-y-3">
      @foreach($users as $u)
      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-start gap-3 mb-3">
          <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0"
               style="background:{{ $u->role==='admin'?'#3b82f6':($u->role==='seller'?'#10b981':'#f59e0b') }}">
            {{ strtoupper(substr($u->name,0,1)) }}
          </div>
          <div class="flex-1 min-w-0">
            <div class="font-semibold text-slate-800 truncate">{{ $u->name }}</div>
            <div class="text-xs text-slate-400 truncate">{{ $u->email }}</div>
            <div class="flex items-center gap-2 mt-1">
              <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                {{ $u->role==='admin'?'bg-blue-100 text-blue-700':($u->role==='seller'?'bg-emerald-100 text-emerald-700':'bg-amber-100 text-amber-700') }}">
                {{ ['admin'=>'Admin','seller'=>'Vendedor','technician'=>'Técnico'][$u->role] ?? $u->role }}
              </span>
              @if($u->blocked)
                <span class="text-xs text-red-500 font-medium">● Bloqueado</span>
              @else
                <span class="text-xs text-emerald-500 font-medium">● Activo</span>
              @endif
            </div>
          </div>
        </div>
        <div class="flex gap-2">
          <button onclick="editUser({{ $u->id }},'{{ addslashes($u->name) }}','{{ $u->email }}','{{ $u->role }}')"
            class="flex-1 bg-blue-600 text-white py-2 rounded-xl text-xs font-semibold hover:bg-blue-700 transition">Editar</button>
          <form method="POST" action="{{ route('admin.users.toggle', $u->id) }}" class="flex-1">
            @csrf
            <button class="w-full py-2 rounded-xl text-xs font-semibold transition border
              {{ $u->blocked ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-amber-50 text-amber-600 border-amber-200' }}">
              {{ $u->blocked ? 'Activar' : 'Bloquear' }}
            </button>
          </form>
          @if($u->id !== auth()->id())
          <form method="POST" action="{{ route('admin.users.destroy', $u->id) }}"
            onsubmit="return confirm('¿Eliminar?')">
            @csrf @method('DELETE')
            <button class="px-3 py-2 rounded-xl text-xs font-semibold bg-red-50 text-red-500 border border-red-200 hover:bg-red-100 transition">✕</button>
          </form>
          @endif
        </div>
      </div>
      @endforeach
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
  </div>
</div>

{{-- ── Modal edición ── --}}
<div id="editModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
    <h3 class="text-base font-bold text-slate-800 mb-5">Editar Usuario</h3>
    <form id="editForm" method="POST" class="space-y-4">
      @csrf @method('PATCH')
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1.5">Nombre</label>
        <input id="editName" name="name" required
          class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"/>
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1.5">Email</label>
        <input id="editEmail" name="email" type="email" required
          class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"/>
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1.5">Rol</label>
        <select id="editRole" name="role"
          class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="seller">Vendedor</option>
          <option value="admin">Administrador</option>
          <option value="technician">Técnico</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1.5">Nueva contraseña <span class="text-slate-400">(opcional)</span></label>
        <input id="editPassword" name="password" type="password"
          class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Dejar vacío para no cambiar"/>
      </div>
      <div class="flex gap-2 pt-2">
        <button type="submit"
          class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl text-sm font-semibold transition">Guardar cambios</button>
        <button type="button" onclick="closeEditModal()"
          class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 py-2.5 rounded-xl text-sm font-medium transition">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
function editUser(id, name, email, role) {
  document.getElementById('editForm').action = `/users/${id}`;
  document.getElementById('editName').value = name;
  document.getElementById('editEmail').value = email;
  document.getElementById('editRole').value = role;
  document.getElementById('editPassword').value = '';
  document.getElementById('editModal').classList.remove('hidden');
}
function closeEditModal() { document.getElementById('editModal').classList.add('hidden'); }
document.getElementById('editModal').addEventListener('click', e => {
  if (e.target === e.currentTarget) closeEditModal();
});
</script>
@endsection
