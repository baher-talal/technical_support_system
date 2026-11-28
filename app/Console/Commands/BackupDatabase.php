<?php
namespace App\Console\Commands;

use App\Support\Audit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'backup:db';
    protected $description = 'نسخة احتياطية SQL من قاعدة البيانات مع حذف النسخ القديمة';

    public function handle(): int
    {
        $disk = Storage::disk('local');
        $disk->makeDirectory('backups');
        $gz = function_exists('gzopen');
        $name = 'backup-' . now()->format('Ymd-His') . '.sql' . ($gz ? '.gz' : '');
        $path = $disk->path('backups/' . $name);
        $fh = $gz ? gzopen($path, 'wb6') : fopen($path, 'wb');
        if (!$fh) { $this->error('تعذر إنشاء ملف النسخة'); return self::FAILURE; }
        $write = fn (string $s) => $gz ? gzwrite($fh, $s) : fwrite($fh, $s);
        try {
            $this->dump($write);
        } catch (\Throwable $e) {
            $gz ? gzclose($fh) : fclose($fh);
            @unlink($path);
            $this->error($e->getMessage());
            return self::FAILURE;
        }
        $gz ? gzclose($fh) : fclose($fh);
        $this->prune();
        Audit::log('backup.created', null, ['file' => $name, 'size' => filesize($path)]);
        $this->info("تم إنشاء $name");
        return self::SUCCESS;
    }

    /** تصدير SQL بـ PHP فقط (لا يحتاج mysqldump) */
    private function dump(callable $write): void
    {
        $pdo = DB::connection()->getPdo();
        $write('-- backup ' . now() . "\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");
        $skip = ['sessions', 'cache', 'cache_locks'];
        foreach (DB::select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'") as $row) {
            $table = array_values((array) $row)[0];
            if (in_array($table, $skip, true)) continue;
            $create = array_values((array) DB::selectOne("SHOW CREATE TABLE `$table`"))[1];
            $write("DROP TABLE IF EXISTS `$table`;\n$create;\n\n");
            $batch = [];
            foreach (DB::table($table)->cursor() as $r) {
                $vals = array_map(fn ($v) => $v === null ? 'NULL' : (is_int($v) || is_float($v) ? (string) $v : $pdo->quote((string) $v)), array_values((array) $r));
                $batch[] = '(' . implode(',', $vals) . ')';
                if (count($batch) >= 200) { $write("INSERT INTO `$table` VALUES " . implode(",\n", $batch) . ";\n"); $batch = []; }
            }
            if ($batch) $write("INSERT INTO `$table` VALUES " . implode(",\n", $batch) . ";\n");
            $write("\n");
        }
        $write("SET FOREIGN_KEY_CHECKS=1;\n");
    }

    private function prune(): void
    {
        $disk = Storage::disk('local');
        $files = collect($disk->files('backups'))->filter(fn ($f) => preg_match('/backup-\d{8}-\d{6}\.sql(\.gz)?$/', $f))->sort()->values();
        $keep = (int) config('helpdesk.backup_keep', 14);
        $files->slice(0, max(0, $files->count() - $keep))->each(fn ($f) => $disk->delete($f));
    }
}
