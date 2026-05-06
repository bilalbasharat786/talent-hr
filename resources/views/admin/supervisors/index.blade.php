@extends('layouts.app', ['role' => 'admin'])
@section('title', 'Supervisors')
@section('content')
<div class="page-header"><div><h1>Supervisors</h1><p>Verify supervisor identity proofs</p></div></div>
<div class="card"><div class="card-body p-0"><table class="table mb-0">
<thead><tr><th>Name</th><th>Email</th><th>CNIC</th><th>Company</th><th>Status</th><th></th></tr></thead>
<tbody id="rows"><tr><td colspan="6" class="empty-state">Loading…</td></tr></tbody></table></div></div>
@push('scripts')
<script>
(async () => {
    try {
        const data = await THR.api('/admin/supervisors');
        const items = data.data || data;
        const tb = document.getElementById('rows');
        if (!items.length) return tb.innerHTML = '<tr><td colspan="6" class="empty-state">No supervisors</td></tr>';
        tb.innerHTML = items.map(s => `<tr><td>${THR.escapeHtml(s.name)}</td><td>${THR.escapeHtml(s.email)}</td><td>${THR.escapeHtml(s.cnic||'—')}</td><td>${THR.escapeHtml(s.company?.name||'—')}</td><td>${THR.statusPill(s.status)}</td><td><a class="btn btn-sm btn-outline-primary" href="/admin/supervisors/${s.id}">View</a></td></tr>`).join('');
    } catch (e) { THR.toast(e.message, 'danger'); }
})();
</script>
@endpush
@endsection
