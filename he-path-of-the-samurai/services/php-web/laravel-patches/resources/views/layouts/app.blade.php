<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Space Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <style>
    :root {
      --brand-primary: #3c6ff8;
      --brand-surface: #0b1224;
    }
    body {
      background: radial-gradient(circle at 20% 20%, rgba(60,111,248,.08), transparent 20%),
                  radial-gradient(circle at 80% 10%, rgba(126,229,255,.08), transparent 18%),
                  #0a0e1a;
      color: #e9eef5;
      min-height: 100vh;
    }
    .navbar {
      background: rgba(13,18,32,.92) !important;
      backdrop-filter: blur(8px);
      border-bottom: 1px solid rgba(255,255,255,.06);
    }
    .card {
      background: rgba(22,29,46,.92);
      border: 1px solid rgba(255,255,255,.05);
      color: #e9eef5;
      box-shadow: 0 8px 32px rgba(0,0,0,.35);
    }
    .table {
      color: #e9eef5;
    }
    .table thead {
      background: rgba(255,255,255,.05);
    }
    a { color: #8fc5ff; }
    a:hover { color: #b7dbff; }
    #map{height:340px}
    .card-animated {
      transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    }
    .card-animated:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 36px rgba(0,0,0,.45);
      border-color: rgba(143,197,255,.6);
    }
    .fade-in {
      animation: fade-in .4s ease-in;
    }
    @keyframes fade-in {
      from { opacity: 0; transform: translateY(6px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark mb-3">
  <div class="container">
    <a class="navbar-brand fw-semibold text-white" href="/dashboard">SpaceDash</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <div class="navbar-nav ms-auto">
        <a class="nav-link text-white-50" href="/dashboard">Обзор</a>
        <a class="nav-link text-white-50" href="/iss">МКС</a>
        <a class="nav-link text-white-50" href="/jwst">JWST</a>
        <a class="nav-link text-white-50" href="/astro">Астро события</a>
        <a class="nav-link text-white-50" href="/osdr">OSDR</a>
      </div>
    </div>
  </div>
</nav>
<main class="pb-5">@yield('content')</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
