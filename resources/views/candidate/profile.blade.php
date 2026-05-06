@extends('layouts.app', ['role' => 'candidate'])
@section('title', 'My Profile')
@section('content')
<div class="page-header"><div><h1>My Profile</h1><p>Verified candidate profile & score</p></div></div>
<div class="row g-3">
    <div class="col-lg-8"><div class="card"><div class="card-body">
        <form id="profileForm">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" readonly></div>
                <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" name="email" readonly></div>
                <div class="col-12"><label class="form-label">Skills (comma separated)</label><input class="form-control" name="skills" placeholder="e.g. PHP, Laravel, React"></div>
                <div class="col-12"><label class="form-label">Education</label><textarea class="form-control" name="education" rows="3"></textarea></div>
                <div class="col-12"><label class="form-label">Experience</label><textarea class="form-control" name="experience" rows="4"></textarea></div>
            </div>
            <button class="btn btn-primary mt-3">Save profile</button>
        </form>
    </div></div></div>
    <div class="col-lg-4"><div class="card"><div class="card-header">Score breakdown</div><div class="card-body" id="scoreBox">—</div></div></div>
</div>
@push('scripts')
<script>
async function load() {
    try {
        const r = await THR.api('/candidate/profile');
        const p = r.profile || r;
        const f = document.getElementById('profileForm');
        f.name.value = p.name || '';
        f.email.value = p.email || '';
        f.skills.value = Array.isArray(p.skills) ? p.skills.join(', ') : (p.skills || '');
        f.education.value = p.education || '';
        f.experience.value = p.experience || '';
        const s = r.score_breakdown || {};
        document.getElementById('scoreBox').innerHTML = Object.keys(s).length ? Object.entries(s).map(([k,v]) => `<div class="d-flex justify-content-between border-bottom py-2"><span>${THR.escapeHtml(k)}</span><span class="fw-semibold">${THR.escapeHtml(JSON.stringify(v))}</span></div>`).join('') : '<p class="text-muted mb-0">No score yet</p>';
    } catch (e) { THR.toast(e.message, 'danger'); }
}
load();
document.getElementById('profileForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = THR.formData(e.target);
    delete data.name; delete data.email;
    if (data.skills) data.skills = data.skills.split(',').map(s=>s.trim()).filter(Boolean);
    try { await THR.api('/candidate/profile', { method: 'PUT', body: data }); THR.toast('Profile saved','success'); load(); }
    catch (err) { THR.toast(err.message, 'danger'); }
});
</script>
@endpush
@endsection
