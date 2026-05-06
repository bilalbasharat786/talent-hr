@extends('layouts.app', ['role' => 'candidate'])
@section('title', 'Internships')
@section('content')
<div class="page-header"><div><h1>Internships</h1><p>Submit internship certificates for verification</p></div></div>
<div class="row g-3">
    <div class="col-lg-5"><div class="card"><div class="card-header">Submit new</div><div class="card-body">
        <form id="intForm" enctype="multipart/form-data">
            <div class="mb-3"><label class="form-label">Company name</label><input class="form-control" name="company_name" required maxlength="255"></div>
            <div class="mb-3"><label class="form-label">Duration</label><input class="form-control" name="duration" required placeholder="e.g. 3 months" maxlength="255"></div>
            <div class="mb-3"><label class="form-label">Supervisor email</label><input class="form-control" type="email" name="supervisor_email" required></div>
            <div class="mb-3"><label class="form-label">Certificate (PDF/JPG/PNG)</label><input class="form-control" type="file" name="certificate" accept=".pdf,.jpg,.jpeg,.png,.webp" required></div>
            <button class="btn btn-primary">Submit</button>
        </form>
    </div></div></div>
    <div class="col-lg-7"><div class="card"><div class="card-header">Submitted internships</div><div class="card-body p-0">
        <table class="table mb-0"><thead><tr><th>Company</th><th>Duration</th><th>Supervisor</th><th>Status</th></tr></thead><tbody id="rows"><tr><td colspan="4" class="empty-state">Loading…</td></tr></tbody></table>
    </div></div></div>
</div>
@push('scripts')
<script>
async function load() {
    try {
        const data = await THR.api('/candidate/internships');
        const items = data.data || data;
        const tb = document.getElementById('rows');
        tb.innerHTML = items.length ? items.map(i => `<tr><td>${THR.escapeHtml(i.company_name)}</td><td>${THR.escapeHtml(i.duration)}</td><td>${THR.escapeHtml(i.supervisor_email)}</td><td>${THR.statusPill(i.status)}</td></tr>`).join('') : '<tr><td colspan="4" class="empty-state">No submissions</td></tr>';
    } catch (e) { THR.toast(e.message, 'danger'); }
}
load();
document.getElementById('intForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    try { await THR.api('/candidate/internships', { method: 'POST', body: new FormData(e.target) }); THR.toast('Submitted','success'); e.target.reset(); load(); }
    catch (err) { THR.toast(err.message, 'danger'); }
});
</script>
@endpush
@endsection
