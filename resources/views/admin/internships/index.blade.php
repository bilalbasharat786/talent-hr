@extends('layouts.app', ['role' => 'admin'])
@section('title', 'Internships')
@section('content')
<div class="page-header"><div><h1>Internships</h1><p>Verify internship certificates submitted by candidates</p></div></div>
<div class="card"><div class="card-body p-0"><table class="table mb-0">
<thead><tr><th>Candidate</th><th>Company</th><th>Duration</th><th>Supervisor</th><th>Status</th><th></th></tr></thead>
<tbody id="rows"><tr><td colspan="6" class="empty-state">Loading…</td></tr></tbody></table></div></div>
@push('scripts')
<script>
(async () => {
    try {
        const data = await THR.api('/admin/internships');
        const items = data.data || data;
        const tb = document.getElementById('rows');
        if (!items.length) return tb.innerHTML = '<tr><td colspan="6" class="empty-state">No internships</td></tr>';
        tb.innerHTML = items.map(i => `<tr><td>${THR.escapeHtml(i.candidate?.name||'—')}</td><td>${THR.escapeHtml(i.company_name)}</td><td>${THR.escapeHtml(i.duration||'—')}</td><td>${THR.escapeHtml(i.supervisor_email||'—')}</td><td>${THR.statusPill(i.status)}</td><td><a class="btn btn-sm btn-outline-primary" href="/admin/internships/${i.id}">View</a></td></tr>`).join('');
    } catch (e) { THR.toast(e.message, 'danger'); }
})();
</script>
@endpush
@endsection
