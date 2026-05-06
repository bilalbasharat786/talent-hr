@extends('layouts.app', ['role' => 'admin'])
@section('title', 'User Detail')
@section('content')
<div class="page-header"><div><h1>User</h1></div><a href="/admin/users" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a></div>
<div class="row g-3">
    <div class="col-lg-8"><div class="card"><div class="card-body" id="body">Loading…</div></div></div>
    <div class="col-lg-4"><div class="card"><div class="card-header">Actions</div><div class="card-body d-grid gap-2">
        <button class="btn btn-success" id="activateBtn">Activate</button>
        <button class="btn btn-danger" id="deactivateBtn">Deactivate</button>
    </div></div></div>
</div>
@push('scripts')
<script>
const id = {{ $id }};
async function load() {
    try {
        const r = await THR.api('/admin/users/' + id);
        const u = r.user || r;
        document.getElementById('body').innerHTML = `
            <dl class="row mb-0">
                <dt class="col-sm-3">Name</dt><dd class="col-sm-9">${THR.escapeHtml(u.name)}</dd>
                <dt class="col-sm-3">Email</dt><dd class="col-sm-9">${THR.escapeHtml(u.email)}</dd>
                <dt class="col-sm-3">Role</dt><dd class="col-sm-9">${THR.escapeHtml(u.role)}</dd>
                <dt class="col-sm-3">Status</dt><dd class="col-sm-9">${THR.statusPill(u.status)}</dd>
                <dt class="col-sm-3">Company</dt><dd class="col-sm-9">${THR.escapeHtml(u.company?.name||'—')}</dd>
                <dt class="col-sm-3">Phone</dt><dd class="col-sm-9">${THR.escapeHtml(u.phone||'—')}</dd>
                <dt class="col-sm-3">Verified</dt><dd class="col-sm-9">${u.email_verified_at? THR.fmtDate(u.email_verified_at):'No'}</dd>
            </dl>`;
    } catch (e) { THR.toast(e.message, 'danger'); }
}
load();
document.getElementById('activateBtn').addEventListener('click', async () => { try { await THR.api(`/admin/users/${id}/activate`, { method: 'POST' }); THR.toast('Activated','success'); load(); } catch(e){THR.toast(e.message,'danger');} });
document.getElementById('deactivateBtn').addEventListener('click', async () => { if(!confirm('Deactivate user?'))return; try { await THR.api(`/admin/users/${id}/deactivate`, { method: 'POST' }); THR.toast('Deactivated','warning'); load(); } catch(e){THR.toast(e.message,'danger');} });
</script>
@endpush
@endsection
