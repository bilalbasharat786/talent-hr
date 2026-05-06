@extends('layouts.app', ['role' => 'hr'])
@section('title', 'Applications')
@section('content')
<div class="page-header"><div><h1>Applications</h1><p>Applicant tracking pipeline</p></div>
<select class="form-select" id="statusFilter" style="width:auto;">
    <option value="">All statuses</option>
    <option value="assessment_pending">Assessment pending</option>
    <option value="passed">Passed assessment</option>
    <option value="failed">Failed assessment</option>
    <option value="shortlisted">Shortlisted</option>
    <option value="second_task_assigned">Second task assigned</option>
    <option value="interview_scheduled">Interview scheduled</option>
    <option value="rejected">Rejected</option>
</select></div>
<div class="card"><div class="card-body p-0"><table class="table mb-0">
<thead><tr><th>Candidate</th><th>Job</th><th>Status</th><th>Score</th><th>Applied</th><th></th></tr></thead>
<tbody id="rows"><tr><td colspan="6" class="empty-state">Loading…</td></tr></tbody></table></div></div>
@push('scripts')
<script>
async function load() {
    const status = document.getElementById('statusFilter').value;
    try {
        const data = await THR.api('/hr/applications' + (status?`?status=${status}`:''));
        const items = data.data || data;
        const tb = document.getElementById('rows');
        if (!items.length) return tb.innerHTML = '<tr><td colspan="6" class="empty-state">No applications</td></tr>';
        tb.innerHTML = items.map(a => `<tr>
            <td>${THR.escapeHtml(a.candidate?.name||'—')}<br><small class="text-muted">${THR.escapeHtml(a.candidate?.email||'')}</small></td>
            <td>${THR.escapeHtml(a.job?.title||'—')}</td>
            <td>${THR.statusPill(a.status)}</td>
            <td>${a.skill_match_percentage??'—'}%</td>
            <td>${THR.fmtDate(a.created_at)}</td>
            <td><a class="btn btn-sm btn-outline-primary" href="/hr/applications/${a.id}">Open</a></td>
        </tr>`).join('');
    } catch (e) { THR.toast(e.message, 'danger'); }
}
load();
document.getElementById('statusFilter').addEventListener('change', load);
</script>
@endpush
@endsection
