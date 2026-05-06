@extends('layouts.app', ['role' => 'candidate'])
@section('title', 'My Dashboard')
@section('content')
<div class="page-header"><div><h1>My Dashboard</h1><p>Track your applications and assessments</p></div></div>
<div class="row g-3">
    <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="stat-label">Applied jobs</div><div class="stat-value" data-k="applied_jobs">—</div></div></div>
    <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="stat-label">Pending assessments</div><div class="stat-value" id="pendingAss">—</div></div></div>
    <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="stat-label">Verified internships</div><div class="stat-value" data-k="verified_internships">—</div></div></div>
    <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="stat-label">Unread notifications</div><div class="stat-value" id="unreadNotif">—</div></div></div>
</div>
<div class="card mt-3"><div class="card-header">Recent applications</div><div class="card-body p-0">
<table class="table mb-0"><thead><tr><th>Job</th><th>Status</th><th>Applied</th><th></th></tr></thead><tbody id="recent"><tr><td colspan="4" class="empty-state">Loading…</td></tr></tbody></table>
</div></div>
@push('scripts')
<script>
(async () => {
    try {
        const data = await THR.api('/candidate/dashboard');
        document.querySelectorAll('[data-k]').forEach(el => { el.textContent = data[el.dataset.k] ?? '—'; });
        document.getElementById('pendingAss').textContent = data.assessment_status?.pending ?? 0;
        document.getElementById('unreadNotif').textContent = data.notifications?.unread ?? 0;
        const items = data.recent_applications || [];
        const tb = document.getElementById('recent');
        tb.innerHTML = items.length ? items.map(a => `<tr><td>${THR.escapeHtml(a.job?.title||'—')}</td><td>${THR.statusPill(a.status)}</td><td>${THR.fmtDate(a.created_at)}</td><td><a class="btn btn-sm btn-outline-primary" href="/candidate/applications/${a.id}">Open</a></td></tr>`).join('') : '<tr><td colspan="4" class="empty-state">No applications yet — <a href="/candidate/jobs">apply now</a></td></tr>';
    } catch (e) { THR.toast(e.message, 'danger'); }
})();
</script>
@endpush
@endsection
