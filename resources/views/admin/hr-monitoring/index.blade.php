@extends('layouts.app', ['role' => 'admin'])
@section('title', 'HR Monitoring')
@section('content')
<div class="page-header"><div><h1>HR Monitoring</h1><p>Track HR users and their hiring activity</p></div></div>
<div class="card"><div class="card-body p-0"><table class="table mb-0">
<thead><tr><th>HR User</th><th>Email</th><th>Company</th><th>HR Type</th><th>Status</th><th></th></tr></thead>
<tbody id="rows"><tr><td colspan="6" class="empty-state">Loading…</td></tr></tbody></table></div></div>
@push('scripts')
<script>
(async () => {
    try {
        const data = await THR.api('/admin/hr-monitoring');
        const items = data.data || data;
        const tb = document.getElementById('rows');
        if (!items.length) return tb.innerHTML = '<tr><td colspan="6" class="empty-state">No HR users</td></tr>';
        tb.innerHTML = items.map(h => `<tr><td>${THR.escapeHtml(h.name)}</td><td>${THR.escapeHtml(h.email)}</td><td>${THR.escapeHtml(h.company?.name||'—')}</td><td>${THR.escapeHtml(h.hr_type||'—')}</td><td>${THR.statusPill(h.status)}</td><td><a class="btn btn-sm btn-outline-primary" href="/admin/hr-monitoring/${h.id}">View</a></td></tr>`).join('');
    } catch (e) { THR.toast(e.message, 'danger'); }
})();
</script>
@endpush
@endsection
