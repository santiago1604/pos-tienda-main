@extends('layouts.app')
@section('content')

{{-- ── Header ── --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
  <div>
    <h1 class="text-xl font-bold text-slate-900">Reparaciones</h1>
    <p class="text-sm text-slate-500 mt-0.5">Gestión de equipos recibidos y en proceso</p>
  </div>
  @if(in_array(auth()->user()->role, ['admin','technician']))
  <a href="{{ route('repairs.history') }}"
    class="flex items-center gap-2 bg-white border border-slate-200 hover:border-blue-300 text-slate-600 hover:text-blue-600 px-4 py-2.5 rounded-xl text-sm font-medium transition">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
    Historial completo
  </a>
  @endif
</div>

@if($errors->any())
<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-4 text-sm">
  <ul class="list-disc pl-4 space-y-0.5">
    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
  </ul>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

  {{-- ── Formulario recibir ── --}}
  <div class="lg:col-span-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden sticky top-20">
      <div class="px-5 py-4 border-b border-slate-100">
        <h2 class="font-semibold text-slate-800 text-sm">Recibir dispositivo</h2>
      </div>
      <form method="POST" action="{{ route('repairs.store') }}" class="p-5 space-y-4" id="repairReceiveForm">
        @csrf
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1.5">Nombre del cliente *</label>
          <input name="customer_name" value="{{ old('customer_name') }}" required
            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Nombre completo"/>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1.5">Teléfono *</label>
          <input name="customer_phone" value="{{ old('customer_phone') }}" required
            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Número de contacto"/>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1.5">Correo electrónico</label>
          <input name="customer_email" value="{{ old('customer_email') }}" type="email"
            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Para notificar cuando esté listo"/>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1.5">Dispositivo *</label>
          <input name="device_description" value="{{ old('device_description') }}" required
            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Ej: Samsung Galaxy S21"/>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1.5">Problema / Motivo *</label>
          <textarea name="issue_description" rows="3" required
            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
            placeholder="Describe el problema del cliente...">{{ old('issue_description') }}</textarea>
        </div>

        {{-- Abono inicial --}}
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
          <div class="flex items-center gap-2 mb-2">
            <input type="checkbox" id="hasDeposit" name="has_deposit" value="1"
              class="rounded border-slate-300 text-blue-600"
              {{ old('has_deposit') ? 'checked' : '' }}/>
            <label for="hasDeposit" class="text-xs font-semibold text-slate-700">Registrar abono inicial</label>
          </div>
          <div id="depositFields" class="space-y-3 {{ old('has_deposit') ? '' : 'hidden' }}">
            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block text-xs text-slate-500 mb-1">Monto</label>
                <input type="text" inputmode="decimal" name="deposit_amount" value="{{ old('deposit_amount') }}"
                  class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 currency-input"
                  placeholder="$ 0"/>
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">Método</label>
                <select name="deposit_payment_method"
                  class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <option value="cash">Efectivo</option>
                  <option value="virtual">Virtual</option>
                </select>
              </div>
            </div>
            <p class="text-xs text-slate-400">El abono se sumará a los totales del POS según el método elegido.</p>
          </div>
        </div>

        <button type="submit"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl text-sm font-semibold transition">
          Recibir dispositivo
        </button>
      </form>
    </div>
  </div>

  {{-- ── Lista de reparaciones ── --}}
  <div class="lg:col-span-8">

    {{-- Filtro estado --}}
    <form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-4 flex flex-wrap gap-2 items-end">
      @php
        $statusOpts = [''=> 'Todos','pending'=>'Pendiente','in_progress'=>'En proceso','completed'=>'Completada','delivered'=>'Entregada'];
      @endphp
      @foreach($statusOpts as $val => $lbl)
      <a href="{{ route('repairs.index', $val ? ['status'=>$val] : []) }}"
        class="px-4 py-2 rounded-xl text-sm font-medium transition
          {{ ($status ?? '') === $val
            ? 'bg-blue-600 text-white'
            : 'bg-slate-50 border border-slate-200 text-slate-600 hover:border-blue-300 hover:text-blue-600' }}">
        {{ $lbl }}
      </a>
      @endforeach
    </form>

    {{-- Cards de reparaciones --}}
    <div class="space-y-3">
    @forelse($repairs as $r)
    @php
      $statusConfig = [
        'pending'     => ['Pendiente',   'bg-amber-100 text-amber-700 border-amber-200'],
        'in_progress' => ['En proceso',  'bg-blue-100 text-blue-700 border-blue-200'],
        'completed'   => ['Completada',  'bg-emerald-100 text-emerald-700 border-emerald-200'],
        'delivered'   => ['Entregada',   'bg-slate-100 text-slate-600 border-slate-200'],
      ];
      [$statusLabel, $statusClass] = $statusConfig[$r->status] ?? ['—','bg-slate-100 text-slate-600 border-slate-200'];
    @endphp
    <div class="bg-white rounded-2xl border {{ $r->is_warranty ? 'border-orange-200 bg-orange-50/30' : 'border-slate-100' }} shadow-sm overflow-hidden">
      <div class="p-5">

        {{-- Header de la card --}}
        <div class="flex items-start justify-between gap-3 mb-3">
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-semibold text-slate-800">{{ $r->customer_name }}</span>
              <span class="text-slate-400">·</span>
              <span class="text-sm text-slate-500">{{ $r->customer_phone }}</span>
              @if($r->is_warranty)
                <span class="text-xs font-bold bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full border border-orange-200">🔧 Garantía</span>
              @endif
            </div>
            <div class="text-sm text-slate-600 mt-0.5">{{ $r->device_description }}</div>
          </div>
          <span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full border flex-shrink-0 {{ $statusClass }}">
            {{ $statusLabel }}
          </span>
        </div>

        {{-- Problema --}}
        <div class="bg-slate-50 rounded-xl p-3 mb-3">
          <div class="text-xs font-semibold text-slate-500 mb-1">Problema reportado</div>
          <div class="text-sm text-slate-700">{{ $r->issue_description }}</div>
          @if($r->is_warranty && $r->warranty_notes)
          <div class="mt-2 text-xs text-orange-600 font-medium">Nota garantía: {{ $r->warranty_notes }}</div>
          @endif
        </div>

        {{-- Trabajo + costos --}}
        @if($r->repair_description || $r->total_cost)
        <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-3 mb-3">
          @if($r->repair_description)
            <div class="text-xs font-semibold text-slate-500 mb-1">Trabajo realizado</div>
            <div class="text-sm text-slate-700 mb-2">{{ $r->repair_description }}</div>
          @endif
          <div class="flex gap-4 text-sm">
            @if($r->parts_cost)
            <span class="text-slate-600">Repuestos: <strong>$ {{ number_format($r->parts_cost,0,',','.') }}</strong></span>
            @endif
            <span class="text-emerald-700 font-bold">Total: $ {{ number_format($r->total_cost,0,',','.') }}</span>
          </div>
        </div>
        @endif

        {{-- Abonos --}}
        <div class="flex gap-4 text-sm mb-3">
          <span class="text-slate-500">Abono: <strong class="text-emerald-600">$ {{ number_format($r->deposit_total,0,',','.') }}</strong></span>
          @if($r->remaining !== null)
          <span class="text-slate-500">Restante: <strong class="{{ $r->remaining > 0 ? 'text-red-500' : 'text-slate-700' }}">$ {{ number_format($r->remaining,0,',','.') }}</strong></span>
          @endif
        </div>

        {{-- Footer info --}}
        <div class="flex items-center gap-3 text-xs text-slate-400 mb-3">
          <span>Recibió: {{ $r->receivedBy?->name ?? '—' }}</span>
          @if($r->technician)
            <span>· Técnico: {{ $r->technician->name }}</span>
          @endif
          <span>· {{ $r->created_at->format('d/m/Y H:i') }}</span>
        </div>

        {{-- Acciones según estado y rol --}}
        @if(in_array(auth()->user()->role, ['admin','technician','seller']) && $r->status !== 'delivered' && $r->status !== 'completed')
        <div class="border-t border-slate-100 pt-3">
          <form method="POST" action="{{ route('repairs.update', $r->id) }}" class="space-y-2">
            @csrf @method('PATCH')
            @if(in_array(auth()->user()->role, ['admin','technician']))
            <input name="repair_description" placeholder="Descripción del trabajo realizado"
              class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"/>
            <div class="grid grid-cols-2 gap-2">
              <input name="parts_cost" type="text" inputmode="decimal" placeholder="Costo repuestos"
                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 currency-input"/>
              <input name="total_cost" type="text" inputmode="decimal" placeholder="Total a cobrar" required
                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 currency-input"/>
            </div>
            @else
            <input name="total_cost" type="text" inputmode="decimal" placeholder="Total a cobrar" required
              class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 currency-input"/>
            @endif
            <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2 rounded-xl text-sm font-semibold transition">
              Registrar precio
            </button>
          </form>
        </div>
        @endif

        {{-- Asignar técnico --}}
        @if(in_array(auth()->user()->role, ['admin','seller']) && $r->status === 'pending')
        <div class="border-t border-slate-100 pt-3 mt-3">
          <form method="POST" action="{{ route('repairs.update', $r->id) }}" class="flex gap-2">
            @csrf @method('PATCH')
            <select name="technician_id"
              class="flex-1 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">Asignar técnico...</option>
              @foreach($technicians as $t)
                <option value="{{ $t->id }}">{{ $t->name }}</option>
              @endforeach
            </select>
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition whitespace-nowrap">
              Asignar
            </button>
          </form>
        </div>
        @endif

        {{-- Entregar y cobrar --}}
        @if(in_array(auth()->user()->role, ['admin','seller','technician']) && $r->status === 'completed')
        @php $due = $r->remaining !== null ? $r->remaining : (($r->total_cost ?? 0) - ($r->deposit_total ?? 0)); @endphp
        <div class="border-t border-slate-100 pt-3 mt-3">
          <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
            <div class="text-xs font-semibold text-blue-700 mb-3">Registrar entrega y cobro</div>
            <form method="POST" action="{{ route('repairs.update', $r->id) }}"
                  class="space-y-3" id="repairPayForm_{{ $r->id }}" data-total="{{ $due }}">
              @csrf @method('PATCH')
              <input type="hidden" name="status" value="delivered"/>
              <div>
                <label class="block text-xs text-slate-500 mb-1">Tipo de pago *</label>
                <select name="payment_type" id="payment_type_{{ $r->id }}"
                  class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                  onchange="togglePaymentFields({{ $r->id }})" required>
                  <option value="">Seleccionar...</option>
                  <option value="cash">Efectivo</option>
                  <option value="digital">Virtual</option>
                  <option value="mixed">Mixto</option>
                </select>
              </div>
              <div id="single_field_{{ $r->id }}" class="hidden">
                <label class="block text-xs text-slate-500 mb-1">Monto recibido *</label>
                <input type="text" name="amount" inputmode="decimal" placeholder="$ 0"
                  class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 currency-input"/>
              </div>
              <div id="mixed_fields_{{ $r->id }}" class="hidden grid grid-cols-2 gap-2">
                <div>
                  <label class="block text-xs text-slate-500 mb-1">Efectivo</label>
                  <input type="text" name="cash_amount" inputmode="decimal" placeholder="$ 0"
                    class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 currency-input"/>
                </div>
                <div>
                  <label class="block text-xs text-slate-500 mb-1">Virtual</label>
                  <input type="text" name="digital_amount" inputmode="decimal" placeholder="$ 0"
                    class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 currency-input"/>
                </div>
              </div>
              <div id="repairHelperBox_{{ $r->id }}" class="hidden bg-white border border-blue-200 rounded-xl p-3 text-xs space-y-1">
                <div class="flex justify-between"><span class="text-slate-500">Total a pagar:</span><span id="repairHelperTotal_{{ $r->id }}" class="font-bold text-slate-800"></span></div>
                <div class="flex justify-between"><span class="text-slate-500">Pagado:</span><span id="repairHelperPaid_{{ $r->id }}" class="font-bold text-slate-800"></span></div>
                <div class="flex justify-between border-t border-slate-100 pt-1">
                  <span id="repairHelperLabel_{{ $r->id }}" class="text-slate-500">Cambio:</span>
                  <span id="repairHelperChange_{{ $r->id }}" class="font-bold text-emerald-600"></span>
                </div>
              </div>
              <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-xl text-sm font-semibold transition">
                Entregar y registrar cobro
              </button>
            </form>
          </div>
        </div>
        <script>
          function togglePaymentFields(id) {
            const type = document.getElementById('payment_type_'+id).value;
            document.getElementById('single_field_'+id).classList.toggle('hidden', type==='mixed'||type==='');
            document.getElementById('mixed_fields_'+id).classList.toggle('hidden', type!=='mixed');
            document.getElementById('repairHelperBox_'+id).classList.toggle('hidden', type==='');
            updateRepairHelper(id);
          }
          function formatPlainCurrency(n) {
            return '$ '+Number(n).toLocaleString('es-CO',{minimumFractionDigits:0,maximumFractionDigits:0});
          }
          function updateRepairHelper(id) {
            const form = document.getElementById('repairPayForm_'+id);
            if (!form) return;
            const total = Number(form.dataset.total||0);
            const type = document.getElementById('payment_type_'+id).value||'cash';
            let paid=0;
            if (type==='mixed') {
              const c = parseFloat((form.querySelector('[name="cash_amount"]')?.value||'0').replace(/[^\d.]/g,''))||0;
              const v = parseFloat((form.querySelector('[name="digital_amount"]')?.value||'0').replace(/[^\d.]/g,''))||0;
              paid=c+v;
            } else {
              paid = parseFloat((form.querySelector('[name="amount"]')?.value||'0').replace(/[^\d.]/g,''))||0;
            }
            const diff = paid - total;
            document.getElementById('repairHelperTotal_'+id).textContent = formatPlainCurrency(total);
            document.getElementById('repairHelperPaid_'+id).textContent  = formatPlainCurrency(paid);
            document.getElementById('repairHelperLabel_'+id).textContent  = diff < 0 ? 'Falta:' : 'Cambio:';
            const ch = document.getElementById('repairHelperChange_'+id);
            ch.textContent = formatPlainCurrency(Math.abs(diff));
            ch.className = 'font-bold '+(diff<0?'text-red-500':'text-emerald-600');
          }
        </script>
        @endif

        @if(auth()->user()->role === 'admin')
        <div class="border-t border-slate-100 pt-3 mt-3 text-right">
          <form method="POST" action="{{ route('repairs.destroy', $r->id) }}" class="inline"
            onsubmit="return confirm('¿Eliminar esta reparación?')">
            @csrf @method('DELETE')
            <button class="text-xs text-red-400 hover:text-red-600 font-medium px-2.5 py-1.5 rounded-lg hover:bg-red-50 transition">
              Eliminar
            </button>
          </form>
        </div>
        @endif
      </div>
    </div>
    @empty
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col items-center py-16 gap-3">
      <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center">
        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
        </svg>
      </div>
      <p class="text-sm text-slate-500">Sin reparaciones en este estado</p>
    </div>
    @endforelse
    </div>
    <div class="mt-4">{{ $repairs->links() }}</div>
  </div>
</div>

@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const chk = document.getElementById('hasDeposit');
  const fields = document.getElementById('depositFields');
  if (chk) chk.addEventListener('change', () => fields.classList.toggle('hidden', !chk.checked));

  attachCurrencyInputs();

  document.querySelectorAll('[id^="repairPayForm_"]').forEach(form => {
    const id = form.id.split('_')[1];
    document.getElementById('payment_type_'+id)?.addEventListener('change', () => updateRepairHelper(id));
    form.querySelectorAll('input.currency-input').forEach(inp => {
      ['input','blur'].forEach(e => inp.addEventListener(e, () => updateRepairHelper(id)));
    });
  });
});

function parseCurrencyToNumber(v) {
  return (v||'').toString().replace(/[^\d.,]/g,'').replace(/\./g,'').replace(',','.');
}
function attachCurrencyInputs() {
  document.querySelectorAll('input.currency-input').forEach(inp => {
    inp.addEventListener('focus', e => { e.target.value = parseCurrencyToNumber(e.target.value); });
    inp.addEventListener('blur', e => {
      let n = parseFloat(parseCurrencyToNumber(e.target.value));
      if (!isNaN(n)) e.target.value = '$ '+n.toLocaleString('es-CO');
    });
  });
  document.querySelectorAll('form').forEach(f => {
    f.addEventListener('submit', () => {
      f.querySelectorAll('input.currency-input').forEach(ci => { ci.value = parseCurrencyToNumber(ci.value); });
    });
  });
}
</script>
@endsection
