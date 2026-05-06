@extends('layouts.guest')
@section('title', 'HR Login')
@section('subtitle', 'HR Workspace')
@section('content')
<h4 class="mb-3">HR sign in</h4>
<form id="loginForm" novalidate>
    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
    <button class="btn btn-primary w-100" type="submit">Sign in</button>
    <p class="text-center mt-3 mb-0 small text-muted">HR accounts are created by your company owner.</p>
    <p class="text-center mb-0 small"><a href="/" class="text-muted">← Back to home</a></p>
</form>
@push('scripts')
<script>
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = window.THR.formData(e.target);
    try {
        const res = await window.THR.api('/hr/login', { method: 'POST', body: data });
        window.THR.Auth.set(res.token, 'hr', res.user);
        location.href = '/hr/dashboard';
    } catch (err) { window.THR.toast(err.message || 'Login failed', 'danger'); }
});
</script>
@endpush
@endsection
