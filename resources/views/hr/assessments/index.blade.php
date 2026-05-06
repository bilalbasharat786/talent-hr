@extends('layouts.app', ['role' => 'hr'])
@section('title', 'Assessments')
@section('content')
<div class="page-header"><div><h1>Assessments</h1><p>Define screening tests for jobs</p></div>
<a href="/hr/assessments/create" class="btn btn-primary"><i class="bi bi-plus-circle"></i> New assessment</a></div>
<div class="card"><div class="card-body p-0"><table class="table mb-0">
<thead><tr><th>Title</th><th>Time limit</th><th>Cooldown</th><th>Status</th><th></th></tr></thead>
<tbody id="rows"><tr><td colspan="5" class="empty-state">Loading…</td></tr></tbody></table></div></div>
@push('scripts')
<script>
(async () => {
    try {
        const data = await THR.api('/hr/assessments');
        const items = data.data || data;
        const tb = document.getElementById('rows');
        if (!items.length) return tb.innerHTML = '<tr><td colspan="5" class="empty-state">No assessments</td></tr>';
        tb.innerHTML = items.map(a => `<tr><td>${THR.escapeHtml(a.title)}</td><td>${a.time_limit||'—'} min</td><td>${a.cooldown_days||0} days</td><td>${THR.statusPill(a.status)}</td><td><a class="btn btn-sm btn-outline-primary" href="/hr/assessments/${a.id}">Open</a></td></tr>`).join('');
    } catch (e) { THR.toast(e.message, 'danger'); }
})();
</script>
@endpush
@endsection
