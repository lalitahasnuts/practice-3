@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <div>
      <p class="text-uppercase text-white-50 small mb-1">Контекст · OSDR</p>
      <h3 class="fw-semibold mb-0">Дашборд NASA OSDR</h3>
      <div class="text-white-50">Фильтрация по дате, столбцу, сортировка вверх/вниз и поиск по ключевым словам в одном запросе.</div>
    </div>
    <a class="btn btn-outline-light mt-3 mt-sm-0" href="/dashboard">К обзору</a>
  </div>

  <div class="card card-animated mb-3">
    <div class="card-body">
      <div class="row g-3 align-items-end">
        <div class="col-lg-3">
          <label class="form-label small text-white-50 mb-1">Ключевые слова</label>
          <input type="text" class="form-control form-control-sm" id="kwInput" placeholder="dataset_id, title, url">
        </div>
        <div class="col-lg-3">
          <label class="form-label small text-white-50 mb-1">Сортировка</label>
          <div class="d-flex gap-2">
            <select class="form-select form-select-sm" id="sortColumn">
              <option value="updated_at">updated_at</option>
              <option value="inserted_at">inserted_at</option>
              <option value="dataset_id">dataset_id</option>
              <option value="title">title</option>
            </select>
            <select class="form-select form-select-sm" id="sortDir" style="width:110px">
              <option value="desc">По убыванию</option>
              <option value="asc">По возрастанию</option>
            </select>
          </div>
        </div>
        <div class="col-lg-4">
          <label class="form-label small text-white-50 mb-1">Диапазон дат</label>
          <div class="d-flex gap-2">
            <select class="form-select form-select-sm" id="dateColumn" style="width:140px">
              <option value="updated_at">updated_at</option>
              <option value="inserted_at">inserted_at</option>
            </select>
            <input type="date" class="form-control form-control-sm" id="dateFrom">
            <input type="date" class="form-control form-control-sm" id="dateTo">
          </div>
        </div>
        <div class="col-lg-2 d-flex gap-2">
          <button class="btn btn-sm btn-primary flex-fill" id="applyBtn">Применить</button>
          <button class="btn btn-sm btn-outline-light" id="resetBtn" title="Сбросить фильтры">Сброс</button>
        </div>
      </div>
      <div class="text-white-50 small mt-2">Источник: {{ $src }}</div>
    </div>
  </div>

  <div class="card card-animated">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="card-title m-0">Наборы данных</h5>
        <div class="small text-white-50">Найдено: <span id="osdrCount">{{ count($items) }}</span></div>
      </div>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>dataset_id</th>
              <th>title</th>
              <th>REST_URL</th>
              <th>updated_at</th>
              <th>inserted_at</th>
              <th>raw</th>
            </tr>
          </thead>
          <tbody id="osdrBody"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const items = @json($items);
  const body = document.getElementById('osdrBody');
  const countEl = document.getElementById('osdrCount');
  const kwInput = document.getElementById('kwInput');
  const sortColumn = document.getElementById('sortColumn');
  const sortDir = document.getElementById('sortDir');
  const dateColumn = document.getElementById('dateColumn');
  const dateFrom = document.getElementById('dateFrom');
  const dateTo = document.getElementById('dateTo');
  const applyBtn = document.getElementById('applyBtn');
  const resetBtn = document.getElementById('resetBtn');

  function toDate(val){
    if (!val) return null;
    const d = new Date(val);
    return isNaN(d.getTime()) ? null : d;
  }

  function matchKeyword(item, kw){
    if (!kw) return true;
    const hay = [
      item.dataset_id, item.title, item.rest_url,
      item.updated_at, item.inserted_at,
      JSON.stringify(item.raw || {})
    ].join(' ').toLowerCase();
    return hay.includes(kw);
  }

  function inDateRange(item, col, from, to){
    if (!from && !to) return true;
    const d = toDate(item[col]);
    if (!d) return false;
    if (from && d < from) return false;
    if (to && d > to) return false;
    return true;
  }

  function render(list){
    countEl.textContent = list.length;
    if (!list.length){
      body.innerHTML = '<tr><td colspan="7" class="text-white-50 text-center">нет данных по фильтрам</td></tr>';
      return;
    }
    body.innerHTML = list.map(row => {
      const rawId = `raw-${row.id}-${(row.dataset_id||row.id)}`.replace(/[^a-zA-Z0-9_-]/g,'');
      const title = row.title || '—';
      return `
        <tr class="fade-in">
          <td>${row.id}</td>
          <td>${row.dataset_id || '—'}</td>
          <td style="max-width:420px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${title}">
            ${title}
          </td>
          <td>${row.rest_url ? `<a href="${row.rest_url}" target="_blank" rel="noopener">открыть</a>` : '—'}</td>
          <td>${row.updated_at || '—'}</td>
          <td>${row.inserted_at || '—'}</td>
          <td><button class="btn btn-outline-light btn-sm" data-bs-toggle="collapse" data-bs-target="#${rawId}">JSON</button></td>
        </tr>
        <tr class="collapse" id="${rawId}">
          <td colspan="7">
            <pre class="mb-0 small text-white-50" style="max-height:260px;overflow:auto">${JSON.stringify(row.raw || {}, null, 2)}</pre>
          </td>
        </tr>
      `;
    }).join('');
  }

  function apply(){
    const kw = (kwInput.value || '').trim().toLowerCase();
    const col = sortColumn.value;
    const dir = sortDir.value;
    const dCol = dateColumn.value;
    const from = toDate(dateFrom.value);
    const to = toDate(dateTo.value);

    const filtered = items
      .filter(it => matchKeyword(it, kw))
      .filter(it => inDateRange(it, dCol, from, to))
      .sort((a,b)=>{
        const av = a[col] || '';
        const bv = b[col] || '';
        // сравниваем даты, если похоже на дату
        const ad = toDate(av), bd = toDate(bv);
        if (ad && bd) {
          return dir === 'asc' ? ad - bd : bd - ad;
        }
        return dir === 'asc' ? String(av).localeCompare(String(bv)) : String(bv).localeCompare(String(av));
      });
    render(filtered);
  }

  applyBtn.addEventListener('click', apply);
  resetBtn.addEventListener('click', ()=>{
    kwInput.value = '';
    sortColumn.value = 'updated_at';
    sortDir.value = 'desc';
    dateColumn.value = 'updated_at';
    dateFrom.value = '';
    dateTo.value = '';
    apply();
  });

  apply();
});
</script>
@endsection
