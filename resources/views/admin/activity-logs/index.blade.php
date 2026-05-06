@extends('layouts.app', ['role' => 'admin'])
@section('title', 'Activity Logs')
@section('content')
<div class="page-header"><div><h1>Activity Logs</h1><p>Audit trail across all modules</p></div></div>
<div class="card"><div class="card-body p-0"><table class="table mb-0">
<thead><tr><th>When</th><th>User</th><th>Action</th><th>Module</th><th>Description</th><th>IP</th></tr></thead>
<tbody id="rows"><tr><td colspan="6" class="empty-state">Loading…</td></tr></tbody></table></div></div>
@push('scripts')
<script>
(async () => {
    try {
        const data = await THR.api('/admin/activity-logs');
        const items = data.data || data;
        const tb = document.getElementById('rows');
        if (!items.length) return tb.innerHTML = '<tr><td colspan="6" class="empty-state">No activity</td></tr>';
        tb.innerHTML = items.map(a => `<tr><td>${THR.fmtDate(a.created_at)}</td><td>${THR.escapeHtml(a.user?.email||'system')}</td><td>${THR.escapeHtml(a.action)}</td><td>${THR.escapeHtml(a.module)}</td><td class="text-truncate" style="max-width:280px;">${THR.escapeHtml(a.description||'')}</td><td>${THR.escapeHtml(a.ip_address||'—')}</td></tr>`).join('');
    } catch (e) { THR.toast(e.message, 'danger'); }
})();
</script>
@endpush
@endsection
