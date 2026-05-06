@extends('layouts.app', ['role' => 'company'])
@section('title', 'Jobs Overview')
@section('content')
<div class="page-header"><div><h1>Jobs Overview</h1><p>All HR-posted jobs across your company</p></div></div>
<div class="card"><div class="card-body p-0"><table class="table mb-0">
<thead><tr><th>Title</th><th>HR</th><th>Type</th><th>Mode</th><th>Status</th><th>Applications</th></tr></thead>
<tbody id="rows"><tr><td colspan="6" class="empty-state">Loading…</td></tr></tbody></table></div></div>
@push('scripts')
<script>
(async () => {
    try {
        const data = await THR.api('/company/jobs-overview');
        const items = data.data || data;
        const tb = document.getElementById('rows');
        if (!items.length) return tb.innerHTML = '<tr><td colspan="6" class="empty-state">No jobs posted</td></tr>';
        tb.innerHTML = items.map(j => `<tr><td>${THR.escapeHtml(j.title)}</td><td>${THR.escapeHtml(j.hr?.name||'—')}</td><td>${THR.escapeHtml(j.type||'—')}</td><td>${THR.escapeHtml(j.work_mode||'—')}</td><td>${THR.statusPill(j.status)}</td><td>${j.applications_count ?? (j.applications?.length || 0)}</td></tr>`).join('');
    } catch (e) { THR.toast(e.message, 'danger'); }
})();
</script>
@endpush
@endsection
