@extends('layouts.guest')
@section('title', 'Two-Factor')
@section('content')
<h4 class="mb-3">Two-factor verification</h4>
<p class="text-muted small">Enter the 6-digit code sent to your email to complete sign-in.</p>
<form id="twofaForm" novalidate>
    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Code</label><input type="text" name="code" class="form-control" required pattern="\d{6}" maxlength="6"></div>
    <button class="btn btn-primary w-100" type="submit">Verify & Sign in</button>
    <p class="text-center mt-2 mb-0 small"><a href="/company/login">Back to login</a></p>
</form>
@push('scripts')
<script>
const pending = sessionStorage.getItem('pendingEmail');
if (pending) document.querySelector('[name=email]').value = pending;
document.getElementById('twofaForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = window.THR.formData(e.target);
    try {
        const res = await window.THR.api('/company/verify-2fa', { method: 'POST', body: data });
        window.THR.Auth.set(res.token, 'company', res.user);
        location.href = '/company/dashboard';
    } catch (err) { window.THR.toast(err.message || '2FA failed', 'danger'); }
});
</script>
@endpush
@endsection
