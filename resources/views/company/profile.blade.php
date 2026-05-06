@extends('layouts.app', ['role' => 'company'])
@section('title', 'Company Profile')
@section('content')
<div class="page-header"><div><h1>Company Profile</h1><p>Update public company info</p></div></div>
<div class="card"><div class="card-body">
<form id="profileForm">
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name"></div>
        <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" name="email" type="email" readonly></div>
        <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" maxlength="30"></div>
        <div class="col-md-6"><label class="form-label">Industry</label><input class="form-control" name="industry"></div>
        <div class="col-md-6"><label class="form-label">Company size</label><input class="form-control" name="company_size"></div>
        <div class="col-md-6"><label class="form-label">Website</label><input class="form-control" name="website"></div>
        <div class="col-12"><label class="form-label">About</label><textarea class="form-control" name="about" rows="4"></textarea></div>
        <div class="col-md-6"><label class="form-label">Office locations (comma separated)</label><input class="form-control" name="office_locations"></div>
        <div class="col-md-6"><label class="form-label">Working hours (e.g. Mon-Fri 9-5)</label><input class="form-control" name="working_hours"></div>
    </div>
    <button class="btn btn-primary mt-3">Save changes</button>
</form>
</div></div>
@push('scripts')
<script>
async function load() {
    try {
        const r = await THR.api('/company/profile');
        const c = r.company || r;
        const form = document.getElementById('profileForm');
        THR.fillForm(form, c);
        if (Array.isArray(c.office_locations)) form.office_locations.value = c.office_locations.join(', ');
        if (c.working_hours && typeof c.working_hours === 'object') form.working_hours.value = JSON.stringify(c.working_hours);
    } catch (e) { THR.toast(e.message, 'danger'); }
}
load();
document.getElementById('profileForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = THR.formData(e.target);
    if (data.office_locations) data.office_locations = data.office_locations.split(',').map(s => s.trim()).filter(Boolean);
    delete data.email;
    try { await THR.api('/company/profile', { method: 'PUT', body: data }); THR.toast('Profile updated','success'); }
    catch (err) { THR.toast(err.message, 'danger'); }
});
</script>
@endpush
@endsection
