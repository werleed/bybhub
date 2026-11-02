<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title','BYBHUB') — Bringing China Closer to You</title>
<meta name="description" content="BYBHUB — Buy direct from China. Delivered to Nigeria, Ghana and Niger.">

<link rel="icon" type="image/png" href="{{ asset('images/icon-192.png') }}">
<link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
<meta name="theme-color" content="#1A3A6B">
<link rel="manifest" href="{{ asset('manifest.json') }}">

<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary:       { DEFAULT:'#1A3A6B', dark:'#0F2548', light:'#2451A3' },
                accent:        { DEFAULT:'#F47B20', dark:'#D4660E' },
            },
            fontFamily: {
                sans: ['Inter','ui-sans-serif','system-ui'],
                display: ['Poppins','Inter','ui-sans-serif'],
            },
        }
    }
}
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Poppins:wght@700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<style>
  body { font-family: 'Inter', sans-serif; margin: 0; min-height: 100vh; }
  .auth-bg {
    background: linear-gradient(160deg, #0A1628 0%, #1A3A6B 55%, #2451A3 100%);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }
  .auth-blob-1 {
    position: fixed; top: -10%; right: -10%; width: 400px; height: 400px;
    border-radius: 50%; pointer-events: none; z-index: 0;
    background: radial-gradient(circle, rgba(244,123,32,0.12), transparent 70%);
  }
  .auth-blob-2 {
    position: fixed; bottom: -10%; left: -10%; width: 350px; height: 350px;
    border-radius: 50%; pointer-events: none; z-index: 0;
    background: radial-gradient(circle, rgba(53,96,223,0.15), transparent 70%);
  }
  input:focus { outline: none; }
  .accent-primary { accent-color: #1A3A6B; }
  .accent-accent  { accent-color: #F47B20; }
  @keyframes fadeUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
  .anim-fade-up { animation: fadeUp 0.5s ease forwards; }
</style>
</head>
<body class="auth-bg">

<div class="auth-blob-1"></div>
<div class="auth-blob-2"></div>

{{-- ── Main content ─────────────────────────────────────────────────────── --}}
<main class="relative z-10 flex-1 flex flex-col items-center justify-center px-4 py-6 sm:py-8">
    @yield('content')
    <p class="text-center text-blue-300/30 text-[10px] mt-6" style="padding-bottom:max(env(safe-area-inset-bottom,0px),16px)">&copy; {{ date('Y') }} BYBHUB &middot; bybimport.com</p>
</main>

</body>
</html>
