@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <div>
      <p class="text-uppercase text-white-50 small mb-1">Контекст · МКС</p>
      <h3 class="fw-semibold mb-0">Положение и динамика МКС</h3>
      <div class="text-white-50">Вынесено на отдельную страницу: карта, графики тренда и последние измерения.</div>
    </div>
    <a class="btn btn-outline-light mt-3 mt-sm-0" href="/dashboard">К обзору</a>
  </div>

  <div class="row g-3">
    <div class="col-md-6">
      <div class="card card-animated h-100">
        <div class="card-body">
          <h5 class="card-title">Последний снимок</h5>
          @if(!empty($last['payload']))
            <ul class="list-group list-group-flush">
              <li class="list-group-item bg-transparent text-white-50">Широта <span class="text-white">{{ $last['payload']['latitude'] ?? '—' }}</span></li>
              <li class="list-group-item bg-transparent text-white-50">Долгота <span class="text-white">{{ $last['payload']['longitude'] ?? '—' }}</span></li>
              <li class="list-group-item bg-transparent text-white-50">Высота, км <span class="text-white">{{ $last['payload']['altitude'] ?? '—' }}</span></li>
              <li class="list-group-item bg-transparent text-white-50">Скорость, км/ч <span class="text-white">{{ $last['payload']['velocity'] ?? '—' }}</span></li>
              <li class="list-group-item bg-transparent text-white-50">Время <span class="text-white">{{ $last['fetched_at'] ?? '—' }}</span></li>
            </ul>
          @else
            <div class="text-white-50">нет данных</div>
          @endif
          <div class="mt-3 small text-white-50"><code>{{ $base }}/last</code></div>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card card-animated h-100">
        <div class="card-body">
          <h5 class="card-title">Тренд движения</h5>
          @if(!empty($trend))
            <ul class="list-group list-group-flush">
              <li class="list-group-item bg-transparent text-white-50">Движение <span class="text-white">{{ ($trend['movement'] ?? false) ? 'да' : 'нет' }}</span></li>
              <li class="list-group-item bg-transparent text-white-50">Смещение, км <span class="text-white">{{ number_format($trend['delta_km'] ?? 0, 3, '.', ' ') }}</span></li>
              <li class="list-group-item bg-transparent text-white-50">Интервал, сек <span class="text-white">{{ $trend['dt_sec'] ?? 0 }}</span></li>
              <li class="list-group-item bg-transparent text-white-50">Скорость, км/ч <span class="text-white">{{ $trend['velocity_kmh'] ?? '—' }}</span></li>
            </ul>
          @else
            <div class="text-white-50">нет данных</div>
          @endif
          <div class="mt-3 small text-white-50"><code>{{ $base }}/iss/trend</code></div>
          <div class="mt-3"><a class="btn btn-outline-light" href="/osdr">Перейти к OSDR</a></div>
        </div>
      </div>
    </div>
  </div>

  <div class="card card-animated mt-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-lg-7">
          <h5 class="card-title">Карта траектории</h5>
          <div id="map" class="rounded border" style="height:340px"></div>
        </div>
        <div class="col-lg-5">
          <h5 class="card-title">Графики</h5>
          <div class="row g-3">
            <div class="col-12">
              <canvas id="issSpeedChart" height="120"></canvas>
            </div>
            <div class="col-12">
              <canvas id="issAltChart" height="120"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async function () {
  if (typeof L === 'undefined' || typeof Chart === 'undefined') return;
  const last = @json(($last['payload'] ?? []));
  let lat0 = Number(last.latitude || 0), lon0 = Number(last.longitude || 0);
  const map = L.map('map', { attributionControl:false }).setView([lat0||0, lon0||0], lat0?3:2);
  L.tileLayer('https://{s}.tile.openstreetmap.de/{z}/{x}/{y}.png', { noWrap:true }).addTo(map);
  const trail  = L.polyline([], {weight:3}).addTo(map);
  const marker = L.marker([lat0||0, lon0||0]).addTo(map).bindPopup('МКС');

  const speedChart = new Chart(document.getElementById('issSpeedChart'), {
    type: 'line', data: { labels: [], datasets: [{ label: 'Скорость', data: [], borderColor:'#8fc5ff', tension:.35 }] },
    options: { responsive: true, scales: { x: { display: false }, y:{ ticks:{ color:'#cfd8e3' } } }, plugins:{ legend:{ labels:{ color:'#cfd8e3' } } } }
  });
  const altChart = new Chart(document.getElementById('issAltChart'), {
    type: 'line', data: { labels: [], datasets: [{ label: 'Высота', data: [], borderColor:'#6ee7ff', tension:.35 }] },
    options: { responsive: true, scales: { x: { display: false }, y:{ ticks:{ color:'#cfd8e3' } } }, plugins:{ legend:{ labels:{ color:'#cfd8e3' } } } }
  });

  async function loadTrend() {
    try {
      const r = await fetch('/api/iss/trend?limit=240');
      const js = await r.json();
      const pts = Array.isArray(js.points) ? js.points.map(p => [p.lat, p.lon]) : [];
      if (pts.length) {
        trail.setLatLngs(pts);
        marker.setLatLng(pts[pts.length-1]);
      }
      const t = (js.points||[]).map(p => new Date(p.at).toLocaleTimeString());
      speedChart.data.labels = t;
      speedChart.data.datasets[0].data = (js.points||[]).map(p => p.velocity);
      speedChart.update();
      altChart.data.labels = t;
      altChart.data.datasets[0].data = (js.points||[]).map(p => p.altitude);
      altChart.update();
    } catch(e) {}
  }
  loadTrend();
  setInterval(loadTrend, 15000);
});
</script>
@endsection
