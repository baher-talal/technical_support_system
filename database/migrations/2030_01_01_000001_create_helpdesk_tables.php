<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $t) {
            $t->id(); $t->string('slug')->unique(); $t->string('name'); $t->timestamps();
        });
        Schema::create('permissions', function (Blueprint $t) {
            $t->id(); $t->string('key')->unique(); $t->string('label');
        });
        Schema::create('permission_role', function (Blueprint $t) {
            $t->foreignId('role_id')->constrained()->cascadeOnDelete();
            $t->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $t->primary(['role_id', 'permission_id']);
        });
        Schema::create('departments', function (Blueprint $t) {
            $t->id(); $t->string('name')->unique(); $t->timestamps();
        });
        Schema::table('users', function (Blueprint $t) {
            $t->foreignId('role_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $t->foreignId('department_id')->nullable()->after('role_id')->constrained()->nullOnDelete();
            $t->string('phone')->nullable();
            $t->boolean('is_active')->default(true)->index();
        });
        Schema::create('devices', function (Blueprint $t) {
            $t->id();
            $t->string('type', 10)->index(); // company | system
            $t->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name'); $t->string('cpu')->nullable(); $t->string('ram')->nullable();
            $t->string('storage')->nullable(); $t->string('os')->nullable(); $t->string('serial')->nullable();
            $t->string('employee_name')->nullable();
            $t->string('ip_address', 45)->nullable()->index();
            $t->string('mac_address', 17)->nullable()->index();
            $t->text('notes')->nullable();
            $t->timestamps();
        });
        Schema::create('tickets', function (Blueprint $t) {
            $t->id(); $t->string('code')->unique();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $t->string('category', 20); $t->string('item', 20)->nullable();
            $t->string('title'); $t->text('description')->nullable();
            $t->string('priority', 10)->default('normal');
            $t->string('status', 20)->default('pending_approval')->index();
            $t->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $t->text('approval_note')->nullable();
            $t->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $t->text('resolution_note')->nullable();
            $t->timestamp('resolved_at')->nullable();
            $t->timestamps();
            $t->index(['department_id', 'status']);
        });
        Schema::create('ticket_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('action'); $t->text('note')->nullable(); $t->timestamps();
        });
        Schema::create('ticket_supplies', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $t->string('item'); $t->unsignedInteger('qty')->default(1);
            $t->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
    }
    public function down(): void
    {
        foreach (['ticket_supplies','ticket_logs','tickets','devices'] as $x) Schema::dropIfExists($x);
        Schema::table('users', function (Blueprint $t) {
            $t->dropConstrainedForeignId('role_id'); $t->dropConstrainedForeignId('department_id');
            $t->dropColumn(['phone', 'is_active']);
        });
        foreach (['departments','permission_role','permissions','roles'] as $x) Schema::dropIfExists($x);
    }
};
