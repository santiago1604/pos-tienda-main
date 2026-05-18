@extends('layouts.app')
@section('content')
@php
function formatCurrency($amount) {
    return '$ ' . number_format($amount, 0, ',', '.');
}
$periods = [
    'today'    => 'Hoy',
    'week'     => 'Esta semana',
    'month'    => 'Este mes',
    'custom'   => 'Personalizado',
    'specific' => 'Día específico',
];
@endphp

{{-- ── Filtros de periodo ── --}}
<div class="flex flex-wrap items-center gap-2 mb-6">
  @foreach($periods as $key => $label)
    <a href="{{ route('dashboard', ['period' => $key]) }}"
       class="px-4 py-2 rounded-xl text-sm font-medium transition-all
              {{ $period === $key
                 ? 'bg-blue-600 text-white shadow-md shadow-blue-200'
                 : 'bg-white border border-slate-200 text-slate-600 hover:border-blue-300 hover:text-blue-600' }}">
      {{ $label }}
    </a>
  @endforeach
  @if(auth()->user()->role === 'admin')
  <a href="{{ route('reports.session.csv') }}"
     class="ml-auto flex items-center gap-2 px-4 py-2 bg-slate-800 text-white rounded-xl text-sm font-medium hover:bg-slate-700 transition">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
    Exportar CSV
  </a>
  @endif
</div>

{{-- ── Filtro rango personalizado ── --}}
@if($period === 'custom')
<div class="bg-white rounded-2xl border border-slate-200 p-5 mb-6 shadow-sm">
  <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col sm:flex-row items-end gap-3">
    <input type="hidden" name="period" value="custom">
    <div class="flex-1">
      <label class="block text-xs font-medium text-slate-500 mb-1.5">Desde</label>
      <input type="date" name="from" value="{{ $from ?? '' }}"
        class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"/>
    </div>
    <div class="flex-1">
      <label class="block text-xs font-medium text-slate-500 mb-1.5">Hasta</label>
      <input type="date" name="to" value="{{ $to ?? '' }}"
        class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"/>
    </div>
    <button type="submit"
      class="bg-blue-600 text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 transition whitespace-nowrap">
      Aplicar filtro
    </button>
  </form>
</div>
@endif

@if($period === 'specific')
<div class="bg-white rounded-2xl border border-slate-200 p-5 mb-6 shadow-sm">
  <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col sm:flex-row items-end gap-3">
    <input type="hidden" name="period" value="specific">
    <div class="flex-1">
      <label class="block text-xs font-medium text-slate-500 mb-1.5">Selecciona un día</label>
      <input type="date" name="date" value="{{ $specificDate ?? '' }}"
        class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"/>
    </div>
    <button type="submit"
      class="bg-blue-600 text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 transition whitespace-nowrap">
      Consultar
    </button>
  </form>
</div>
@endif

