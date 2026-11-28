<?php
namespace App\Http\Controllers;

use App\Support\Audit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    private const PATTERN = '/^backup-\d{8}-\d{6}\.sql(\.gz)?$/';

    public function index()
    {
        $dir = Storage::disk('local')->path('backups');
        $files = collect(File::isDirectory($dir) ? File::files($dir) : [])
            ->filter(fn ($f) => preg_match(self::PATTERN, $f->getFilename()))
            ->map(fn ($f) => ['name' => $f->getFilename(), 'size' => $f->getSize(), 'time' => Carbon::createFromTimestamp($f->getMTime())])
            ->sortByDesc('time')->values();
        return view('admin.backups', compact('files'));
    }

    public function store()
    {
        set_time_limit(0);
        $code = Artisan::call('backup:db');
        return $code === 0 ? back()->with('ok', 'تم إنشاء نسخة احتياطية') : back()->withErrors(['backup' => trim(Artisan::output()) ?: 'فشل إنشاء النسخة']);
    }

    public function download(string $file)
    {
        abort_unless(preg_match(self::PATTERN, $file) && Storage::disk('local')->exists("backups/$file"), 404);
        Audit::log('backup.downloaded', null, ['file' => $file]);
        return Storage::disk('local')->download("backups/$file");
    }

    public function destroy(string $file)
    {
        abort_unless(preg_match(self::PATTERN, $file), 404);
        Storage::disk('local')->delete("backups/$file");
        Audit::log('backup.deleted', null, ['file' => $file]);
        return back()->with('ok', 'تم حذف النسخة');
    }
}
