@extends('layouts.app', ['role' => 'admin'])
@section('title', 'Dashboard')
@section('content')
<div class="page-header"><div><h1>Admin Dashboard</h1><p>System-wide overview & risk indicators</p></div></div>
<div class="row g-3">
    <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="d-flex justify-content-between align-items-start"><div><div class="stat-label">Total Companies</div><div class="stat-value" id="sTotalCo">—</div></div><span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-building"></i></span></div></div></div>
    <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="d-flex justify-content-between align-items-start"><div><div class="stat-label">Verified Companies</div><div class="stat-value" id="sVerCo">—</div></div><span class="stat-icon bg-success-subtle text-success"><i class="bi bi-patch-check"></i></span></div></div></div>
    <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="d-flex justify-content-between align-items-start"><div><div class="stat-label">Pending Verifications</div><div class="stat-value" id="sPend">—</div></div><span class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-hourglass-split"></i></span></div></div></div>
    <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="d-flex justify-content-between align-items-start"><div><div class="stat-label">Fraud Alerts</div><div class="stat-value" id="sFraud">—</div></div><span class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-shield-exclamation"></i></span></div></div></div>
</div>
<div class="row g-3 mt-1">
    <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="stat-label">Total Candidates</div><div class="stat-value" id="sCand">—</div></div></div>
    <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="stat-label">Total Jobs</div><div class="stat-value" id="sJobs">—</div></div></div>
    <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="stat-label">Pending Job Approvals</div><div class="stat-value" id="sPendJobs">—</div></div></div>
    <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="stat-label">Total Assessments</div><div class="stat-value" id="sAss">—</div></div></div>
</div>
<div class="card mt-3"><div class="card-header">Recent Activity</div><div class="card-body p-0"><table class="table mb-0"><thead><tr><th>Action</th><th>Module</th><th>User</th><th>Time</th></tr></thead><tbody id="recentActivity"><tr><td colspan="4" class="empty-state">Loading…</td></tr></tbody></table></div></div>
@push('scripts')
<script>
(async () => {
    try {
        const data = await window.THR.api('/admin/dashboard');
        const s = data.stats || {};
        document.getElementById('sTotalCo').textContent = s.total_companies ?? 0;
        document.getElementById('sVerCo').textContent = s.verified_companies ?? 0;
        document.getElementById('sPend').textContent = s.pending_verifications ?? 0;
        document.getElementById('sFraud').textContent = s.fraud_alerts_count ?? 0;
        document.getElementById('sCand').textContent = s.total_candidates ?? 0;
        document.getElementById('sJobs').textContent = s.total_jobs ?? 0;
        document.getElementById('sPendJobs').textContent = s.pending_job_approvals ?? 0;
        document.getElementById('sAss').textContent = s.total_assessments ?? 0;
        const ra = document.getElementById('recentActivity');
        const acts = data.recent_activities || [];
        ra.innerHTML = acts.length ? acts.map(a => `<tr><td>${THR.escapeHtml(a.action||'')}</td><td>${THR.escapeHtml(a.module||'')}</td><td>${THR.escapeHtml(a.user?.email||'system')}</td><td>${THR.fmtDate(a.created_at)}</td></tr>`).join('') : '<tr><td colspan="4" class="empty-state">No recent activity</td></tr>';
    } catch (e) { THR.toast(e.message, 'danger'); }
})();
</script>
@endpush
@endsection
