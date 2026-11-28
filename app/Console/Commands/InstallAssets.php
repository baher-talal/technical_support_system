<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

/**
 * ينزّل Bootstrap وأيقوناته وخط Cairo إلى public/vendor مرة واحدة (على جهاز عليه إنترنت)،
 * فيعمل النظام بعدها على شبكة داخلية بدون إنترنت. القوالب تستخدم الملفات المحلية إن وُجدت وإلا CDN.
 */
class InstallAssets extends Command
{
    protected $signature = 'assets:install {--force : إعادة التنزيل حتى لو الملفات موجودة}';
    protected $description = 'تنزيل Bootstrap وأيقوناته وخط Cairo إلى public/vendor للعمل بدون إنترنت';

    private const BS = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist';
    private const ICONS = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font';
    private const FONT_CSS = 'https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap';
    // متصفح حديث ليرجع Google Fonts ملفات woff2
    private const UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36';

    private int $failed = 0;

    public function handle(): int
    {
        $pub = public_path('vendor');

        $this->get(self::BS . '/css/bootstrap.rtl.min.css', "$pub/bootstrap/css/bootstrap.rtl.min.css");
        $this->get(self::BS . '/js/bootstrap.bundle.min.js', "$pub/bootstrap/js/bootstrap.bundle.min.js");

        $this->get(self::ICONS . '/bootstrap-icons.css', "$pub/bootstrap-icons/font/bootstrap-icons.css");
        foreach (['bootstrap-icons.woff2', 'bootstrap-icons.woff'] as $f) {
            $this->get(self::ICONS . "/fonts/$f", "$pub/bootstrap-icons/font/fonts/$f");
        }

        $this->cairo("$pub/cairo");

        if ($this->failed) {
            $this->error("فشل تنزيل {$this->failed} ملف. تأكد من اتصال هذا الجهاز بالإنترنت ثم أعد التشغيل. (النظام يعمل في الأثناء عبر CDN)");
            return self::FAILURE;
        }
        $this->info('تم. الملفات في public/vendor — أضفها للنسخة التي تنشرها على الخادم الداخلي.');
        return self::SUCCESS;
    }

    private function get(string $url, string $dest): ?string
    {
        if (!$this->option('force') && is_file($dest)) { $this->line("موجود: " . basename($dest)); return file_get_contents($dest); }
        try {
            $res = Http::timeout(60)->withUserAgent(self::UA)->get($url)->throw();
            File::ensureDirectoryExists(dirname($dest));
            File::put($dest, $res->body());
            $this->line('تم: ' . str_replace(public_path() . DIRECTORY_SEPARATOR, '', $dest));
            return $res->body();
        } catch (\Throwable $e) {
            $this->failed++;
            $this->warn("تعذر تنزيل $url — " . $e->getMessage());
            return null;
        }
    }

    /** يجلب CSS الخط، وينزّل ملفات woff2 المذكورة فيه، ويعيد كتابة المسارات لتصبح محلية */
    private function cairo(string $dir): void
    {
        $dest = "$dir/cairo.css";
        if (!$this->option('force') && is_file($dest)) { $this->line('موجود: cairo.css'); return; }
        try {
            $css = Http::timeout(60)->withUserAgent(self::UA)->get(self::FONT_CSS)->throw()->body();
        } catch (\Throwable $e) {
            $this->failed++;
            $this->warn('تعذر تنزيل خط Cairo — ' . $e->getMessage());
            return;
        }
        preg_match_all('~url\((https://fonts\.gstatic\.com/[^)]+\.woff2)\)~', $css, $m);
        foreach (array_unique($m[1]) as $url) {
            $name = basename(parse_url($url, PHP_URL_PATH));
            if ($this->get($url, "$dir/fonts/$name") === null) return;
            $css = str_replace($url, "fonts/$name", $css);
        }
        File::ensureDirectoryExists($dir);
        File::put($dest, $css);
        $this->line('تم: vendor/cairo/cairo.css');
    }
}
