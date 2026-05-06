@extends('layouts.app', ['role' => 'candidate'])
@section('title', 'Application')
@section('content')
<div class="page-header"><div><h1 id="jobTitle">Application</h1><p id="meta" class="text-muted small"></p></div><a href="/candidate/applications" class="btn btn-light">Back</a></div>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card"><div class="card-header">Status timeline</div><div class="card-body" id="timeline">Loading…</div></div>
        <div class="card mt-3"><div class="card-header">Second-round task</div><div class="card-body" id="taskBox">—</div></div>
    </div>
    <div class="col-lg-5">
        <div class="card"><div class="card-header">Quick actions</div><div class="card-body d-grid gap-2" id="actions">—</div></div>
        <div class="card mt-3"><div class="card-header">Interview</div><div class="card-body" id="interviewBox">—</div></div>
    </div>
</div>

<div class="modal fade" id="taskUploadModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form id="taskUploadForm" enctype="multipart/form-data">
    <div class="modal-header"><h5 class="modal-title">Submit task</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <input type="hidden" name="task_id" id="taskIdInput">
        <div class="mb-3"><label class="form-label">File (PDF/DOC/ZIP)</label><input class="form-control" type="file" name="file" required></div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary">Upload</button></div>
</form></div></div></div>

@push('scripts')
<script>
const id = {{ $id }};
let taskUploadModal;
async function load() {
    try {
        const r = await THR.api('/candidate/applications/' + id);
        const a = r.application || r;
        document.getElementById('jobTitle').textContent = a.job?.title || 'Application';
        document.getElementById('meta').innerHTML = `${THR.statusPill(a.status)} · Applied ${THR.fmtDate(a.created_at)}`;
        document.getElementById('timeline').innerHTML = `
            <ol class="list-group list-group-numbered">
                <li class="list-group-item">Application submitted — ${THR.fmtDate(a.created_at)}</li>
                <li class="list-group-item">Assessment ${r.assessment_submission? THR.statusPill(r.assessment_submission.status) : '<span class="text-muted">pending</span>'}</li>
                <li class="list-group-item">Pipeline status: ${THR.statusPill(a.status)}</li>
                <li class="list-group-item">Rejection reason: ${THR.escapeHtml(a.rejection_reason||'—')}</li>
            </ol>`;

        const acts = [];
        if (['assessment_pending'].includes(a.status) && a.job?.assessment_id) {
            acts.push(`<a class="btn btn-primary" href="/candidate/assessment?application_id=${a.id}"><i class="bi bi-pencil-square"></i> Start assessment</a>`);
        }
        if (a.task && ['assigned','submitted'].includes(a.task.status)) {
            acts.push(`<button class="btn btn-info text-white" id="openTaskUpload"><i class="bi bi-upload"></i> Upload task</button>`);
        }
        document.getElementById('actions').innerHTML = acts.length ? acts.join('') : '<span class="text-muted">No actions available right now.</span>';
        const openBtn = document.getElementById('openTaskUpload');
        if (openBtn) openBtn.addEventListener('click', () => { document.getElementById('taskIdInput').value = a.task.id; taskUploadModal.show(); });

        document.getElementById('taskBox').innerHTML = a.task ? `
            <p><strong>${THR.escapeHtml(a.task.title)}</strong> — ${THR.statusPill(a.task.status)}</p>
            <p class="small">${THR.escapeHtml(a.task.description||'')}</p>
            <p class="small text-muted">Deadline: ${THR.fmtDate(a.task.deadline)}</p>
        ` : '<span class="text-muted">No task assigned</span>';

        document.getElementById('interviewBox').innerHTML = a.interview ? `<p class="mb-1"><strong>${a.interview.date} ${a.interview.time}</strong></p><p class="mb-0 text-muted small">Mode: ${THR.escapeHtml(a.interview.mode)}</p>` : '<span class="text-muted">No interview scheduled</span>';
    } catch (e) { THR.toast(e.message, 'danger'); }
}
document.addEventListener('DOMContentLoaded', () => {
    taskUploadModal = new bootstrap.Modal('#taskUploadModal');
    load();
});
document.getElementById('taskUploadForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    try { await THR.api('/candidate/task/submit', { method: 'POST', body: new FormData(e.target) }); THR.toast('Task submitted','success'); taskUploadModal.hide(); load(); }
    catch (err) { THR.toast(err.message, 'danger'); }
});
</script>
@endpush
@endsection
