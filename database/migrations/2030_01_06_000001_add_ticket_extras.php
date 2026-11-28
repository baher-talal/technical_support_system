<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $t) {
            $t->unsignedTinyInteger('rating')->nullable();      // تقييم صاحب الطلب للحل (1-5)
            $t->string('rating_note', 500)->nullable();
            $t->index(['status', 'priority', 'created_at']);
        });
        Schema::table('ticket_logs', function (Blueprint $t) {
            $t->boolean('is_comment')->default(false);          // تعليق مكتوب وليس حدثا آليا
        });
    }
    public function down(): void
    {
        Schema::table('ticket_logs', fn (Blueprint $t) => $t->dropColumn('is_comment'));
        Schema::table('tickets', function (Blueprint $t) {
            $t->dropIndex(['status', 'priority', 'created_at']);
            $t->dropColumn(['rating', 'rating_note']);
        });
    }
};
