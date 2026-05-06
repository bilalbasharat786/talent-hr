@extends('layouts.app', ['role' => 'company'])
@section('title', 'Company Dashboard')
@section('content')
<div class="page-header"><div><h1>Company Dashboard</h1><p>Overview of your hiring operations</p></div></div>
<div class="row g-3">
    <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="stat-label">Company Status</div><div class="stat-value" id="cStatus" style="font-size:1.25rem;">—</div></div></div>
    <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="stat-label">Trust Level</div><div class="stat-value text-capitalize" id="cTrust" style="font-size:1.25rem;">—</div></div></div>
    <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="stat-label">HR Users</div><div class="stat-value" id="cHr">—</div></div></div>
    <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="stat-label">Live Jobs</div><div class="stat-value" id="cLive">—</div></div></div>
</div>
<div class="row g-3 mt-1">
    <div class="col-lg-6"><div class="card"><div class="card-header">Jobs Overview</div><div class="card-body" id="jobsBox">Loading…</div></div></div>
    <div class="col-lg-6"><div class="card"><div class="card-header">Internship Overview</div><div class="card-body" id="intBox">Loading…</div></div></div>
</div>
@push('scripts')
<script>
(async () => {
    try {
        const d = await THR.api('/company/dashboard');
        document.getElementById('cStatus').innerHTML = THR.statusPill(d.company_status||'pending');
        document.getElementById('cTrust').textContent = d.trust_level || '—';
        document.getElementById('cHr').textContent = d.hr_users_count ?? 0;
        const jo = d.jobs_overview || {};
        document.getElementById('cLive').textContent = jo.live ?? 0;
        document.getElementById('jobsBox').innerHTML = `
            <div class="d-flex justify-content-between border-bottom py-2"><span>Draft</span><span class="fw-semibold">${jo.draft ?? 0}</span></div>
            <div class="d-flex justify-content-between border-bottom py-2"><span>Pending Approval</span><span class="fw-semibold">${jo.pending_approval ?? 0}</span></div>
            <div class="d-flex justify-content-between py-2"><span>Live</span><span class="fw-semibold">${jo.live ?? 0}</span></div>`;
        const io = d.internship_overview || {};
        document.getElementById('intBox').innerHTML = `
            <div class="d-flex justify-content-between border-bottom py-2"><span>Total</span><span class="fw-semibold">${io.total ?? 0}</span></div>
            <div class="d-flex justify-content-between border-bottom py-2"><span>Pending</span><span class="fw-semibold">${io.pending ?? 0}</span></div>
            <div class="d-flex justify-content-between border-bottom py-2"><span>Verified</span><span class="fw-semibold">${io.verified ?? 0}</span></div>
            <div class="d-flex justify-content-between border-bottom py-2"><span>Partial</span><span class="fw-semibold">${io.partial ?? 0}</span></div>
            <div class="d-flex justify-content-between py-2"><span>Rejected</span><span class="fw-semibold">${io.rejected ?? 0}</span></div>`;
    } catch (e) { THR.toast(e.message, 'danger'); }
})();
</script>
@endpush
@endsection
