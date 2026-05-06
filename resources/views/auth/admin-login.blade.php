@extends('layouts.guest')
@section('title', 'Admin Login')
@section('subtitle', 'Super Admin Console')
@section('content')
<h4 class="mb-3">Admin sign in</h4>
<form id="loginForm" novalidate>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required minlength="6">
    </div>
    <button class="btn btn-primary w-100" type="submit"><i class="bi bi-box-arrow-in-right me-1"></i>Sign in</button>
    <p class="text-center mt-3 mb-0 small"><a href="/" class="text-muted">← Back to home</a></p>
</form>
@push('scripts')
<script>
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = window.THR.formData(e.target);
    try {
        const res = await window.THR.api('/admin/login', { method: 'POST', body: data });
        window.THR.Auth.set(res.token, 'admin', res.user);
        window.THR.toast('Welcome, admin!', 'success');
        setTimeout(() => location.href = '/admin/dashboard', 400);
    } catch (err) {
        window.THR.toast(err.message || 'Login failed', 'danger');
    }
});
</script>
@endpush
@endsection
