@extends('layouts.app', ['role' => 'hr'])
@section('title', 'Application Detail')
@section('content')
<div class="page-header"><div><h1>Application</h1><p id="meta" class="text-muted small"></p></div><a href="/hr/applications" class="btn btn-light">Back</a></div>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card"><div class="card-header">Candidate</div><div class="card-body" id="candBody">Loading…</div></div>
        <div class="card mt-3"><div class="card-header">Assessment</div><div class="card-body" id="assBody">—</div></div>
        <div class="card mt-3"><div class="card-header">Anti-cheat & verification</div><div class="card-body" id="antiBody">—</div></div>
    </div>
    <div class="col-lg-4">
        <div class="card"><div class="card-header">Pipeline actions</div><div class="card-body d-grid gap-2">
            <button class="btn btn-success" id="shortlistBtn"><i class="bi bi-star"></i> Shortlist</button>
            <button class="btn btn-info text-white" id="taskBtn"><i class="bi bi-clipboard-check"></i> Assign 2nd round task</button>
            <button class="btn btn-primary" id="reviewTaskBtn"><i class="bi bi-clipboard-data"></i> Review task</button>
            <button class="btn btn-warning" id="interviewBtn"><i class="bi bi-calendar-event"></i> Schedule interview</button>
            <button class="btn btn-danger" id="rejectBtn"><i class="bi bi-x-circle"></i> Reject</button>
        </div></div>
    </div>
</div>

<div class="modal fade" id="taskModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form id="taskForm" enctype="multipart/form-data">
    <div class="modal-header"><h5 class="modal-title">Assign 2nd Round Task</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label">Title</label><input class="form-control" name="title" required></div>
        <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="4" required></textarea></div>
        <div class="mb-3"><label class="form-label">Deadline</label><input type="datetime-local" name="deadline" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Instructions file</label><input type="file" name="instructions_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp" class="form-control"></div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary">Assign</button></div>
</form></div></div></div>

<div class="modal fade" id="interviewModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form id="interviewForm">
    <div class="modal-header"><h5 class="modal-title">Schedule Interview</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label">Date</label><input type="date" name="date" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Time</label><input type="time" name="time" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Mode</label><select class="form-select" name="mode" required><option value="onsite">Onsite</option><option value="online">Online</option><option value="hybrid">Hybrid</option></select></div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary">Schedule</button></div>
</form></div></div></div>

@push('scripts')
<script>
const id = {{ $id }};
let taskModal, interviewModal;
async function load() {
    try {
        const r = await THR.api('/hr/applications/' + id);
        const a = r.application || r;
        document.getElementById('meta').innerHTML = `Status: ${THR.statusPill(a.status)} · Job: ${THR.escapeHtml(a.job?.title||'—')}`;
        document.getElementById('candBody').innerHTML = `
            <dl class="row mb-0">
                <dt class="col-sm-3">Name</dt><dd class="col-sm-9">${THR.escapeHtml(a.candidate?.name||'—')}</dd>
                <dt class="col-sm-3">Email</dt><dd class="col-sm-9">${THR.escapeHtml(a.candidate?.email||'—')}</dd>
                <dt class="col-sm-3">Skill match</dt><dd class="col-sm-9">${a.skill_match_percentage??'—'}%</dd>
                <dt class="col-sm-3">Experience verified</dt><dd class="col-sm-9">${THR.escapeHtml(a.experience_verification_status||'—')}</dd>
                <dt class="col-sm-3">Portfolio</dt><dd class="col-sm-9">${(a.portfolio_links||[]).map(l=>`<a href="${THR.escapeHtml(l)}" target="_blank">${THR.escapeHtml(l)}</a>`).join('<br>')||'—'}</dd>
                <dt class="col-sm-3">2nd round task</dt><dd class="col-sm-9">${a.task? `${THR.escapeHtml(a.task.title)} (${THR.statusPill(a.task.status)})` : '—'}</dd>
                <dt class="col-sm-3">Interview</dt><dd class="col-sm-9">${a.interview? `${a.interview.date} ${a.interview.time} (${a.interview.mode})` : '—'}</dd>
            </dl>`;
        const sb = r.assessment_score_breakdown;
        document.getElementById('assBody').innerHTML = sb? `Score: <strong>${sb.score}</strong> · Status: ${THR.statusPill(sb.status)}` : '<span class="text-muted">No submission yet</span>';
        document.getElementById('antiBody').innerHTML = `
            <p class="mb-1"><strong>Plagiarism:</strong> <pre class="small mb-0">${THR.escapeHtml(JSON.stringify(r.plagiarism_report||a.plagiarism_report||{}, null, 2))}</pre></p>
            <p class="mb-0"><strong>Anti-cheat logs:</strong> <pre class="small mb-0">${THR.escapeHtml(JSON.stringify(r.anti_cheat_logs||a.anti_cheat_logs||{}, null, 2))}</pre></p>`;
    } catch (e) { THR.toast(e.message, 'danger'); }
}
document.addEventListener('DOMContentLoaded', () => {
    taskModal = new bootstrap.Modal('#taskModal');
    interviewModal = new bootstrap.Modal('#interviewModal');
    load();
});
document.getElementById('shortlistBtn').addEventListener('click', async () => {
    try { await THR.api(`/hr/applications/${id}/shortlist`, { method: 'POST' }); THR.toast('Shortlisted','success'); load(); }
    catch (e) { THR.toast(e.message, 'danger'); }
});
document.getElementById('rejectBtn').addEventListener('click', async () => {
    const reason = prompt('Rejection reason:'); if (!reason) return;
    try { await THR.api(`/hr/applications/${id}/reject`, { method: 'POST', body: { reason } }); THR.toast('Rejected','warning'); load(); }
    catch (e) { THR.toast(e.message, 'danger'); }
});
document.getElementById('taskBtn').addEventListener('click', () => taskModal.show());
document.getElementById('taskForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    try { await THR.api(`/hr/applications/${id}/assign-task`, { method: 'POST', body: new FormData(e.target) }); THR.toast('Task assigned','success'); taskModal.hide(); load(); }
    catch (err) { THR.toast(err.message, 'danger'); }
});
document.getElementById('reviewTaskBtn').addEventListener('click', async () => {
    const status = prompt('Mark task as: passed or failed?'); if (!['passed','failed'].includes(status)) return THR.toast('Invalid value','warning');
    try { await THR.api(`/hr/applications/${id}/review-task`, { method: 'POST', body: { status } }); THR.toast('Task reviewed','success'); load(); }
    catch (e) { THR.toast(e.message, 'danger'); }
});
document.getElementById('interviewBtn').addEventListener('click', () => interviewModal.show());
document.getElementById('interviewForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    try { await THR.api(`/hr/applications/${id}/schedule-interview`, { method: 'POST', body: THR.formData(e.target) }); THR.toast('Interview scheduled','success'); interviewModal.hide(); load(); }
    catch (err) { THR.toast(err.message, 'danger'); }
});
</script>
@endpush
@endsection
