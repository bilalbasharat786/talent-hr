@extends('layouts.app', ['role' => 'hr'])
@section('title', 'HR Dashboard')
@section('content')
<div class="page-header"><div><h1>HR Dashboard</h1><p>Hiring funnel snapshot</p></div></div>
<div class="row g-3">
    <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="stat-label">Total Jobs</div><div class="stat-value" id="kTotal">—</div></div></div>
    <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="stat-label">Active Jobs</div><div class="stat-value" id="kActive">—</div></div></div>
    <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="stat-label">Candidates in Pipeline</div><div class="stat-value" id="kPipe">—</div></div></div>
    <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="stat-label">Pending Reviews</div><div class="stat-value" id="kPend">—</div></div></div>
</div>
<div class="row g-3 mt-1">
    <div class="col-lg-5"><div class="card"><div class="card-header">Pipeline by stage</div><div class="card-body" id="pipeline">Loading…</div></div></div>
    <div class="col-lg-7"><div class="card"><div class="card-header">Recent applications</div><div class="card-body p-0"><table class="table mb-0"><thead><tr><th>Candidate</th><th>Job</th><th>Status</th><th>Applied</th></tr></thead><tbody id="recent"><tr><td colspan="4" class="empty-state">Loading…</td></tr></tbody></table></div></div></div>
</div>
@push('scripts')
<script>
(async () => {
    try {
        const d = await THR.api('/hr/dashboard');
        document.getElementById('kTotal').textContent = d.total_jobs ?? 0;
        document.getElementById('kActive').textContent = d.active_jobs ?? 0;
        document.getElementById('kPipe').textContent = d.candidates_in_pipeline ?? 0;
        document.getElementById('kPend').textContent = d.pending_reviews ?? 0;
        const stages = d.pipeline_overview || {};
        const rows = Object.entries(stages).map(([s,n]) => `<div class="d-flex justify-content-between border-bottom py-2"><span class="text-capitalize">${THR.escapeHtml(s.replace(/_/g,' '))}</span><span class="fw-semibold">${n}</span></div>`).join('');
        document.getElementById('pipeline').innerHTML = rows || '<p class="text-muted mb-0">No data</p>';
        const apps = d.recent_applications || [];
        const tb = document.getElementById('recent');
        tb.innerHTML = apps.length ? apps.map(a => `<tr><td>${THR.escapeHtml(a.candidate?.name||'—')}</td><td>${THR.escapeHtml(a.job?.title||'—')}</td><td>${THR.statusPill(a.status)}</td><td>${THR.fmtDate(a.created_at)}</td></tr>`).join('') : '<tr><td colspan="4" class="empty-state">No applications yet</td></tr>';
    } catch (e) { THR.toast(e.message, 'danger'); }
})();
</script>
@endpush
@endsection
