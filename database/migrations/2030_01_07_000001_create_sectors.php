<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // قطاع يضم عدة إدارات ويتولاه رئيس قطاع
        Schema::create('sectors', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->foreignId('head_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
        Schema::table('departments', function (Blueprint $t) {
            $t->foreignId('sector_id')->nullable()->constrained()->nullOnDelete();
        });
        // من أنشأ التيكيت (قد يختلف عن صاحب الطلب عند الإنشاء لزميل في نفس الإدارة)
        if (!Schema::hasColumn('tickets', 'created_by')) {
            Schema::table('tickets', function (Blueprint $t) {
                $t->foreignId('created_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            });
        }
    }
    public function down(): void
    {
        if (Schema::hasColumn('tickets', 'created_by')) Schema::table('tickets', fn (Blueprint $t) => $t->dropConstrainedForeignId('created_by'));
        Schema::table('departments', fn (Blueprint $t) => $t->dropConstrainedForeignId('sector_id'));
        Schema::dropIfExists('sectors');
    }
};
