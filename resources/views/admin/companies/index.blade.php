@extends('layouts.app', ['role' => 'admin'])
@section('title', 'Companies')
@section('content')
<div class="page-header"><div><h1>Companies</h1><p>Verify, approve, or reject company registrations</p></div></div>
<div class="card"><div class="card-body p-0"><table class="table mb-0">
<thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Trust</th><th>Registered</th><th></th></tr></thead>
<tbody id="rows"><tr><td colspan="6" class="empty-state">Loading…</td></tr></tbody></table></div></div>
@push('scripts')
<script>
(async () => {
    try {
        const data = await THR.api('/admin/companies');
        const items = data.data || data;
        const tb = document.getElementById('rows');
        if (!items.length) return tb.innerHTML = '<tr><td colspan="6" class="empty-state">No companies</td></tr>';
        tb.innerHTML = items.map(c => `<tr>
            <td>${THR.escapeHtml(c.name)}</td><td>${THR.escapeHtml(c.email)}</td>
            <td>${THR.statusPill(c.status)}</td><td><span class="badge bg-secondary">${THR.escapeHtml(c.trust_level||'basic')}</span></td>
            <td>${THR.fmtDate(c.created_at)}</td>
            <td><a class="btn btn-sm btn-outline-primary" href="/admin/companies/${c.id}">View</a></td></tr>`).join('');
    } catch (e) { THR.toast(e.message, 'danger'); }
})();
</script>
@endpush
@endsection
