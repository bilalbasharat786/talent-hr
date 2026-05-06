@extends('layouts.app', ['role' => 'hr'])
@section('title', 'Job Detail')
@section('content')
<div class="page-header"><div><h1 id="jobTitle">Job</h1><p id="jobMeta" class="text-muted small"></p></div>
<div><a href="/hr/jobs/{{ $id }}/edit" class="btn btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
<button class="btn btn-outline-danger" id="deactivateBtn"><i class="bi bi-power"></i> Deactivate</button></div></div>
<div class="card"><div class="card-body" id="body">Loading…</div></div>
@push('scripts')
<script>
const id = {{ $id }};
async function load() {
    try {
        const r = await THR.api('/hr/jobs/' + id);
        const j = r.job || r;
        document.getElementById('jobTitle').textContent = j.title;
        document.getElementById('jobMeta').innerHTML = `${THR.escapeHtml(j.type||'')} · ${THR.escapeHtml(j.work_mode||'')} · ${THR.escapeHtml(j.location||'')} · ${THR.statusPill(j.status)}`;
        document.getElementById('body').innerHTML = `
            <dl class="row mb-0">
                <dt class="col-sm-3">Description</dt><dd class="col-sm-9" style="white-space:pre-wrap;">${THR.escapeHtml(j.description||'')}</dd>
                <dt class="col-sm-3">Skills</dt><dd class="col-sm-9">${(j.skills||[]).map(s=>`<span class="badge bg-secondary me-1">${THR.escapeHtml(s)}</span>`).join('')||'—'}</dd>
                <dt class="col-sm-3">Experience</dt><dd class="col-sm-9">${THR.escapeHtml(j.experience_level||'—')}</dd>
                <dt class="col-sm-3">Education</dt><dd class="col-sm-9">${THR.escapeHtml(j.education||'—')}</dd>
                <dt class="col-sm-3">Required</dt><dd class="col-sm-9">${j.candidates_required||1}</dd>
                <dt class="col-sm-3">Urgency</dt><dd class="col-sm-9">${THR.escapeHtml(j.hiring_urgency||'normal')}</dd>
                <dt class="col-sm-3">Assessment</dt><dd class="col-sm-9">${j.assessment_id? `#${j.assessment_id}` : '—'}</dd>
            </dl>`;
    } catch (e) { THR.toast(e.message, 'danger'); }
}
load();
document.getElementById('deactivateBtn').addEventListener('click', async () => {
    if (!confirm('Deactivate this job?')) return;
    try { await THR.api(`/hr/jobs/${id}/deactivate`, { method: 'POST' }); THR.toast('Deactivated','warning'); load(); }
    catch (e) { THR.toast(e.message, 'danger'); }
});
</script>
@endpush
@endsection
