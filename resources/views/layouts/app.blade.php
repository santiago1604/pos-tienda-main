<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title>StoreCell POS</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    *, *::before, *::after { box-sizing: border-box; }
    body { font-family: 'Inter', sans-serif; margin: 0; background: #f1f5f9; }

    /* ── Sidebar ─────────────────────────────────────────────── */
    .sidebar {
      position: fixed; top: 0; left: 0;
      width: 240px; height: 100vh;
      background: #0f172a;
      display: flex; flex-direction: column;
      z-index: 50;
      transition: transform 0.25s cubic-bezier(.4,0,.2,1);
      overflow: hidden;
    }
    .sidebar-logo {
      display: flex; align-items: center; gap: 10px;
      padding: 20px 20px 16px;
      border-bottom: 1px solid rgba(255,255,255,.07);
    }
    .sidebar-logo-icon {
      width: 36px; height: 36px; border-radius: 10px;
      background: linear-gradient(135deg,#3b82f6,#1d4ed8);
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
    }
    .sidebar-logo-name {
      font-size: 15px; font-weight: 700;
      color: #fff; line-height: 1.2;
    }
    .sidebar-logo-sub {
      font-size: 10px; color: #64748b; font-weight: 500;
      text-transform: uppercase; letter-spacing: .06em;
    }

    /* Nav groups */
    .nav-group { padding: 12px 12px 4px; }
    .nav-group-label {
      font-size: 10px; font-weight: 600; color: #475569;
      text-transform: uppercase; letter-spacing: .08em;
      padding: 0 8px; margin-bottom: 4px;
    }
    .nav-item {
      display: flex; align-items: center; gap: 10px;
      padding: 9px 12px; border-radius: 10px;
      font-size: 13.5px; font-weight: 500; color: #94a3b8;
      text-decoration: none; transition: all .15s;
      margin-bottom: 2px; cursor: pointer;
    }
    .nav-item svg { flex-shrink: 0; opacity: .7; transition: opacity .15s; }
    .nav-item:hover { background: rgba(255,255,255,.06); color: #e2e8f0; }
    .nav-item:hover svg { opacity: 1; }
    .nav-item.active {
      background: rgba(59,130,246,.18); color: #60a5fa;
    }
    .nav-item.active svg { opacity: 1; }
    .nav-item.active-indicator {
      position: relative;
    }
    .nav-item.active::before {
      content: ''; position: absolute; left: 0; top: 50%;
      transform: translateY(-50%);
      width: 3px; height: 60%; background: #3b82f6;
      border-radius: 0 3px 3px 0;
    }
    .nav-item.nav-danger { color: #f87171; }
    .nav-item.nav-danger:hover { background: rgba(248,113,113,.1); color: #fca5a5; }

    /* Sidebar footer */
    .sidebar-footer {
      margin-top: auto;
      padding: 12px;
      border-top: 1px solid rgba(255,255,255,.07);
    }
    .user-card {
      display: flex; align-items: center; gap: 10px;
      padding: 10px 12px; border-radius: 12px;
      background: rgba(255,255,255,.05);
    }
    .user-avatar {
      width: 34px; height: 34px; border-radius: 50%;
      background: linear-gradient(135deg,#3b82f6,#6366f1);
      display: flex; align-items: center; justify-content: center;
      font-size: 13px; font-weight: 700; color: #fff;
      flex-shrink: 0;
    }
    .user-name { font-size: 13px; font-weight: 600; color: #e2e8f0; line-height: 1.3; }
    .user-role {
      font-size: 10px; font-weight: 600; padding: 1px 7px;
      border-radius: 20px; display: inline-block; margin-top: 2px;
    }
    .role-admin      { background: rgba(59,130,246,.2);  color: #93c5fd; }
    .role-seller     { background: rgba(16,185,129,.2);  color: #6ee7b7; }
    .role-technician { background: rgba(245,158,11,.2);  color: #fcd34d; }

    /* ── Top bar ─────────────────────────────────────────────── */
    .topbar {
      position: fixed; top: 0; left: 240px; right: 0; height: 60px;
      background: rgba(241,245,249,.9);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid #e2e8f0;
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 24px; z-index: 40;
      transition: left .25s cubic-bezier(.4,0,.2,1);
    }
    .topbar-title { font-size: 16px; font-weight: 600; color: #0f172a; }
    .topbar-time { font-size: 12px; color: #64748b; font-weight: 500; }

    /* ── Main content ────────────────────────────────────────── */
    .main-wrap {
      margin-left: 240px;
      padding: 84px 24px 32px;
      min-height: 100vh;
      transition: margin-left .25s cubic-bezier(.4,0,.2,1);
    }

    /* ── Alert banners ───────────────────────────────────────── */
    .alert {
      display: flex; align-items: flex-start; gap: 10px;
      padding: 12px 16px; border-radius: 12px;
      font-size: 13.5px; margin-bottom: 16px;
    }
    .alert-ok  { background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; }
    .alert-err { background:#fef2f2; border:1px solid #fecaca; color:#991b1b; }

    /* ── Overlay mobile ──────────────────────────────────────── */
    .overlay {
      display: none; position: fixed; inset: 0;
      background: rgba(0,0,0,.5); z-index: 45;
      backdrop-filter: blur(2px);
    }
    .overlay.open { display: block; }

    /* ── Mobile hamburger ────────────────────────────────────── */
    .hamburger {
      display: none; align-items: center; justify-content: center;
      width: 38px; height: 38px; border-radius: 10px;
      background: #fff; border: 1px solid #e2e8f0;
      cursor: pointer; flex-shrink: 0;
      transition: background .15s;
    }
    .hamburger:hover { background: #f8fafc; }

    /* ── Scrollbar sidebar ───────────────────────────────────── */
    .sidebar-scroll {
      flex: 1; overflow-y: auto; overflow-x: hidden;
    }
    .sidebar-scroll::-webkit-scrollbar { width: 4px; }
    .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
    .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 4px; }

    /* ── Responsive ──────────────────────────────────────────── */
    @media (max-width: 768px) {
      .sidebar { transform: translateX(-100%); }
      .sidebar.open { transform: translateX(0); }
      .topbar { left: 0; padding: 0 16px; gap: 12px; }
      .main-wrap { margin-left: 0; padding: 76px 12px 24px; }
      .hamburger { display: flex; }
    }

    @media (max-width: 640px) {
      input, select, textarea, button { font-size: 16px !important; }
    }
  </style>
</head>
<body>

@php
  $logo      = \App\Models\Setting::get('logo_path');
  $storeName = \App\Models\Setting::get('store_name', 'StoreCell');
  $route     = request()->route()?->getName() ?? '';
  $pageTitle = match(true) {
    str_starts_with($route,'pos')      => 'Punto de Venta',
    str_starts_with($route,'orders')   => 'Pedidos',
    str_starts_with($route,'repairs')  => 'Reparaciones',
    str_starts_with($route,'products') => 'Productos',
    str_starts_with($route,'cash')     => 'Caja',
    str_starts_with($route,'dashboard')=> 'Dashboard',
    str_starts_with($route,'settings') => 'Configuración',
    str_starts_with($route,'admin')    => 'Administración',
    default                            => $storeName,
  };
@endphp

@auth

{{-- ═══ SIDEBAR ═══ --}}
<aside class="sidebar" id="sidebar">

  {{-- Logo --}}
  <div class="sidebar-logo">
    @if($logo)
      <img src="{{ asset('storage/'.$logo) }}" alt="{{ $storeName }}" style="height:36px;object-fit:contain;max-width:160px"/>
    @else
      <div class="sidebar-logo-icon">
        <svg width="18" height="18" fill="none" stroke="#fff" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
      </div>
      <div>
        <div class="sidebar-logo-name">{{ $storeName }}</div>
        <div class="sidebar-logo-sub">Sistema POS</div>
      </div>
    @endif
  </div>

  {{-- Nav links --}}
  <nav class="sidebar-scroll">

    {{-- General --}}
    <div class="nav-group">
      <div class="nav-group-label">General</div>

      <a href="{{ route('pos.index') }}"
         class="nav-item active-indicator {{ str_starts_with($route,'pos') ? 'active' : '' }}">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 7H6a2 2 0 00-2 2v9a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1-4H9m1 4V3"/>
        </svg>
        Punto de Venta
      </a>

      @if(in_array(auth()->user()->role, ['seller','admin']))
      <a href="{{ route('orders.index') }}"
         class="nav-item active-indicator {{ str_starts_with($route,'orders') ? 'active' : '' }}">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
        </svg>
        Pedidos
      </a>
      @endif

      @if(in_array(auth()->user()->role, ['admin','technician']))
      <a href="{{ route('repairs.index') }}"
         class="nav-item active-indicator {{ str_starts_with($route,'repairs') ? 'active' : '' }}">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        Reparaciones
      </a>
      @endif
    </div>

    @if(auth()->user()->role === 'admin')
    {{-- Administración --}}
    <div class="nav-group">
      <div class="nav-group-label">Administración</div>

      <a href="{{ route('products.index') }}"
         class="nav-item active-indicator {{ str_starts_with($route,'products') ? 'active' : '' }}">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        Productos
      </a>

      <a href="{{ route('cash.open') }}"
         class="nav-item active-indicator {{ str_starts_with($route,'cash') ? 'active' : '' }}">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        Caja
      </a>

      <a href="{{ route('dashboard') }}"
         class="nav-item active-indicator {{ str_starts_with($route,'dashboard') ? 'active' : '' }}">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        Dashboard
      </a>

      <a href="{{ route('admin.users.index') }}"
         class="nav-item active-indicator {{ str_starts_with($route,'admin') ? 'active' : '' }}">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        Usuarios
      </a>

      <a href="{{ route('settings.index') }}"
         class="nav-item active-indicator {{ str_starts_with($route,'settings') ? 'active' : '' }}">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
        </svg>
        Configuración
      </a>
    </div>
    @endif

  </nav>

  {{-- Footer del sidebar --}}
  <div class="sidebar-footer">
    <div class="user-card">
      <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
      <div style="flex:1;min-width:0">
        <div class="user-name" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
          {{ auth()->user()->name }}
        </div>
        <span class="user-role role-{{ auth()->user()->role }}">{{ auth()->user()->role }}</span>
      </div>
      <form method="POST" action="{{ route('logout') }}" style="flex-shrink:0">
        @csrf
        <button type="submit" title="Cerrar sesión" style="background:none;border:none;cursor:pointer;padding:4px;border-radius:6px;display:flex;align-items:center">
          <svg width="16" height="16" fill="none" stroke="#f87171" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
          </svg>
        </button>
      </form>
    </div>
  </div>
</aside>

{{-- Overlay móvil --}}
<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

{{-- ═══ TOPBAR ═══ --}}
<header class="topbar">
  <div style="display:flex;align-items:center;gap:12px">
    <button class="hamburger" onclick="openSidebar()" aria-label="Menú">
      <svg width="18" height="18" fill="none" stroke="#374151" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>
    <div>
      <div class="topbar-title">{{ $pageTitle }}</div>
    </div>
  </div>
  <div class="topbar-time" id="live-time"></div>
</header>

@endauth

{{-- ═══ CONTENT ═══ --}}
<main class="{{ auth()->check() ? 'main-wrap' : '' }}">

  @if(session('status') || session('ok'))
  <div class="alert alert-ok">
    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" style="flex-shrink:0;margin-top:1px">
      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
    </svg>
    {{ session('status') ?? session('ok') }}
  </div>
  @endif

  @if(session('err') || session('error'))
  <div class="alert alert-err">
    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" style="flex-shrink:0;margin-top:1px">
      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
    </svg>
    {{ session('err') ?? session('error') }}
  </div>
  @endif

  @yield('content')
</main>

<script>
  // Sidebar móvil
  function openSidebar()  {
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('overlay').classList.add('open');
  }
  function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('overlay').classList.remove('open');
  }
  document.querySelectorAll('.sidebar a').forEach(a => {
    a.addEventListener('click', () => closeSidebar());
  });

  // Reloj en vivo
  function tick() {
    const el = document.getElementById('live-time');
    if (!el) return;
    const now = new Date();
    const days = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
    const months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    const h = String(now.getHours()).padStart(2,'0');
    const m = String(now.getMinutes()).padStart(2,'0');
    const s = String(now.getSeconds()).padStart(2,'0');
    el.textContent = `${days[now.getDay()]} ${now.getDate()} ${months[now.getMonth()]} · ${h}:${m}:${s}`;
  }
  tick(); setInterval(tick, 1000);
</script>

@yield('scripts')
</body>
</html>
