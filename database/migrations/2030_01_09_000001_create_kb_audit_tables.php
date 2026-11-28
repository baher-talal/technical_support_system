<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // سجل النشاط (كان هذا الملف ينشئ أيضا جدول قاعدة المعرفة، حُذف في 2030_01_10)
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('action', 50)->index();
            $t->string('subject_type', 50)->nullable();
            $t->unsignedBigInteger('subject_id')->nullable();
            $t->string('subject_label')->nullable();
            $t->json('changes')->nullable();
            $t->string('ip', 45)->nullable();
            $t->timestamp('created_at')->useCurrent()->index();
            $t->index(['subject_type', 'subject_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
