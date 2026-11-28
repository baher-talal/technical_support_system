<!doctype html>
<html lang="ar" dir="rtl">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>تسجيل الدخول</title>
<script>document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('theme') || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'));</script>
@include('partials.css')
<style>body{font-family:'Cairo','Segoe UI',Tahoma,Arial,sans-serif}</style></head>
<body class="bg-body-tertiary d-flex align-items-center" style="min-height:100vh">
<div class="container" style="max-width:420px">
  <div class="card shadow-sm"><div class="card-body p-4">
    <h4 class="text-center fw-bold mb-4">نظام إدارة الدعم الفني</h4>
    <form method="post" action="{{ route('login') }}">@csrf
      <div class="mb-3"><label class="form-label">البريد الإلكتروني</label><input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
      <div class="mb-3"><label class="form-label">كلمة المرور</label><input type="password" name="password" class="form-control" required></div>
      <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="remember" id="rm"><label class="form-check-label" for="rm">تذكرني</label></div>
      <button class="btn btn-primary w-100">دخول</button>
    </form>
  </div></div>
</div></body></html>
