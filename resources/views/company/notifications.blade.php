@extends('layouts.app', ['role' => 'company'])
@section('title', 'Notifications')
@section('content')
<div class="page-header"><div><h1>Notifications</h1><p>System alerts & updates</p></div>
<button class="btn btn-light" id="markAll"><i class="bi bi-check2-all"></i> Mark all read</button></div>
<div class="card"><div class="card-body p-0"><ul class="list-group list-group-flush" id="list"><li class="list-group-item empty-state">Loading…</li></ul></div></div>
@push('scripts')
<script>
async function load() {
    try {
        const data = await THR.api('/company/notifications');
        const items = data.data || data;
        const ul = document.getElementById('list');
        if (!items.length) return ul.innerHTML = '<li class="list-group-item empty-state">No notifications</li>';
        ul.innerHTML = items.map(n => `<li class="list-group-item d-flex justify-content-between align-items-start ${n.read_at?'':'bg-primary-subtle'}">
            <div><div class="fw-semibold">${THR.escapeHtml(n.title)}</div><div class="small text-muted">${THR.escapeHtml(n.message)}</div><div class="small text-muted mt-1">${THR.fmtDate(n.created_at)}</div></div>
            ${n.read_at?'':`<button class="btn btn-sm btn-outline-secondary read-btn" data-id="${n.id}">Mark read</button>`}
        </li>`).join('');
        document.querySelectorAll('.read-btn').forEach(b => b.addEventListener('click', async () => {
            try { await THR.api(`/company/notifications/${b.dataset.id}/read`, { method: 'POST' }); load(); } catch(e){THR.toast(e.message,'danger');}
        }));
    } catch (e) { THR.toast(e.message, 'danger'); }
}
load();
document.getElementById('markAll').addEventListener('click', async () => {
    try { await THR.api('/company/notifications/read-all', { method: 'POST' }); THR.toast('All marked read','success'); load(); }
    catch (e) { THR.toast(e.message, 'danger'); }
});
</script>
@endpush
@endsection
