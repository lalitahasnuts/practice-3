@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="row align-items-center mb-4">
    <div class="col-lg-8">
      <p class="text-uppercase text-white-50 small mb-1">Центр управления</p>
      <h2 class="fw-semibold mb-2">Космические данные по контекстам</h2>
      <p class="text-white-50 mb-0">Каждая бизнес-функция вынесена в отдельный экран: МКС, JWST, события астрономии и данные OSDR. Нужное открывается в один клик.</p>
    </div>
    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
      <a class="btn btn-primary btn-lg" href="/jwst">Открыть галерею JWST</a>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-6 col-lg-3">
      <div class="card card-animated p-3 h-100">
        <div class="small text-white-50 mb-1">МКС — скорость</div>
        <div class="display-6 fw-semibold">{{ isset(($iss['payload'] ?? [])['velocity']) ? number_format($iss['payload']['velocity'],0,'',' ') : '—' }}</div>
        <div class="text-white-50">км/ч · <a href="/iss">детали</a></div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card card-animated p-3 h-100">
        <div class="small text-white-50 mb-1">МКС — высота</div>
        <div class="display-6 fw-semibold">{{ isset(($iss['payload'] ?? [])['altitude']) ? number_format($iss['payload']['altitude'],0,'',' ') : '—' }}</div>
        <div class="text-white-50">км · <a href="/iss">карта</a></div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card card-animated p-3 h-100">
        <div class="small text-white-50 mb-1">JWST</div>
        <div class="display-6 fw-semibold">Галерея</div>
        <div class="text-white-50">фильтры, поиск · <a href="/jwst">перейти</a></div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card card-animated p-3 h-100">
        <div class="small text-white-50 mb-1">OSDR</div>
        <div class="display-6 fw-semibold">{{ count($iss['payload'] ?? []) ? 'Готово' : 'Онлайн' }}</div>
        <div class="text-white-50">сортировки, поиск · <a href="/osdr">открыть</a></div>
      </div>
    </div>
  </div>

  <div class="row g-3 mt-2">
    <div class="col-lg-6">
      <div class="card card-animated h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h5 class="mb-1">МКС · краткая сводка</h5>
              <div class="text-white-50">Обновлено: {{ $iss['fetched_at'] ?? '—' }}</div>
            </div>
            <a class="btn btn-outline-light btn-sm" href="/iss">Детали</a>
          </div>
          <div class="mt-3">
            <div class="d-flex justify-content-between text-white-50 mb-1">
              <span>Высота</span><span>{{ $iss['payload']['altitude'] ?? '—' }} км</span>
            </div>
            <div class="progress bg-dark-subtle" style="height:6px">
              <div class="progress-bar" role="progressbar" style="width: {{ min(100, max(0, ($iss['payload']['altitude'] ?? 0)/500*100)) }}%"></div>
            </div>
            <div class="d-flex justify-content-between text-white-50 mt-3 mb-1">
              <span>Скорость</span><span>{{ $iss['payload']['velocity'] ?? '—' }} км/ч</span>
            </div>
            <div class="progress bg-dark-subtle" style="height:6px">
              <div class="progress-bar bg-info" role="progressbar" style="width: {{ min(100, max(0, ($iss['payload']['velocity'] ?? 0)/30000*100)) }}%"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card card-animated h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h5 class="mb-1">Активности</h5>
              <div class="text-white-50">Выбирайте нужный контекст</div>
            </div>
          </div>
          <div class="row g-2 mt-2">
            <div class="col-6">
              <a class="btn btn-outline-light w-100" href="/jwst">JWST галерея</a>
            </div>
            <div class="col-6">
              <a class="btn btn-outline-light w-100" href="/astro">Астро события</a>
            </div>
            <div class="col-6">
              <a class="btn btn-outline-light w-100" href="/iss">МКС карта</a>
            </div>
            <div class="col-6">
              <a class="btn btn-outline-light w-100" href="/osdr">OSDR данные</a>
            </div>
          </div>
          <div class="text-white-50 small mt-3">Все модули разнесены по страницам, чтобы интерфейс не конфликтовал и не перегружался.</div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
