@extends('layouts.app', ['role' => 'candidate'])
@section('title', 'My Applications')
@section('content')
<div class="page-header"><div><h1>My Applications</h1><p>Track your hiring journey</p></div>
<a href="/candidate/jobs" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Apply to a job</a></div>
<div class="card"><div class="card-body p-0"><table class="table mb-0">
<thead><tr><th>Job</th><th>Status</th><th>Task</th><th>Interview</th><th>Applied</th><th></th></tr></thead>
<tbody id="rows"><tr><td colspan="6" class="empty-state">Loading…</td></tr></tbody></table></div></div>
@push('scripts')
<script>
(async () => {
    try {
        const data = await THR.api('/candidate/applications');
        const items = data.data || data;
        const tb = document.getElementById('rows');
        if (!items.length) return tb.innerHTML = '<tr><td colspan="6" class="empty-state">No applications yet</td></tr>';
        tb.innerHTML = items.map(a => `<tr>
            <td>${THR.escapeHtml(a.job?.title||'—')}<br><small class="text-muted">${THR.escapeHtml(a.job?.location||'')}</small></td>
            <td>${THR.statusPill(a.status)}</td>
            <td>${a.task? THR.statusPill(a.task.status):'—'}</td>
            <td>${a.interview? `${a.interview.date} ${a.interview.time}`:'—'}</td>
            <td>${THR.fmtDate(a.created_at)}</td>
            <td><a class="btn btn-sm btn-outline-primary" href="/candidate/applications/${a.id}">Open</a></td>
        </tr>`).join('');
    } catch (e) { THR.toast(e.message, 'danger'); }
})();
</script>
@endpush
@endsection
