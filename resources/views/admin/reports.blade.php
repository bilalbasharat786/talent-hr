@extends('layouts.app', ['role' => 'admin'])
@section('title', 'Reports')
@section('content')
<div class="page-header"><div><h1>Reports</h1><p>Aggregated platform analytics</p></div></div>
<div class="card"><div class="card-body"><pre id="reportPayload" class="mb-0" style="white-space:pre-wrap;">Loading…</pre></div></div>
@push('scripts')
<script>
(async () => {
    try {
        const data = await window.THR.api('/admin/reports');
        document.getElementById('reportPayload').textContent = JSON.stringify(data, null, 2);
    } catch (e) { THR.toast(e.message, 'danger'); }
})();
</script>
@endpush
@endsection
