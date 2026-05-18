<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title>StoreCell — Iniciar sesión</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    body { font-family: 'Inter', sans-serif; }
    .gradient-bg {
      background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%);
    }
    .input-field {
      transition: all 0.2s;
    }
    .input-field:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
    }
    .btn-login {
      background: linear-gradient(135deg, #1d4ed8, #1e40af);
      transition: all 0.2s;
    }
    .btn-login:hover {
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
      transform: translateY(-1px);
      box-shadow: 0 4px 15px rgba(29,78,216,0.4);
    }
    @media (max-width: 640px) { input, button { font-size: 16px !important; } }
  </style>
</head>
<body class="min-h-screen flex">

  {{-- Panel izquierdo decorativo --}}
  <div class="hidden lg:flex lg:w-1/2 gradient-bg flex-col items-center justify-center p-12 relative overflow-hidden">
    {{-- Círculos decorativos --}}
    <div class="absolute top-0 left-0 w-64 h-64 bg-blue-500 rounded-full opacity-5 -translate-x-1/2 -translate-y-1/2"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-blue-400 rounded-full opacity-5 translate-x-1/3 translate-y-1/3"></div>

    @php $storeName = \App\Models\Setting::get('store_name', 'StoreCell'); @endphp

    <div class="relative z-10 text-center">
      <div class="w-20 h-20 bg-blue-500 bg-opacity-20 rounded-2xl flex items-center justify-center mx-auto mb-6 border border-blue-400 border-opacity-30">
        <svg class="w-10 h-10 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
      </div>
      <h1 class="text-4xl font-bold text-white mb-3">{{ $storeName }}</h1>
      <p class="text-blue-200 text-lg mb-10">Sistema de Punto de Venta</p>

      <div class="grid grid-cols-3 gap-6 text-center">
        <div class="bg-white bg-opacity-5 rounded-xl p-4 border border-white border-opacity-10">
          <div class="text-2xl font-bold text-white">POS</div>
          <div class="text-blue-300 text-xs mt-1">Ventas</div>
        </div>
        <div class="bg-white bg-opacity-5 rounded-xl p-4 border border-white border-opacity-10">
          <div class="text-2xl font-bold text-white">📦</div>
          <div class="text-blue-300 text-xs mt-1">Inventario</div>
        </div>
        <div class="bg-white bg-opacity-5 rounded-xl p-4 border border-white border-opacity-10">
          <div class="text-2xl font-bold text-white">🔧</div>
          <div class="text-blue-300 text-xs mt-1">Reparaciones</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Panel derecho — formulario --}}
  <div class="w-full lg:w-1/2 flex items-center justify-center bg-gray-50 p-6 sm:p-12">
    <div class="w-full max-w-md">

      {{-- Logo móvil --}}
      <div class="lg:hidden text-center mb-8">
        @php $logo = \App\Models\Setting::get('logo_path'); @endphp
        @if($logo)
          <img src="{{ asset('storage/' . $logo) }}" alt="{{ $storeName }}" class="h-14 mx-auto mb-3 object-contain"/>
        @else
          <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
          </div>
        @endif
        <h1 class="text-xl font-bold text-gray-900">{{ $storeName }}</h1>
      </div>

      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
        <div class="mb-7">
          <h2 class="text-2xl font-bold text-gray-900">Bienvenido</h2>
          <p class="text-gray-500 text-sm mt-1">Ingresa tus credenciales para continuar</p>
        </div>

        @error('email')
          <div class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 p-3 rounded-lg mb-5 text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $message }}
          </div>
        @enderror

        <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
          @csrf
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Correo electrónico</label>
            <input
              name="email"
              type="email"
              class="input-field w-full border border-gray-300 rounded-xl px-4 py-3 text-sm text-gray-900 bg-white outline-none"
              value="{{ old('email') }}"
              placeholder="correo@ejemplo.com"
              required
              autocomplete="email"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Contraseña</label>
            <input
              name="password"
              type="password"
              class="input-field w-full border border-gray-300 rounded-xl px-4 py-3 text-sm text-gray-900 bg-white outline-none"
              placeholder="••••••••"
              required
              autocomplete="current-password"
            />
          </div>
          <button type="submit" class="btn-login w-full text-white py-3.5 rounded-xl font-semibold text-sm mt-2">
            Iniciar sesión
          </button>
        </form>
      </div>

      <p class="text-center text-xs text-gray-400 mt-6">{{ $storeName }} &copy; {{ date('Y') }}</p>
    </div>
  </div>

</body>
</html>