{{-- ── KPI Cards ── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

  <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total vendido</span>
      <div class="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center">
        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
    </div>
    <div class="text-2xl font-bold text-slate-900">{!! formatCurrency($summary->total ?? 0) !!}</div>
    <div class="text-xs text-slate-400 mt-1">{{ $summary->n ?? 0 }} ventas</div>
  </div>

  <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Efectivo</span>
      <div class="w-9 h-9 bg-emerald-50 rounded-xl flex items-center justify-center">
        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
      </div>
    </div>
    <div class="text-2xl font-bold text-slate-900">{!! formatCurrency($summary->cash ?? 0) !!}</div>
    <div class="text-xs text-slate-400 mt-1">Pagos en efectivo</div>
  </div>

  <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Virtual</span>
      <div class="w-9 h-9 bg-violet-50 rounded-xl flex items-center justify-center">
        <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
        </svg>
      </div>
    </div>
    <div class="text-2xl font-bold text-slate-900">{!! formatCurrency($summary->virtual ?? 0) !!}</div>
    <div class="text-xs text-slate-400 mt-1">Transferencias / digital</div>
  </div>

  <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Ticket promedio</span>
      <div class="w-9 h-9 bg-amber-50 rounded-xl flex items-center justify-center">
        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
        </svg>
      </div>
    </div>
    <div class="text-2xl font-bold text-slate-900">{!! formatCurrency($ticket) !!}</div>
    <div class="text-xs text-slate-400 mt-1">Por venta</div>
  </div>
</div>

{{-- ── Fila 2: Top productos + Caja ── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">

  {{-- Top 10 productos --}}
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
      <h2 class="font-semibold text-slate-800 text-sm">Top 10 productos del mes</h2>
      <span class="text-xs text-slate-400">{{ now()->format('M Y') }}</span>
    </div>
    <div class="p-4">
      @if(count($top) > 0)
        <div class="space-y-2">
          @foreach($top as $i => $t)
          @php $pct = $top[0]->qty > 0 ? round(($t->qty / $top[0]->qty) * 100) : 0; @endphp
          <div class="flex items-center gap-3">
            <span class="w-6 text-xs font-bold text-slate-300 text-center flex-shrink-0">#{{ $i+1 }}</span>
            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-center mb-1">
                <span class="text-sm font-medium text-slate-700 truncate">{{ $t->description }}</span>
                <span class="text-xs font-bold text-slate-500 ml-2 flex-shrink-0">{{ $t->qty }} uds</span>
              </div>
              <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full {{ $i === 0 ? 'bg-blue-500' : ($i < 3 ? 'bg-blue-300' : 'bg-slate-300') }}"
                     style="width:{{ $pct }}%"></div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      @else
        <div class="text-center py-10 text-slate-400 text-sm">Sin datos de productos este mes</div>
      @endif
    </div>
  </div>

  {{-- Caja hoy --}}
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
      <h2 class="font-semibold text-slate-800 text-sm">Resumen de caja hoy</h2>
      @if($session)
        <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full
          {{ $session->close_at ? 'bg-slate-100 text-slate-500' : 'bg-emerald-50 text-emerald-600' }}">
          <span class="w-1.5 h-1.5 rounded-full {{ $session->close_at ? 'bg-slate-400' : 'bg-emerald-500' }}"></span>
          {{ $session->close_at ? 'Cerrada' : 'Abierta' }}
        </span>
      @endif
    </div>

    @if($session)
    <div class="p-4 grid grid-cols-2 gap-3">
      @php
        $cajaCells = [
          ['label'=>'Base de caja',   'value'=>formatCurrency($session->base_amount ?? 0), 'color'=>'text-slate-700'],
          ['label'=>'Ventas efectivo','value'=>formatCurrency($sum->cash ?? 0),             'color'=>'text-emerald-600'],
          ['label'=>'Ventas virtual', 'value'=>formatCurrency($sum->virtual ?? 0),          'color'=>'text-violet-600'],
          ['label'=>'Otros ingresos', 'value'=>'+'.formatCurrency($mov->ing ?? 0),           'color'=>'text-emerald-600'],
          ['label'=>'Egresos',        'value'=>'-'.formatCurrency($mov->egr ?? 0),           'color'=>'text-red-500'],
          ['label'=>'Total ventas',   'value'=>formatCurrency($sum->total ?? 0),             'color'=>'text-slate-700'],
        ];
      @endphp
      @foreach($cajaCells as $cell)
      <div class="bg-slate-50 rounded-xl p-3">
        <div class="text-xs text-slate-400 font-medium mb-1">{{ $cell['label'] }}</div>
        <div class="font-bold text-sm {{ $cell['color'] }}">{!! $cell['value'] !!}</div>
      </div>
      @endforeach

      <div class="col-span-2 rounded-xl p-4"
           style="background:linear-gradient(135deg,#0f172a,#1e3a5f)">
        <div class="text-xs text-blue-300 font-medium mb-1">Total en caja</div>
        <div class="text-2xl font-bold text-white">{!! formatCurrency($enCaja ?? 0) !!}</div>
      </div>
    </div>
    @else
    <div class="flex flex-col items-center justify-center py-12 gap-3">
      <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center">
        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
      </div>
      <p class="text-sm text-slate-500">Sin sesión de caja abierta hoy</p>
      <a href="{{ route('cash.open') }}"
         class="px-5 py-2 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition">
        Abrir caja
      </a>
    </div>
    @endif
  </div>
</div>

{{-- ── Ventas detalladas ── --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-6">
  <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
    <h2 class="font-semibold text-slate-800 text-sm">Ventas detalladas</h2>
    <span class="text-xs text-slate-400">{{ ucfirst($periods[$period] ?? $period) }}</span>
  </div>

  @if(isset($periodSales) && count($periodSales))
  {{-- Desktop --}}
  <div class="hidden sm:block overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead>
        <tr class="bg-slate-50 border-b border-slate-100">
          <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Fecha / Hora</th>
          <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Vendedor</th>
          <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Productos</th>
          <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Total</th>
          <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Efectivo</th>
          <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Virtual</th>
          <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-50">
        @foreach($periodSales as $s)
        <tr class="hover:bg-slate-50 transition">
          <td class="px-5 py-3.5">
            <div class="font-medium text-slate-700">{{ $s->created_at->format('d/m/Y') }}</div>
            <div class="text-xs text-slate-400">{{ $s->created_at->format('H:i:s') }}</div>
          </td>
          <td class="px-5 py-3.5">
            <span class="text-sm text-slate-600">{{ $s->user->name ?? '—' }}</span>
          </td>
          <td class="px-5 py-3.5">
            <ul class="space-y-0.5">
              @foreach($s->items as $it)
              <li class="text-xs text-slate-500">
                <span class="font-semibold text-slate-700">{{ $it->quantity }}×</span>
                {{ $it->product->description ?? $it->description ?? 'Item' }}
              </li>
              @endforeach
            </ul>
          </td>
          <td class="px-5 py-3.5 text-right font-bold text-slate-800">{!! formatCurrency($s->total) !!}</td>
          <td class="px-5 py-3.5 text-right text-emerald-600 font-semibold">{!! formatCurrency($s->payment_cash) !!}</td>
          <td class="px-5 py-3.5 text-right text-violet-600 font-semibold">{!! formatCurrency($s->payment_virtual) !!}</td>
          <td class="px-5 py-3.5 text-center">
            <form method="POST" action="{{ route('pos.sales.destroy', $s->id) }}"
                  onsubmit="return confirm('¿Eliminar esta venta?')" class="inline">
              @csrf @method('DELETE')
              <button class="text-xs text-red-400 hover:text-red-600 font-medium transition px-2 py-1 rounded-lg hover:bg-red-50">
                Eliminar
              </button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- Mobile --}}
  <div class="sm:hidden divide-y divide-slate-100">
    @foreach($periodSales as $s)
    <div class="p-4">
      <div class="flex justify-between items-start mb-2">
        <div>
          <div class="font-semibold text-sm text-slate-800">{{ $s->created_at->format('d/m/Y H:i') }}</div>
          <div class="text-xs text-slate-400">{{ $s->user->name ?? '—' }}</div>
        </div>
        <div class="font-bold text-slate-900">{!! formatCurrency($s->total) !!}</div>
      </div>
      <ul class="space-y-0.5 mb-2">
        @foreach($s->items as $it)
        <li class="text-xs text-slate-500">{{ $it->quantity }}× {{ $it->product->description ?? 'Item' }}</li>
        @endforeach
      </ul>
      <div class="flex justify-between text-xs mb-3">
        <span class="text-emerald-600 font-medium">Efectivo: {!! formatCurrency($s->payment_cash) !!}</span>
        <span class="text-violet-600 font-medium">Virtual: {!! formatCurrency($s->payment_virtual) !!}</span>
      </div>
      <form method="POST" action="{{ route('pos.sales.destroy', $s->id) }}"
            onsubmit="return confirm('¿Eliminar esta venta?')">
        @csrf @method('DELETE')
        <button class="w-full text-xs text-red-400 hover:text-red-600 font-medium py-1.5 rounded-lg bg-red-50 hover:bg-red-100 transition">
          Eliminar venta
        </button>
      </form>
    </div>
    @endforeach
  </div>
  @else
  <div class="flex flex-col items-center py-14 text-slate-400 gap-2">
    <svg class="w-8 h-8 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
    <span class="text-sm">Sin ventas en este período</span>
  </div>
  @endif
</div>

{{-- ── Fila 3: Movimientos + Productos vendidos ── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

  {{-- Movimientos de caja --}}
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
      <h2 class="font-semibold text-slate-800 text-sm">Movimientos de caja hoy</h2>
    </div>
    @if(isset($movements) && count($movements))
    <div class="divide-y divide-slate-50">
      @foreach($movements as $m)
      <div class="flex items-center gap-3 px-5 py-3.5">
        <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0
          {{ $m->type === 'ingreso' ? 'bg-emerald-50' : 'bg-red-50' }}">
          <svg class="w-4 h-4 {{ $m->type === 'ingreso' ? 'text-emerald-500' : 'text-red-400' }}"
               fill="none" stroke="currentColor" viewBox="0 0 24 24">
            @if($m->type === 'ingreso')
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            @else
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
            @endif
          </svg>
        </div>
        <div class="flex-1 min-w-0">
          <div class="text-sm font-medium text-slate-700 truncate">{{ $m->description }}</div>
          <div class="text-xs text-slate-400">{{ $m->created_at->format('H:i:s') }}</div>
        </div>
        <div class="font-bold text-sm flex-shrink-0
          {{ $m->type === 'ingreso' ? 'text-emerald-600' : 'text-red-500' }}">
          {{ $m->type === 'ingreso' ? '+' : '−' }}{!! formatCurrency($m->amount) !!}
        </div>
      </div>
      @endforeach
    </div>
    @else
    <div class="flex flex-col items-center py-12 text-slate-400 gap-2">
      <svg class="w-7 h-7 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
      </svg>
      <span class="text-sm">Sin movimientos hoy</span>
    </div>
    @endif
  </div>

  {{-- Productos vendidos hoy --}}
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
      <h2 class="font-semibold text-slate-800 text-sm">Productos vendidos hoy</h2>
    </div>
    @if(isset($soldProducts) && count($soldProducts))
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-100">
            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Producto</th>
            <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Uds</th>
            <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Total</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          @foreach($soldProducts as $prod)
          <tr class="hover:bg-slate-50 transition">
            <td class="px-5 py-3 text-slate-700 font-medium">{{ $prod->description }}</td>
            <td class="px-5 py-3 text-center">
              <span class="inline-flex items-center justify-center w-7 h-7 bg-blue-50 text-blue-600 rounded-lg text-xs font-bold">
                {{ $prod->qty }}
              </span>
            </td>
            <td class="px-5 py-3 text-right font-bold text-slate-800">{!! formatCurrency($prod->total) !!}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @else
    <div class="flex flex-col items-center py-12 text-slate-400 gap-2">
      <svg class="w-7 h-7 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
      </svg>
      <span class="text-sm">Sin productos vendidos hoy</span>
    </div>
    @endif
  </div>

</div>
@endsection
