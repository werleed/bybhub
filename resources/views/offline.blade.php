<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#1A3A6B">
<title>You're Offline — BYBHUB</title>
<link rel="icon" type="image/jpeg" href="/images/logo.jpg">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: {
    extend: {
      colors: {
        primary: { DEFAULT: '#1A3A6B', dark: '#112548' },
        accent:  { DEFAULT: '#F47B20', dark: '#D4660E' }
      }
    }
  }
}
</script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@700;800;900&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;-webkit-font-smoothing:antialiased}
body{font-family:'Inter',sans-serif;background:#F0F4F8;margin:0}
h1,h2{font-family:'Poppins',sans-serif}
</style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center px-4 py-16 text-center">

  <!-- Logo -->
  <a href="/" class="mb-8 inline-block">
    <img src="/images/logo.jpg" alt="BYBHUB"
         style="height:48px;width:auto;border-radius:12px;background:white;padding:4px 10px;box-shadow:0 2px 12px rgba(26,58,107,0.12);display:block">
  </a>

  <!-- Wifi-off icon (inline SVG — no CDN dependency) -->
  <div class="w-24 h-24 rounded-3xl flex items-center justify-center mx-auto mb-6 bg-gray-100">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
         stroke="#9CA3AF" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"
         width="48" height="48" aria-hidden="true">
      <line x1="1" y1="1" x2="23" y2="23"></line>
      <path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"></path>
      <path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"></path>
      <path d="M10.71 5.05A16 16 0 0 1 22.56 9"></path>
      <path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88"></path>
      <path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path>
      <line x1="12" y1="20" x2="12.01" y2="20"></line>
    </svg>
  </div>

  <h1 class="text-3xl font-black mb-3" style="color:#1A3A6B">
    You're Offline
  </h1>
  <p class="text-gray-500 max-w-sm mb-8 leading-relaxed text-sm">
    It looks like you've lost your internet connection. Check your network and try again — BYBHUB will be right here when you're back.
  </p>

  <div class="flex flex-wrap gap-3 justify-center">
    <button onclick="window.location.reload()"
        class="inline-flex items-center gap-2 text-white font-bold px-6 py-3 rounded-xl text-sm transition hover:opacity-90"
        style="background:#1A3A6B">
      <!-- redo icon -->
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"
           width="14" height="14" aria-hidden="true">
        <polyline points="1 4 1 10 7 10"></polyline>
        <path d="M3.51 15a9 9 0 1 0 .49-4.95"></path>
      </svg>
      Try Again
    </button>
    <a href="/"
        class="inline-flex items-center gap-2 border-2 border-gray-200 text-gray-600 font-bold px-6 py-3 rounded-xl text-sm hover:bg-gray-50 transition">
      <!-- home icon -->
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"
           width="14" height="14" aria-hidden="true">
        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
        <polyline points="9 22 9 12 15 12 15 22"></polyline>
      </svg>
      Go Home
    </a>
  </div>

  <p class="text-xs text-gray-400 mt-8">
    Some previously visited pages are available offline from cache.
  </p>

</body>
</html>
