@extends('layouts.app', ['role' => 'hr'])
@section('title', 'Jobs')
@section('content')
<div class="page-header"><div><h1>My Jobs</h1><p>Manage your job postings</p></div>
<a href="/hr/jobs/create" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Post new job</a></div>
<div class="card"><div class="card-body p-0"><table class="table mb-0">
<thead><tr><th>Title</th><th>Type</th><th>Mode</th><th>Location</th><th>Status</th><th></th></tr></thead>
<tbody id="rows"><tr><td colspan="6" class="empty-state">Loading…</td></tr></tbody></table></div></div>
@push('scripts')
<script>
(async () => {
    try {
        const data = await THR.api('/hr/jobs');
        const items = data.data || data;
        const tb = document.getElementById('rows');
        if (!items.length) return tb.innerHTML = '<tr><td colspan="6" class="empty-state">No jobs yet</td></tr>';
        tb.innerHTML = items.map(j => `<tr><td>${THR.escapeHtml(j.title)}</td><td>${THR.escapeHtml(j.type||'—')}</td><td>${THR.escapeHtml(j.work_mode||'—')}</td><td>${THR.escapeHtml(j.location||'—')}</td><td>${THR.statusPill(j.status)}</td>
            <td><a class="btn btn-sm btn-outline-primary" href="/hr/jobs/${j.id}">View</a> <a class="btn btn-sm btn-outline-secondary" href="/hr/jobs/${j.id}/edit">Edit</a></td></tr>`).join('');
    } catch (e) { THR.toast(e.message, 'danger'); }
})();
</script>
@endpush
@endsection
