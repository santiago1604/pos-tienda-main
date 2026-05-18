@extends('layouts.app')
@section('content')

<div class="max-w-lg mx-auto">
  <div class="mb-6">
    <h1 class="text-xl font-bold text-slate-900">Sesión de Caja</h1>
    <p class="text-sm text-slate-500 mt-0.5">{{ $session ? 'Actualiza la base o gestiona la caja de hoy' : 'Abre la caja para comenzar a registrar ventas' }}</p>
  </div>

  {{-- Estado actual --}}
  @if($session)
  <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 mb-5 flex items-center gap-3">
    <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
      <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
    </div>
    <div>
      <div class="text-sm font-semibold text-emerald-800">Caja abierta</div>
      <div class="text-xs text-emerald-600">
        Abierta por <strong>{{ $session->opened_by ?? 'Administrador' }}</strong>
        · Base: <strong>$ {{ number_format($session->base_amount, 0, ',', '.') }}</strong>
      </div>
    </div>
  </div>
  @endif

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-100">
      <h2 class="font-semibold text-slate-800">{{ $session ? 'Actualizar base de caja' : 'Abrir caja' }}</h2>
    </div>
    <form method="POST" action="{{ route('cash.store') }}" class="p-6 space-y-5">
      @csrf
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1.5">Monto base de caja</label>
        <div class="relative">
          <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-medium text-sm">$</span>
          <input name="base_amount" type="text" inputmode="decimal" required
            value="{{ $session ? $session->base_amount : '' }}"
            placeholder="0"
            class="w-full border border-slate-200 rounded-xl pl-8 pr-4 py-3 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 currency-input"/>
        </div>
        <p class="text-xs text-slate-400 mt-1.5">Dinero físico disponible al inicio del día</p>
      </div>
      <button type="submit"
        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-semibold text-sm transition shadow-md shadow-blue-100">
        {{ $session ? 'Guardar cambios' : '🔓 Abrir caja' }}
      </button>
    </form>
  </div>

  {{-- Accesos rápidos --}}
  <div class="grid grid-cols-2 gap-3 mt-4">
    <a href="{{ route('cash.open') }}"
      class="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm hover:border-blue-200 hover:bg-blue-50 transition group">
      <div class="w-9 h-9 bg-slate-100 group-hover:bg-blue-100 rounded-xl flex items-center justify-center mb-2 transition">
        <svg class="w-4 h-4 text-slate-500 group-hover:text-blue-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
      </div>
      <div class="text-sm font-semibold text-slate-700 group-hover:text-blue-700">Apertura</div>
      <div class="text-xs text-slate-400">Base e inicio de caja</div>
    </a>
    <a href="{{ route('cash.close.summary') }}"
      class="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm hover:border-red-200 hover:bg-red-50 transition group">
      <div class="w-9 h-9 bg-slate-100 group-hover:bg-red-100 rounded-xl flex items-center justify-center mb-2 transition">
        <svg class="w-4 h-4 text-slate-500 group-hover:text-red-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
      </div>
      <div class="text-sm font-semibold text-slate-700 group-hover:text-red-600">Cerrar caja</div>
      <div class="text-xs text-slate-400">Ver resumen y cerrar</div>
    </a>
  </div>
</div>

@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('input.currency-input').forEach(inp => {
    inp.addEventListener('focus', e => {
      e.target.value = e.target.value.replace(/[^\d.,]/g,'').replace(/\./g,'').replace(',','.');
    });
    inp.addEventListener('blur', e => {
      let n = parseFloat(e.target.value);
      if (!isNaN(n)) e.target.value = '$ ' + n.toLocaleString('es-CO');
    });
  });
  document.querySelectorAll('form').forEach(f => {
    f.addEventListener('submit', () => {
      f.querySelectorAll('input.currency-input').forEach(ci => {
        ci.value = ci.value.replace(/[^\d.,]/g,'').replace(/\./g,'').replace(',','.');
      });
    });
  });
});
</script>
@endsection
