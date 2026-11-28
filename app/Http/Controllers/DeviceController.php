<?php
namespace App\Http\Controllers;

use App\Models\{Department, Device, Ticket, User};
use App\Services\DeviceMaintenance;
use App\Support\Audit;
use App\Support\XlsxExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DeviceController extends Controller
{
    /** أعمدة ملف Excel (المفتاح => عنوان العمود). الاستيراد يعتمد على العناوين وليس الترتيب */
    private const COLUMNS = [
        'department' => 'الإدارة', 'name' => 'اسم الجهاز', 'employee_email' => 'بريد الموظف', 'employee_name' => 'اسم الموظف',
        'serial' => 'السيريال', 'ip_address' => 'IP Address', 'mac_address' => 'MAC Address', 'cpu' => 'المعالج',
        'ram' => 'الذاكرة', 'storage' => 'التخزين', 'os' => 'نظام التشغيل', 'notes' => 'ملاحظات',
    ];
    private const MAC_RE = '/^([0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2}$/';

    private function pfx(string $type): string { return $type === 'system' ? 'system_devices' : 'devices'; }
    private function can(string $type, string $act): void
    {
        abort_unless(auth()->user()->hasPermission($this->pfx($type) . '.' . $act), 403);
    }

    private function filtered(Request $r, string $type)
    {
        $q = Device::with(['department', 'employee'])->where('type', $type)->orderBy('name');
        if ($r->filled('department_id')) $q->where('department_id', $r->department_id);
        if ($r->filled('building')) $q->whereHas('department', fn ($d) => $d->where('building', $r->building));
        if ($s = trim((string) $r->q)) {
            $like = '%' . addcslashes($s, '%_\\') . '%';
            $q->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('employee_name', 'like', $like)
                ->orWhere('ip_address', 'like', $like)->orWhere('mac_address', 'like', $like)->orWhere('serial', 'like', $like));
        }
        return $q;
    }

    public function index(Request $r, string $type)
    {
        $this->can($type, 'view');
        $u = auth()->user();
        $q = $this->filtered($r, $type);
        if ($type === 'company') {
            // عدد أعطال كل جهاز خلال فترة التنبيه (ضمن التيكيتات المسموح للمستخدم برؤيتها)
            $q->withCount(['tickets as repairs_count' => fn ($t) => $t->visibleTo($u)->where('tickets.category', 'repair')
                ->where('tickets.created_at', '>=', now()->subDays((int) config('helpdesk.device_alert.days')))]);
            if ($r->input('sort') === 'repairs') $q->reorder()->orderByDesc('repairs_count')->orderBy('name');
            if ($r->boolean('frequent')) $q->having('repairs_count', '>=', (int) config('helpdesk.device_alert.repairs'));
        }
        return view('devices.index', [
            'type' => $type, 'devices' => $q->paginate(40)->withQueryString(),
            'departments' => Department::orderBy('name')->get(),
            'canManage' => $u->hasPermission($this->pfx($type) . '.manage'),
            'canExport' => $u->hasPermission($this->pfx($type) . '.export'),
            'canImport' => $u->hasPermission($this->pfx($type) . '.import'),
        ]);
    }

    /** سجل صيانة الجهاز: كل التيكيتات السابقة عليه + مؤشرات تساعد في قرار الاستبدال */
    public function show(string $type, Device $device, DeviceMaintenance $maintenance)
    {
        abort_unless($type === 'company' && $device->type === 'company', 404);
        $this->can($type, 'view');
        $u = auth()->user();
        $device->load(['department', 'employee']);
        return view('devices.show', [
            'device' => $device, 'type' => $type,
            'stats' => $maintenance->summary($device, $u),
            'tickets' => Ticket::visibleTo($u)->with(['user', 'department', 'technician'])->where('tickets.device_id', $device->id)->latest()->paginate(15),
            'canManage' => $u->hasPermission('devices.manage'),
            'canCreate' => $u->hasPermission('tickets.create'),
        ]);
    }

    /** صفحة طباعة ملصقات QR (أجهزة الشركة): يفتح مسح الملصق تيكيتا جديدا بالجهاز محددا */
    public function labels(Request $r, string $type)
    {
        abort_unless($type === 'company', 404);
        $this->can($type, 'view');
        $q = $this->filtered($r, $type);
        if ($r->filled('id')) $q->where('devices.id', $r->input('id'));
        return view('devices.labels', ['devices' => $q->limit(300)->get(), 'type' => $type]);
    }

    public function form(string $type, ?Device $device = null)
    {
        $this->can($type, 'manage');
        return view('devices.form', [
            'type' => $type, 'device' => $device ?? new Device(),
            'departments' => Department::orderBy('name')->get(),
            'users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name', 'department_id']),
        ]);
    }

    public function save(Request $r, string $type, ?Device $device = null)
    {
        $this->can($type, 'manage');
        $d = $r->validate([
            'department_id' => 'nullable|required_with:user_id|exists:departments,id', 'name' => 'required|string|max:100',
            'user_id' => ['nullable', \Illuminate\Validation\Rule::exists('users', 'id')->where('department_id', $r->department_id)],
            'cpu' => 'nullable|string|max:100', 'ram' => 'nullable|string|max:50', 'storage' => 'nullable|string|max:50',
            'os' => 'nullable|string|max:100', 'serial' => 'nullable|string|max:100', 'employee_name' => 'nullable|string|max:100',
            'ip_address' => 'nullable|ip', 'mac_address' => ['nullable', 'regex:' . self::MAC_RE],
            'notes' => 'nullable|string|max:2000',
        ]);
        if (!empty($d['user_id'])) $d['employee_name'] = User::find($d['user_id'])->name;
        $d['type'] = $type;
        $device ? $device->update($d) : Device::create($d);
        return redirect()->route('devices.index', $type)->with('ok', 'تم الحفظ');
    }

    public function destroy(string $type, Device $device)
    {
        $this->can($type, 'manage');
        abort_unless($device->type === $type, 404);
        $device->delete();
        return back()->with('ok', 'تم الحذف');
    }

    // ---------- Excel ----------
    public function export(Request $r, string $type)
    {
        $this->can($type, 'export');
        return $this->xlsx("devices-{$type}-" . date('Ymd-His') . '.xlsx', $this->filtered($r, $type)->limit(10000)->get());
    }

    public function template(string $type)
    {
        $this->can($type, 'import');
        return $this->xlsx("devices-{$type}-template.xlsx", collect());
    }

    private function xlsx(string $file, $devices)
    {
        // آخر ثلاثة أعمدة للعرض فقط ويتجاهلها الاستيراد
        $headers = array_merge(array_values(self::COLUMNS), ['المبنى', 'الدور', 'رقم الغرفة']);
        $rows = $devices->map(fn ($d) => [
            $d->department?->name, $d->name, $d->employee?->email, $d->employee_name, $d->serial, $d->ip_address, $d->mac_address,
            $d->cpu, $d->ram, $d->storage, $d->os, $d->notes, $d->department?->building, $d->department?->floor, $d->department?->room_number,
        ]);
        return XlsxExport::download($file, $headers, $rows, 'Devices');
    }

    public function import(Request $r, string $type)
    {
        $this->can($type, 'import');
        $r->validate(['file' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120']);
        try {
            $rows = IOFactory::load($r->file('file')->getRealPath())->getActiveSheet()->toArray(null, true, false, false);
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'تعذر قراءة الملف، تأكد أنه ملف Excel صحيح']);
        }
        $header = array_shift($rows) ?? [];
        $byLabel = array_flip(self::COLUMNS);
        $idx = [];
        foreach ($header as $i => $h) { $h = trim((string) $h); if (isset($byLabel[$h])) $idx[$byLabel[$h]] = $i; }
        if (!isset($idx['name'], $idx['department'])) return back()->withErrors(['file' => 'الملف يجب أن يحتوي عمودَي «اسم الجهاز» و«الإدارة». حمّل القالب واستخدمه.']);
        if (count($rows) > 5000) return back()->withErrors(['file' => 'الحد الأقصى 5000 صف في الملف الواحد']);

        $depts = Department::all()->keyBy(fn ($d) => mb_strtolower(trim($d->name)));
        $users = User::all(['id', 'name', 'email', 'department_id'])->keyBy(fn ($u) => mb_strtolower($u->email));
        $get = fn ($row, $k) => isset($idx[$k]) ? trim((string) ($row[$idx[$k]] ?? '')) : '';
        $nz = fn ($v) => $v === '' ? null : $v;
        $created = $updated = 0; $errors = [];

        Audit::$muted = true; // لا نسجّل كل جهاز على حدة، بل ملخص الاستيراد
        DB::transaction(function () use ($rows, $idx, $type, $depts, $users, $get, $nz, &$created, &$updated, &$errors) {
            foreach ($rows as $i => $row) {
                $line = $i + 2;
                if (collect($row)->filter(fn ($v) => trim((string) $v) !== '')->isEmpty()) continue;
                $name = $get($row, 'name');
                if ($name === '') { $errors[] = "سطر $line: اسم الجهاز فارغ"; continue; }
                $dept = $depts[mb_strtolower($get($row, 'department'))] ?? null;
                if (!$dept) { $errors[] = "سطر $line: الإدارة «{$get($row, 'department')}» غير موجودة"; continue; }
                $p = ['type' => $type, 'department_id' => $dept->id, 'name' => $name];
                foreach (['serial', 'cpu', 'ram', 'storage', 'os', 'notes'] as $k) if (isset($idx[$k])) $p[$k] = $nz($get($row, $k));
                if (isset($idx['ip_address'])) {
                    $ip = $get($row, 'ip_address');
                    if ($ip !== '' && !filter_var($ip, FILTER_VALIDATE_IP)) { $errors[] = "سطر $line: IP غير صالح ($ip)"; continue; }
                    $p['ip_address'] = $nz($ip);
                }
                if (isset($idx['mac_address'])) {
                    $mac = $get($row, 'mac_address');
                    if ($mac !== '') {
                        if (!preg_match(self::MAC_RE, $mac)) { $errors[] = "سطر $line: MAC غير صالح ($mac)"; continue; }
                        $mac = strtoupper(str_replace('-', ':', $mac));
                    }
                    $p['mac_address'] = $nz($mac);
                }
                if (isset($idx['employee_email'])) {
                    $email = mb_strtolower($get($row, 'employee_email'));
                    if ($email !== '') {
                        $u = $users[$email] ?? null;
                        if (!$u) { $errors[] = "سطر $line: بريد الموظف غير موجود ($email)"; continue; }
                        if ($u->department_id !== $dept->id) { $errors[] = "سطر $line: الموظف ليس من نفس إدارة الجهاز"; continue; }
                        $p['user_id'] = $u->id; $p['employee_name'] = $u->name;
                    } else {
                        $p['user_id'] = null;
                        if (isset($idx['employee_name'])) $p['employee_name'] = $nz($get($row, 'employee_name'));
                    }
                } elseif (isset($idx['employee_name'])) {
                    $p['employee_name'] = $nz($get($row, 'employee_name'));
                }
                $serial = $p['serial'] ?? null;
                $existing = Device::where('type', $type)->when($serial, fn ($q, $s) => $q->where('serial', $s),
                    fn ($q) => $q->where('name', $name)->where('department_id', $dept->id))->first();
                if ($existing) { $existing->update($p); $updated++; } else { Device::create($p); $created++; }
            }
        });

        Audit::$muted = false;
        Audit::log('devices.imported', null, ['type' => $type, 'created' => $created, 'updated' => $updated, 'skipped' => count($errors)]);
        $back = back()->with('ok', "تم الاستيراد: {$created} جهاز جديد، {$updated} محدّث" . ($errors ? '، وتخطي ' . count($errors) . ' صف' : ''));
        if ($errors) $back->with('warn', implode("\n", array_slice($errors, 0, 15)) . (count($errors) > 15 ? "\n... و" . (count($errors) - 15) . ' أخطاء أخرى' : ''));
        return $back;
    }
}
