<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // استثناءات الصلاحيات لكل مستخدم: granted=1 إضافة صلاحية فوق دوره، granted=0 سحب صلاحية يملكها دوره
        Schema::create('permission_user', function (Blueprint $t) {
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $t->boolean('granted')->default(true);
            $t->primary(['user_id', 'permission_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('permission_user'); }
};
