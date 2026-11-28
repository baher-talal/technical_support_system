<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $t) {
            $t->string('building', 1)->nullable()->index();   // A | B | C
            $t->string('floor', 30)->nullable();
            $t->string('room_number', 30)->nullable();
        });
        Schema::table('devices', function (Blueprint $t) {
            $t->foreignId('user_id')->nullable()->after('department_id')->constrained()->nullOnDelete();
        });
        Schema::create('app_notifications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('ticket_id')->nullable()->constrained()->cascadeOnDelete();
            $t->string('message');
            $t->timestamp('notified_at')->nullable();
            $t->timestamp('read_at')->nullable();
            $t->timestamps();
            $t->index(['user_id', 'read_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
        Schema::table('devices', fn (Blueprint $t) => $t->dropConstrainedForeignId('user_id'));
        Schema::table('departments', fn (Blueprint $t) => $t->dropColumn(['building', 'floor', 'room_number']));
    }
};
