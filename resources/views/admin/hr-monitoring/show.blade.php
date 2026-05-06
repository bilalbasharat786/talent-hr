@extends('layouts.app', ['role' => 'admin'])
@section('title', 'HR Detail')
@section('content')
<div class="page-header"><div><h1>HR User</h1></div><a href="/admin/hr-monitoring" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a></div>
<div class="card"><div class="card-body" id="body">Loading…</div></div>
@push('scripts')
<script>
const id = {{ $id }};
(async () => {
    try {
        const r = await THR.api('/admin/hr-monitoring/' + id);
        document.getElementById('body').innerHTML = `<pre style="white-space:pre-wrap;">${THR.escapeHtml(JSON.stringify(r, null, 2))}</pre>`;
    } catch (e) { THR.toast(e.message, 'danger'); }
})();
</script>
@endpush
@endsection
