<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'نظام إدارة الدعم الفني')</title>
<script>document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('theme') || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'));</script>
@include('partials.css', ['icons' => true, 'weights' => '400;600;700'])
<style>body{font-family:'Cairo','Segoe UI',Tahoma,Arial,sans-serif;font-size:.92rem}.card-header{font-weight:600}.table>:not(caption)>*>*{padding:.35rem .5rem}.sidebar{min-height:100vh;width:240px}.card{border-radius:.75rem}
@media(max-width:991px){.sidebar{min-height:auto;width:100%}}</style>
</head>
<body class="bg-body-tertiary">
@php $u = auth()->user(); $nav = [
  ['dashboard','bi-speedometer2','لوحة التحكم','dashboard.view'],
  ['tickets.index','bi-ticket-perforated','التيكيتات',null],
  ['tickets.create','bi-plus-circle','تيكيت جديد','tickets.create'],
]; @endphp
<div class="d-lg-flex">
  <aside class="sidebar bg-body border-start p-3 flex-shrink-0">
    <h5 class="fw-bold mb-1"><i class="bi bi-headset text-primary"></i> الدعم الفني</h5>
    <a href="{{ route('profile') }}" class="d-block small text-body-secondary text-decoration-none mb-3" title="حسابي"><i class="bi bi-person-circle"></i> {{ $u->name }} — {{ $u->role?->name }}</a>
    <nav class="nav nav-pills flex-column gap-1">
      @foreach($nav as [$r,$i,$l,$p]) @if(!$p || $u->hasPermission($p))
        <a class="nav-link {{ request()->routeIs($r) ? 'active' : '' }}" href="{{ route($r) }}"><i class="bi {{ $i }}"></i> {{ $l }}</a>
      @endif @endforeach
      @if($u->hasPermission('devices.view'))<a class="nav-link {{ request()->is('devices/company*') ? 'active' : '' }}" href="{{ route('devices.index','company') }}"><i class="bi bi-pc-display"></i> أجهزة الشركة</a>@endif
      @if($u->hasPermission('system_devices.view'))<a class="nav-link {{ request()->is('devices/system*') ? 'active' : '' }}" href="{{ route('devices.index','system') }}"><i class="bi bi-hdd-network"></i> Computer ERP System</a>@endif
      @if($u->hasPermission('reports.view'))<a class="nav-link {{ request()->routeIs('reports') ? 'active' : '' }}" href="{{ route('reports') }}"><i class="bi bi-bar-chart"></i> التقارير</a>@endif
      @if($u->hasPermission('users.manage'))
        <a class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}" href="{{ route('admin.users') }}"><i class="bi bi-people"></i> المستخدمون</a>
        <a class="nav-link {{ request()->routeIs('admin.departments') ? 'active' : '' }}" href="{{ route('admin.departments') }}"><i class="bi bi-building"></i> الإدارات</a>
        <a class="nav-link {{ request()->routeIs('admin.sectors') ? 'active' : '' }}" href="{{ route('admin.sectors') }}"><i class="bi bi-diagram-3"></i> القطاعات</a>
      @endif
      @if($u->hasPermission('roles.manage'))<a class="nav-link {{ request()->routeIs('admin.roles') ? 'active' : '' }}" href="{{ route('admin.roles') }}"><i class="bi bi-shield-lock"></i> الصلاحيات</a>@endif
      @if($u->hasPermission('audit.view'))<a class="nav-link {{ request()->routeIs('audit') ? 'active' : '' }}" href="{{ route('audit') }}"><i class="bi bi-clock-history"></i> سجل النشاط</a>@endif
      @if($u->hasPermission('backup.manage'))<a class="nav-link {{ request()->routeIs('backups') ? 'active' : '' }}" href="{{ route('backups') }}"><i class="bi bi-database-down"></i> النسخ الاحتياطي</a>@endif
    </nav>
    <div class="dropdown mb-2">
      <button class="btn btn-outline-primary btn-sm w-100" data-bs-toggle="dropdown" data-bs-auto-close="outside"><i class="bi bi-bell"></i> الإشعارات <span id="bellCount" class="badge text-bg-danger d-none">0</span></button>
      <ul class="dropdown-menu w-100 p-0" style="max-height:340px;overflow:auto">
        <li class="d-flex justify-content-between align-items-center p-2 border-bottom small"><span>غير المقروءة</span><a href="#" id="readAll">تحديد الكل كمقروء</a></li>
        <li><div id="bellList"><div class="p-2 small text-body-secondary">لا توجد إشعارات</div></div></li>
      </ul>
    </div>
    <hr>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" id="themeBtn" type="button"><i class="bi bi-moon-stars"></i> الوضع الداكن</button>
      <form method="post" action="{{ route('logout') }}">@csrf<button class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-left"></i> خروج</button></form>
    </div>
  </aside>
  <main class="flex-grow-1 p-3 p-lg-4">
    @if(session('ok'))<div class="alert alert-success alert-dismissible fade show">{{ session('ok') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('warn'))<div class="alert alert-warning" style="white-space:pre-line">{{ session('warn') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    @yield('content')
  </main>
</div>
@include('partials.js')
<script>document.getElementById('themeBtn').onclick=()=>{const h=document.documentElement,t=h.dataset.bsTheme==='dark'?'light':'dark';h.dataset.bsTheme=t;localStorage.setItem('theme',t)}</script>
<div id="toastBox" class="toast-container position-fixed top-0 start-0 p-3" style="z-index:1100"></div>
<button id="soundBtn" type="button" class="btn btn-warning shadow position-fixed bottom-0 start-0 m-3 d-none" style="z-index:1100"><i class="bi bi-volume-up"></i> اضغط لتفعيل صوت الإشعارات</button>
<script>
(()=>{let ctx,unlocked=false;const csrf=document.querySelector('meta[name=csrf-token]').content;
const AC=window.AudioContext||window.webkitAudioContext;
function unlock(){try{ctx=ctx||new AC();ctx.resume().then(()=>{if(ctx.state==='running'){unlocked=true;soundBtn.classList.add('d-none');}});}catch(e){}
 if('Notification' in window&&Notification.permission==='default')Notification.requestPermission();}
['click','keydown','touchstart'].forEach(ev=>addEventListener(ev,unlock,{passive:true}));
soundBtn.onclick=unlock;
function beep(){try{ctx=ctx||new AC();if(ctx.state!=='running'){ctx.resume();}
 [880,1175,880].forEach((f,i)=>{const o=ctx.createOscillator(),g=ctx.createGain(),t=ctx.currentTime+i*.2;o.frequency.value=f;o.connect(g);g.connect(ctx.destination);g.gain.setValueAtTime(.3,t);g.gain.exponentialRampToValueAtTime(.001,t+.25);o.start(t);o.stop(t+.3)});}catch(e){}
 if(!unlocked)soundBtn.classList.remove('d-none');}
const post=u=>fetch(u,{method:'POST',headers:{'X-CSRF-TOKEN':csrf,Accept:'application/json'}});
function sys(i){if('Notification' in window&&Notification.permission==='granted'&&document.hidden){const n=new Notification('نظام الدعم الفني',{body:i.message});n.onclick=()=>{focus();if(i.url)location.href=i.url;n.close();};}}
function toast(i){const el=document.createElement('div');el.className='toast align-items-center text-bg-primary border-0';el.setAttribute('role','alert');
 const w=document.createElement('div');w.className='d-flex';const b=document.createElement('div');b.className='toast-body';
 const a=document.createElement(i.url?'a':'span');a.className='text-white fw-bold text-decoration-none';a.textContent='🔔 '+i.message;if(i.url){a.href=i.url;a.onclick=()=>post('{{ url('notifications') }}/'+i.id+'/read');}
 b.appendChild(a);const x=document.createElement('button');x.className='btn-close btn-close-white me-2 m-auto';x.dataset.bsDismiss='toast';w.append(b,x);el.appendChild(w);toastBox.appendChild(el);
 bootstrap.Toast.getOrCreateInstance(el,{delay:9000}).show();el.addEventListener('hidden.bs.toast',()=>el.remove());}
function render(d){bellCount.textContent=d.count;bellCount.classList.toggle('d-none',!d.count);bellList.innerHTML='';
 if(!d.items.length){bellList.innerHTML='<div class="p-2 small text-body-secondary">لا توجد إشعارات</div>';return;}
 d.items.forEach(i=>{const a=document.createElement('a');a.className='d-block p-2 border-bottom small text-decoration-none text-body';a.href=i.url||'#';
  a.textContent=i.message;const t=document.createElement('div');t.className='text-body-secondary';t.textContent=i.at;a.appendChild(t);
  a.onclick=()=>post('{{ url('notifications') }}/'+i.id+'/read');bellList.appendChild(a);});}
async function poll(){try{const r=await fetch('{{ route('notifications.poll') }}',{headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'},credentials:'same-origin'});if(!r.ok)return;const d=await r.json();
 render(d);const n=d.items.filter(i=>i.new);if(n.length){n.forEach(i=>{toast(i);sys(i);});beep();}}catch(e){}}
readAll.onclick=async e=>{e.preventDefault();await post('{{ route('notifications.readAll') }}');poll();};
document.addEventListener('visibilitychange',()=>{if(!document.hidden)poll();});
poll();setInterval(poll,15000);})();
</script>
</body></html>
