@extends('layouts.app', ['role' => 'candidate'])
@section('title', 'Apply to a Job')
@section('content')
<div class="page-header"><div><h1>Apply to a Job</h1><p>Enter a job code shared with you to start your application</p></div></div>
<div class="row g-3">
    <div class="col-lg-6"><div class="card"><div class="card-body">
        <form id="applyForm">
            <div class="mb-3"><label class="form-label">Job ID</label><input class="form-control" name="job_id" type="number" required min="1"></div>
            <button class="btn btn-primary"><i class="bi bi-send"></i> Submit application</button>
        </form>
        <p class="text-muted small mt-3 mb-0">Once you apply, you will get an assessment to complete. Track your progress on the <a href="/candidate/applications">applications page</a>.</p>
    </div></div></div>
    <div class="col-lg-6"><div class="card"><div class="card-header">Tips</div><div class="card-body">
        <ul class="mb-0 small text-muted">
            <li>Make sure your <a href="/candidate/profile">profile</a> has up-to-date skills.</li>
            <li>Add verified internships from the <a href="/candidate/internships">internships</a> page to boost your score.</li>
            <li>Each job allows only one application per candidate.</li>
        </ul>
    </div></div></div>
</div>
@push('scripts')
<script>
document.getElementById('applyForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = THR.formData(e.target);
    data.job_id = parseInt(data.job_id, 10);
    try {
        const r = await THR.api('/candidate/apply', { method: 'POST', body: data });
        THR.toast('Application submitted','success');
        setTimeout(()=>location.href='/candidate/applications/'+(r.application?.id||''), 500);
    } catch (err) { THR.toast(err.message, 'danger'); }
});
</script>
@endpush
@endsection
