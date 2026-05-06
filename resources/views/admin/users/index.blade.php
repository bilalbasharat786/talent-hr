@extends('layouts.app', ['role' => 'admin'])
@section('title', 'User Management')
@section('content')
<div class="page-header"><div><h1>Users</h1><p>Manage all platform users</p></div></div>
<div class="card"><div class="card-body p-0"><table class="table mb-0">
<thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Company</th><th></th></tr></thead>
<tbody id="rows"><tr><td colspan="6" class="empty-state">Loading…</td></tr></tbody></table></div></div>
@push('scripts')
<script>
(async () => {
    try {
        const data = await THR.api('/admin/users');
        const items = data.data || data;
        const tb = document.getElementById('rows');
        if (!items.length) return tb.innerHTML = '<tr><td colspan="6" class="empty-state">No users</td></tr>';
        tb.innerHTML = items.map(u => `<tr><td>${THR.escapeHtml(u.name)}</td><td>${THR.escapeHtml(u.email)}</td><td><span class="badge bg-secondary">${THR.escapeHtml(u.role)}</span></td><td>${THR.statusPill(u.status)}</td><td>${THR.escapeHtml(u.company?.name||'—')}</td><td><a class="btn btn-sm btn-outline-primary" href="/admin/users/${u.id}">View</a></td></tr>`).join('');
    } catch (e) { THR.toast(e.message, 'danger'); }
})();
</script>
@endpush
@endsection
