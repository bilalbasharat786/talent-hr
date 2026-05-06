@extends('layouts.guest')
@section('title', 'Register Company')
@section('content')
<h4 class="mb-3">Register your company</h4>
<form id="registerForm" novalidate>
    <div class="mb-3"><label class="form-label">Company name</label><input type="text" name="name" class="form-control" required maxlength="255"></div>
    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" maxlength="30"></div>
    <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required minlength="6"></div>
    <button class="btn btn-primary w-100" type="submit">Create account</button>
    <p class="text-center mt-3 mb-0 small">Already have an account? <a href="/company/login">Sign in</a></p>
</form>
@push('scripts')
<script>
document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = window.THR.formData(e.target);
    try {
        const res = await window.THR.api('/company/register', { method: 'POST', body: data });
        sessionStorage.setItem('pendingEmail', data.email);
        if (res.verification_code) window.THR.toast('Dev code: ' + res.verification_code, 'info');
        window.THR.toast(res.message || 'Registered. Verify email.', 'success');
        setTimeout(() => location.href = '/company/verify-email', 600);
    } catch (err) {
        window.THR.toast(err.message || 'Registration failed', 'danger');
    }
});
</script>
@endpush
@endsection
