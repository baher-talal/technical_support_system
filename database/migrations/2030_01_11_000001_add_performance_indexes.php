<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /** [الجدول => [اسم الفهرس => الأعمدة]] */
    private const INDEXES = [
        // سجل صيانة الجهاز + ترتيب قائمة التيكيتات بالأحدث
        'tickets' => ['tickets_device_created_idx' => ['device_id', 'created_at'], 'tickets_created_at_idx' => ['created_at']],
        // قوائم الأجهزة: التصفية بالنوع ثم الترتيب بالاسم / الإدارة، والبحث بالسيريال عند الاستيراد
        'devices' => ['devices_type_name_idx' => ['type', 'name'], 'devices_type_dept_idx' => ['type', 'department_id'], 'devices_type_serial_idx' => ['type', 'serial']],
        // سجل النشاط: التصفية ثم الترتيب بالأحدث
        'audit_logs' => ['audit_user_created_idx' => ['user_id', 'created_at'], 'audit_action_created_idx' => ['action', 'created_at'], 'audit_subject_created_idx' => ['subject_type', 'created_at']],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            foreach ($indexes as $name => $cols) {
                if (!Schema::hasIndex($table, $name)) Schema::table($table, fn (Blueprint $t) => $t->index($cols, $name));
            }
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            foreach (array_keys($indexes) as $name) {
                if (Schema::hasIndex($table, $name)) Schema::table($table, fn (Blueprint $t) => $t->dropIndex($name));
            }
        }
    }
};
