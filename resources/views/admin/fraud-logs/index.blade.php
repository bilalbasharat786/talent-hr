@extends('layouts.app', ['role' => 'admin'])
@section('title', 'Fraud Logs')
@section('content')
<div class="page-header"><div><h1>Fraud Logs</h1><p>Triage suspicious behaviour</p></div></div>
<div class="card"><div class="card-body p-0"><table class="table mb-0">
<thead><tr><th>Type</th><th>Reference</th><th>Status</th><th>Description</th><th>Created</th><th></th></tr></thead>
<tbody id="rows"><tr><td colspan="6" class="empty-state">Loading…</td></tr></tbody></table></div></div>
@push('scripts')
<script>
(async () => {
    try {
        const data = await THR.api('/admin/fraud-logs');
        const items = data.data || data;
        const tb = document.getElementById('rows');
        if (!items.length) return tb.innerHTML = '<tr><td colspan="6" class="empty-state">No fraud logs</td></tr>';
        tb.innerHTML = items.map(f => `<tr><td>${THR.escapeHtml(f.type)}</td><td>${THR.escapeHtml(f.reference_id)}</td><td>${THR.statusPill(f.status)}</td><td class="text-truncate" style="max-width:300px;">${THR.escapeHtml(f.description||'')}</td><td>${THR.fmtDate(f.created_at)}</td><td><a class="btn btn-sm btn-outline-primary" href="/admin/fraud-logs/${f.id}">View</a></td></tr>`).join('');
    } catch (e) { THR.toast(e.message, 'danger'); }
})();
</script>
@endpush
@endsection
