@extends('layouts.app')
@section('content')

{{-- ── Header ── --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
  <div>
    <h1 class="text-xl font-bold text-slate-900">Productos</h1>
    <p class="text-sm text-slate-500 mt-0.5">Gestión de inventario y catálogo</p>
  </div>
  <button onclick="document.getElementById('formPanel').scrollIntoView({behavior:'smooth'})"
    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition sm:hidden">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Nuevo producto
  </button>
</div>

{{-- ── Alertas de stock ── --}}
@if($outOfStock->count() || $lowStock->count())
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-5">
  @if($outOfStock->count())
  <div class="bg-red-50 border border-red-200 rounded-2xl p-4">
    <div class="flex items-center justify-between mb-2">
      <span class="text-sm font-semibold text-red-700 flex items-center gap-2">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        Sin stock ({{ $outOfStock->count() }})
      </span>
      <span class="text-xs font-bold bg-red-600 text-white px-2 py-0.5 rounded-full">Urgente</span>
    </div>
    <ul class="space-y-1 max-h-32 overflow-y-auto">
      @foreach($outOfStock as $p0)
      <li class="flex justify-between text-xs text-red-600">
        <span class="truncate">{{ $p0->description }}</span>
        <span class="font-bold ml-2">0</span>
      </li>
      @endforeach
    </ul>
  </div>
  @endif
  @if($lowStock->count())
  <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
    <div class="flex items-center justify-between mb-2">
      <span class="text-sm font-semibold text-amber-700 flex items-center gap-2">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        Stock bajo ({{ $lowStock->count() }})
      </span>
      <span class="text-xs font-bold bg-amber-400 text-amber-900 px-2 py-0.5 rounded-full">Revisar</span>
    </div>
    <ul class="space-y-1 max-h-32 overflow-y-auto">
      @foreach($lowStock as $pl)
      <li class="flex justify-between text-xs text-amber-700">
        <span class="truncate">{{ $pl->description }}</span>
        <span class="font-bold ml-2">{{ $pl->stock_qty }}</span>
      </li>
      @endforeach
    </ul>
  </div>
  @endif
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

  {{-- ── Formulario nuevo producto ── --}}
  <div class="lg:col-span-4" id="formPanel">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden sticky top-20">
      <div class="px-5 py-4 border-b border-slate-100">
        <h2 class="font-semibold text-slate-800 text-sm">Nuevo producto</h2>
      </div>
      <form method="POST" action="{{ route('products.store') }}" class="p-5 space-y-4">
        @csrf
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1.5">Descripción</label>
          <input name="description" required
            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Nombre del producto"/>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1.5">Categoría</label>
          <div class="flex gap-2">
            <select id="category_select" name="category_id" required
              class="flex-1 border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">Seleccionar...</option>
              @foreach($categories as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
              @endforeach
            </select>
            <button type="button" onclick="openCategoryModal()"
              class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 rounded-xl text-sm font-medium text-slate-600 transition whitespace-nowrap">
              + Nueva
            </button>
          </div>
        </div>
        <div class="grid grid-cols-3 gap-2">
          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1.5">Stock</label>
            <input name="stock_qty" type="number" min="0" value="0"
              class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"/>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1.5">Costo</label>
            <input name="unit_cost" type="text" inputmode="decimal" placeholder="0"
              class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 currency-input"/>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1.5">Precio</label>
            <input name="sale_price" type="text" inputmode="decimal" placeholder="0"
              class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 currency-input"/>
          </div>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1.5">Código de barras</label>
          <input name="barcode"
            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Opcional"/>
        </div>
        <div class="flex gap-2 pt-1">
          <button type="submit"
            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl text-sm font-semibold transition">
            Agregar producto
          </button>
          <button type="button" onclick="openManageCategoriesModal()"
            class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 rounded-xl text-sm text-slate-600 transition" title="Gestionar categorías">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
            </svg>
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- ── Tabla de productos ── --}}
  <div class="lg:col-span-8">

    {{-- Buscador --}}
    <form method="GET" action="{{ route('products.index') }}"
      class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-4">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1.5">Buscar</label>
          <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Nombre o código..."
            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"/>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1.5">Categoría</label>
          <select name="category"
            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Todas</option>
            @foreach($categories as $c)
              <option value="{{ $c->id }}" {{ ($categoryFilter ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1.5">Stock ≤</label>
          <input type="number" name="stock" value="{{ $stock ?? '' }}" min="0" placeholder="Ej: 2"
            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"/>
        </div>
      </div>
      <div class="flex gap-2 mt-3">
        <button class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition">Buscar</button>
        @if(request()->hasAny(['stock','search','category']))
          <a href="{{ route('products.index') }}"
            class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-5 py-2.5 rounded-xl text-sm font-medium transition">
            Limpiar
          </a>
        @endif
      </div>
    </form>

    {{-- Tabla desktop --}}
    <div class="hidden sm:block bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-100">
            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Producto</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Cat.</th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Stock</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Costo</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Precio</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
        @foreach($products as $p)
          {{-- Fila vista --}}
          <tr id="row-view-{{ $p->id }}" class="hover:bg-slate-50 transition {{ $p->stock_qty == 0 ? 'bg-red-50' : ($p->stock_qty <= 2 ? 'bg-amber-50' : '') }}">
            <td class="px-5 py-3.5 font-medium text-slate-800">{{ $p->description }}</td>
            <td class="px-4 py-3.5 text-slate-500 text-xs">{{ $p->category->name ?? '—' }}</td>
            <td class="px-4 py-3.5 text-center">
              @if($p->stock_qty == 0)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">0 — Sin stock</span>
              @elseif($p->stock_qty <= 2)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">{{ $p->stock_qty }} — Bajo</span>
              @else
                <span class="inline-flex items-center justify-center w-8 h-6 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold">{{ $p->stock_qty }}</span>
              @endif
            </td>
            <td class="px-4 py-3.5 text-right text-slate-600">$ {{ number_format($p->unit_cost,0,',','.') }}</td>
            <td class="px-4 py-3.5 text-right font-semibold text-slate-800">$ {{ number_format($p->sale_price,0,',','.') }}</td>
            <td class="px-4 py-3.5 text-right">
              <div class="flex items-center justify-end gap-2">
                <button onclick="toggleEdit({{ $p->id }}, true)"
                  class="text-xs text-blue-600 hover:text-blue-800 font-medium px-2 py-1 rounded-lg hover:bg-blue-50 transition">Editar</button>
                <form method="POST" action="{{ route('products.destroy', $p) }}"
                  onsubmit="return confirm('¿Eliminar {{ addslashes($p->description) }}?')">
                  @csrf @method('DELETE')
                  <button class="text-xs text-red-400 hover:text-red-600 font-medium px-2 py-1 rounded-lg hover:bg-red-50 transition">Eliminar</button>
                </form>
              </div>
            </td>
          </tr>
          {{-- Fila edición --}}
          <tr id="row-edit-{{ $p->id }}" class="hidden bg-blue-50 border-l-4 border-blue-400">
            <td colspan="6" class="px-5 py-4">
              <form method="POST" action="{{ route('products.update', $p) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="category_id" value="{{ $p->category_id }}">
                <input type="hidden" name="barcode" value="{{ $p->barcode }}">
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-3">
                  <div class="sm:col-span-2">
                    <label class="block text-xs text-slate-500 mb-1">Descripción</label>
                    <input name="description" value="{{ $p->description }}" required
                      class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"/>
                  </div>
                  <div>
                    <label class="block text-xs text-slate-500 mb-1">Stock</label>
                    <input name="stock_qty" type="number" min="0" value="{{ $p->stock_qty }}" required
                      class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"/>
                  </div>
                  <div>
                    <label class="block text-xs text-slate-500 mb-1">Costo</label>
                    <input name="unit_cost" type="text" inputmode="decimal" value="{{ number_format($p->unit_cost,2) }}" required
                      class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 currency-input"/>
                  </div>
                  <div>
                    <label class="block text-xs text-slate-500 mb-1">Precio</label>
                    <input name="sale_price" type="text" inputmode="decimal" value="{{ number_format($p->sale_price,2) }}" required
                      class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 currency-input"/>
                  </div>
                </div>
                <div class="flex gap-2">
                  <button onclick="toggleEdit({{ $p->id }}, false)" type="button"
                    class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl text-sm text-slate-600 transition">Cancelar</button>
                  <button type="submit"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition">Guardar cambios</button>
                </div>
              </form>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>

    {{-- Cards móvil --}}
    <div class="sm:hidden space-y-3">
      @foreach($products as $p)
      <div class="bg-white rounded-2xl border shadow-sm overflow-hidden {{ $p->stock_qty == 0 ? 'border-red-300' : ($p->stock_qty <= 2 ? 'border-amber-300' : 'border-slate-100') }}"
           id="card-view-{{ $p->id }}">
        <div class="p-4">
          <div class="flex justify-between items-start mb-3">
            <div>
              <div class="font-semibold text-slate-800">{{ $p->description }}</div>
              <div class="text-xs text-slate-500 mt-0.5">{{ $p->category->name ?? '—' }}</div>
            </div>
            @if($p->stock_qty == 0)
              <span class="text-xs font-bold bg-red-100 text-red-700 px-2 py-0.5 rounded-full">Sin stock</span>
            @elseif($p->stock_qty <= 2)
              <span class="text-xs font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">Stock bajo</span>
            @endif
          </div>
          <div class="grid grid-cols-3 gap-2 mb-3">
            <div class="bg-slate-50 rounded-xl p-2.5 text-center">
              <div class="text-xs text-slate-400">Stock</div>
              <div class="font-bold text-slate-800">{{ $p->stock_qty }}</div>
            </div>
            <div class="bg-slate-50 rounded-xl p-2.5 text-center">
              <div class="text-xs text-slate-400">Costo</div>
              <div class="font-semibold text-slate-700 text-xs">$ {{ number_format($p->unit_cost,0,',','.') }}</div>
            </div>
            <div class="bg-slate-50 rounded-xl p-2.5 text-center">
              <div class="text-xs text-slate-400">Precio</div>
              <div class="font-bold text-slate-800 text-xs">$ {{ number_format($p->sale_price,0,',','.') }}</div>
            </div>
          </div>
          <div class="flex gap-2">
            <button onclick="toggleEditCard({{ $p->id }}, true)"
              class="flex-1 bg-blue-600 text-white py-2 rounded-xl text-sm font-semibold hover:bg-blue-700 transition">Editar</button>
            <form method="POST" action="{{ route('products.destroy', $p) }}" class="flex-1"
              onsubmit="return confirm('¿Eliminar?')">
              @csrf @method('DELETE')
              <button class="w-full bg-red-50 text-red-600 border border-red-200 py-2 rounded-xl text-sm font-semibold hover:bg-red-100 transition">Eliminar</button>
            </form>
          </div>
        </div>
      </div>
      <div class="hidden bg-blue-50 rounded-2xl border border-blue-200 shadow-sm p-4" id="card-edit-{{ $p->id }}">
        <form method="POST" action="{{ route('products.update', $p) }}" class="space-y-3">
          @csrf @method('PATCH')
          <input type="hidden" name="category_id" value="{{ $p->category_id }}">
          <input type="hidden" name="barcode" value="{{ $p->barcode }}">
          <div>
            <label class="block text-xs text-slate-500 mb-1">Descripción</label>
            <input name="description" value="{{ $p->description }}" required
              class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"/>
          </div>
          <div class="grid grid-cols-3 gap-2">
            <div>
              <label class="block text-xs text-slate-500 mb-1">Stock</label>
              <input name="stock_qty" type="number" min="0" value="{{ $p->stock_qty }}" required
                class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"/>
            </div>
            <div>
              <label class="block text-xs text-slate-500 mb-1">Costo</label>
              <input name="unit_cost" type="text" inputmode="decimal" value="{{ number_format($p->unit_cost,2) }}" required
                class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 currency-input"/>
            </div>
            <div>
              <label class="block text-xs text-slate-500 mb-1">Precio</label>
              <input name="sale_price" type="text" inputmode="decimal" value="{{ number_format($p->sale_price,2) }}" required
                class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 currency-input"/>
            </div>
          </div>
          <div class="flex gap-2">
            <button type="button" onclick="toggleEditCard({{ $p->id }}, false)"
              class="flex-1 bg-white border border-slate-200 py-2.5 rounded-xl text-sm text-slate-600">Cancelar</button>
            <button type="submit"
              class="flex-1 bg-blue-600 text-white py-2.5 rounded-xl text-sm font-semibold">Guardar</button>
          </div>
        </form>
      </div>
      @endforeach
    </div>

    <div class="mt-4">{{ $products->links() }}</div>
  </div>
</div>

{{-- ── Modal nueva categoría ── --}}
<div id="categoryModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
    <h3 class="text-base font-bold text-slate-800 mb-4">Nueva Categoría</h3>
    <form id="categoryForm" onsubmit="saveCategory(event)" class="space-y-4">
      <div>
        <label class="block text-xs font-medium text-slate-500 mb-1.5">Nombre de la categoría</label>
        <input id="categoryName" type="text" required placeholder="Ej: Smartphones, Accesorios..."
          class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"/>
      </div>
      <div id="categoryError" class="hidden bg-red-50 border border-red-200 text-red-700 px-3 py-2.5 rounded-xl text-sm"></div>
      <div id="categorySuccess" class="hidden bg-emerald-50 border border-emerald-200 text-emerald-700 px-3 py-2.5 rounded-xl text-sm"></div>
      <div class="flex gap-2 pt-1">
        <button type="submit"
          class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl text-sm font-semibold transition">Guardar</button>
        <button type="button" onclick="closeCategoryModal()"
          class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 py-2.5 rounded-xl text-sm font-medium transition">Cancelar</button>
      </div>
    </form>
  </div>
</div>

{{-- ── Modal gestión categorías ── --}}
<div id="manageCategoriesModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">
    <h3 class="text-base font-bold text-slate-800 mb-4">Gestionar Categorías</h3>
    <div id="categoriesList" class="divide-y divide-slate-100 border border-slate-100 rounded-xl max-h-72 overflow-y-auto mb-4">
      @foreach($categories as $c)
      <div class="flex items-center justify-between px-4 py-3 hover:bg-slate-50" data-category-id="{{ $c->id }}">
        <span class="text-sm font-medium text-slate-700">{{ $c->name }}</span>
        <button type="button" onclick="deleteCategory({{ $c->id }}, '{{ $c->name }}')"
          class="text-xs text-red-500 hover:text-red-700 font-medium px-2.5 py-1 rounded-lg hover:bg-red-50 transition">Eliminar</button>
      </div>
      @endforeach
    </div>
    <div id="manageError" class="hidden bg-red-50 border border-red-200 text-red-700 px-3 py-2.5 rounded-xl text-sm mb-3"></div>
    <div id="manageSuccess" class="hidden bg-emerald-50 border border-emerald-200 text-emerald-700 px-3 py-2.5 rounded-xl text-sm mb-3"></div>
    <button onclick="closeManageCategoriesModal()"
      class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 py-2.5 rounded-xl text-sm font-medium transition">Cerrar</button>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => { attachCurrencyInputs(); });

function toggleEdit(id, on) {
  document.getElementById('row-view-'+id).classList.toggle('hidden', on);
  document.getElementById('row-edit-'+id).classList.toggle('hidden', !on);
}
function toggleEditCard(id, on) {
  document.getElementById('card-view-'+id).classList.toggle('hidden', on);
  document.getElementById('card-edit-'+id).classList.toggle('hidden', !on);
}
function parseCurrencyToNumber(v) {
  return (v||'').toString().replace(/[^\d.,]/g,'').replace(/\./g,'').replace(',','.');
}
function attachCurrencyInputs() {
  document.querySelectorAll('input.currency-input').forEach(inp => {
    inp.addEventListener('focus', e => { e.target.value = parseCurrencyToNumber(e.target.value); });
    inp.addEventListener('blur', e => {
      let v = parseCurrencyToNumber(e.target.value);
      let n = parseFloat(v);
      if (!isNaN(n)) e.target.value = '$ ' + n.toLocaleString('es-CO');
    });
  });
  document.querySelectorAll('form').forEach(f => {
    f.addEventListener('submit', () => {
      f.querySelectorAll('input.currency-input').forEach(ci => { ci.value = parseCurrencyToNumber(ci.value); });
    });
  });
}
function openCategoryModal() {
  document.getElementById('categoryModal').classList.remove('hidden');
  document.getElementById('categoryName').value = '';
  document.getElementById('categoryError').classList.add('hidden');
  document.getElementById('categorySuccess').classList.add('hidden');
  document.getElementById('categoryName').focus();
}
function closeCategoryModal() { document.getElementById('categoryModal').classList.add('hidden'); }
function openManageCategoriesModal() {
  document.getElementById('manageCategoriesModal').classList.remove('hidden');
}
function closeManageCategoriesModal() { document.getElementById('manageCategoriesModal').classList.add('hidden'); }
document.getElementById('categoryModal').addEventListener('click', e => { if(e.target===e.currentTarget) closeCategoryModal(); });
document.getElementById('manageCategoriesModal').addEventListener('click', e => { if(e.target===e.currentTarget) closeManageCategoriesModal(); });

async function saveCategory(event) {
  event.preventDefault();
  const name = document.getElementById('categoryName').value.trim();
  const errorDiv = document.getElementById('categoryError');
  const successDiv = document.getElementById('categorySuccess');
  const btn = event.target.querySelector('button[type="submit"]');
  if (!name) { errorDiv.textContent='El nombre es requerido'; errorDiv.classList.remove('hidden'); return; }
  btn.disabled = true; btn.textContent = 'Guardando...';
  errorDiv.classList.add('hidden'); successDiv.classList.add('hidden');
  try {
    const r = await fetch('{{ route("categories.store") }}', {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
      body: JSON.stringify({name})
    });
    const data = await r.json();
    if (r.ok && data.success) {
      const sel = document.getElementById('category_select');
      sel.add(new Option(data.category.name, data.category.id, true, true), sel.options.length);
      successDiv.textContent = '✓ Categoría creada';
      successDiv.classList.remove('hidden');
      setTimeout(() => closeCategoryModal(), 900);
    } else {
      errorDiv.textContent = data.message || 'Error al crear'; errorDiv.classList.remove('hidden');
    }
  } catch(e) { errorDiv.textContent='Error de conexión'; errorDiv.classList.remove('hidden'); }
  finally { btn.disabled=false; btn.textContent='Guardar'; }
}
async function deleteCategory(id, name) {
  if (!confirm(`¿Eliminar la categoría "${name}"?`)) return;
  const errorDiv = document.getElementById('manageError');
  const successDiv = document.getElementById('manageSuccess');
  errorDiv.classList.add('hidden'); successDiv.classList.add('hidden');
  try {
    const r = await fetch(`/categories/${id}`, {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
      body: JSON.stringify({_method:'DELETE'})
    });
    const data = await r.json();
    if (r.ok && data.success) {
      document.querySelector(`#category_select option[value="${id}"]`)?.remove();
      document.querySelector(`[data-category-id="${id}"]`)?.remove();
      successDiv.textContent='✓ Categoría eliminada'; successDiv.classList.remove('hidden');
    } else { errorDiv.textContent=data.message||'Error'; errorDiv.classList.remove('hidden'); }
  } catch(e) { errorDiv.textContent='Error de conexión'; errorDiv.classList.remove('hidden'); }
}
</script>
@endsection
