@extends('layouts.app', ['role' => 'admin'])
@section('title', 'Fraud Detail')
@section('content')
<div class="page-header"><div><h1>Fraud Log</h1></div><a href="/admin/fraud-logs" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a></div>
<div class="row g-3">
    <div class="col-lg-8"><div class="card"><div class="card-body" id="body">Loading…</div></div></div>
    <div class="col-lg-4"><div class="card"><div class="card-header">Actions</div><div class="card-body d-grid gap-2">
        <button class="btn btn-warning" id="flagBtn">Flag</button>
        <button class="btn btn-danger" id="markBtn">Mark as Fraud</button>
        <button class="btn btn-success" id="resolveBtn">Resolve</button>
    </div></div></div>
</div>
@push('scripts')
<script>
const id = {{ $id }};
async function load() {
    try {
        const r = await THR.api('/admin/fraud-logs/' + id);
        const f = r.fraud_log || r;
        document.getElementById('body').innerHTML = `
            <dl class="row mb-0">
                <dt class="col-sm-3">Type</dt><dd class="col-sm-9">${THR.escapeHtml(f.type)}</dd>
                <dt class="col-sm-3">Reference</dt><dd class="col-sm-9">${THR.escapeHtml(f.reference_id)}</dd>
                <dt class="col-sm-3">Status</dt><dd class="col-sm-9">${THR.statusPill(f.status)}</dd>
                <dt class="col-sm-3">Description</dt><dd class="col-sm-9">${THR.escapeHtml(f.description||'—')}</dd>
                <dt class="col-sm-3">Resolved at</dt><dd class="col-sm-9">${f.resolved_at? THR.fmtDate(f.resolved_at):'—'}</dd>
            </dl>`;
    } catch (e) { THR.toast(e.message, 'danger'); }
}
load();
function act(path, ok) { return async () => { try { await THR.api(path, { method: 'POST' }); THR.toast(ok,'success'); load(); } catch(e){THR.toast(e.message,'danger');} }; }
document.getElementById('flagBtn').addEventListener('click', act(`/admin/fraud/${id}/flag`, 'Flagged'));
document.getElementById('markBtn').addEventListener('click', act(`/admin/fraud/${id}/mark-as-fraud`, 'Marked as fraud'));
document.getElementById('resolveBtn').addEventListener('click', act(`/admin/fraud/${id}/resolve`, 'Resolved'));
</script>
@endpush
@endsection
