<?php
use App\Http\Controllers\{AuditController, BackupController, ProfileController, NotificationController, EmployeeController, AuthController, DashboardController, TicketController, DeviceController, AdminController, ReportController};
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');
    Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::get('employees/{user}', [EmployeeController::class, 'show'])->name('employees.show')->middleware('perm:devices.view,system_devices.view,users.manage');
    Route::get('tickets/department/{department}/devices', [TicketController::class, 'departmentDevices'])->name('tickets.departmentDevices')->middleware('perm:tickets.create');

    Route::get('tickets/export', [TicketController::class, 'export'])->name('tickets.export')->middleware('perm:reports.view');
    Route::get('tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('tickets/create', [TicketController::class, 'create'])->name('tickets.create')->middleware('perm:tickets.create');
    Route::post('tickets', [TicketController::class, 'store'])->name('tickets.store')->middleware('perm:tickets.create');
    Route::get('tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::delete('tickets/{ticket}', [TicketController::class, 'destroy'])->name('tickets.destroy')->middleware('perm:tickets.delete');
    Route::post('tickets/{ticket}/approve', [TicketController::class, 'approve'])->name('tickets.approve')->middleware('perm:tickets.approve');
    Route::post('tickets/{ticket}/reject', [TicketController::class, 'reject'])->name('tickets.reject')->middleware('perm:tickets.approve');
    Route::post('tickets/{ticket}/start', [TicketController::class, 'start'])->name('tickets.start');
    Route::post('tickets/{ticket}/supplies', [TicketController::class, 'issue'])->name('tickets.supplies');
    Route::post('tickets/{ticket}/resolve', [TicketController::class, 'resolve'])->name('tickets.resolve');
    Route::post('tickets/{ticket}/comment', [TicketController::class, 'comment'])->name('tickets.comment');
    Route::post('tickets/{ticket}/reassign', [TicketController::class, 'reassign'])->name('tickets.reassign')->middleware('perm:tickets.approve');
    Route::post('tickets/{ticket}/reopen', [TicketController::class, 'reopen'])->name('tickets.reopen');
    Route::post('tickets/{ticket}/rate', [TicketController::class, 'rate'])->name('tickets.rate');

    Route::prefix('admin')->group(function () {
        Route::get('audit', [AuditController::class, 'index'])->name('audit')->middleware('perm:audit.view');
        Route::middleware('perm:backup.manage')->group(function () {
            Route::get('backups', [BackupController::class, 'index'])->name('backups');
            Route::post('backups', [BackupController::class, 'store'])->name('backups.store');
            Route::get('backups/{file}', [BackupController::class, 'download'])->where('file', 'backup-[0-9]{8}-[0-9]{6}\.sql(\.gz)?')->name('backups.download');
            Route::delete('backups/{file}', [BackupController::class, 'destroy'])->where('file', 'backup-[0-9]{8}-[0-9]{6}\.sql(\.gz)?')->name('backups.destroy');
        });
    });

    Route::get('profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('devices/{type}', [DeviceController::class, 'index'])->whereIn('type', ['company', 'system'])->name('devices.index');
    Route::get('devices/{type}/labels', [DeviceController::class, 'labels'])->whereIn('type', ['company'])->name('devices.labels');
    Route::get('devices/{type}/export', [DeviceController::class, 'export'])->whereIn('type', ['company', 'system'])->name('devices.export');
    Route::get('devices/{type}/template', [DeviceController::class, 'template'])->whereIn('type', ['company', 'system'])->name('devices.template');
    Route::post('devices/{type}/import', [DeviceController::class, 'import'])->whereIn('type', ['company', 'system'])->name('devices.import')->middleware('throttle:10,1');
    Route::get('devices/{type}/create', [DeviceController::class, 'form'])->whereIn('type', ['company', 'system'])->name('devices.create');
    Route::get('devices/{type}/{device}', [DeviceController::class, 'show'])->whereIn('type', ['company'])->whereNumber('device')->name('devices.show');
    Route::get('devices/{type}/{device}/edit', [DeviceController::class, 'form'])->whereIn('type', ['company', 'system'])->name('devices.edit');
    Route::post('devices/{type}', [DeviceController::class, 'save'])->whereIn('type', ['company', 'system'])->name('devices.store');
    Route::put('devices/{type}/{device}', [DeviceController::class, 'save'])->whereIn('type', ['company', 'system'])->name('devices.update');
    Route::delete('devices/{type}/{device}', [DeviceController::class, 'destroy'])->whereIn('type', ['company', 'system'])->name('devices.destroy');

    Route::get('reports', [ReportController::class, 'index'])->name('reports')->middleware('perm:reports.view');

    Route::middleware('perm:users.manage')->prefix('admin')->group(function () {
        Route::get('users', [AdminController::class, 'users'])->name('admin.users');
        Route::get('users/create', [AdminController::class, 'userForm'])->name('admin.users.create');
        Route::get('users/{user}/edit', [AdminController::class, 'userForm'])->name('admin.users.edit');
        Route::post('users', [AdminController::class, 'userSave'])->name('admin.users.store');
        Route::put('users/{user}', [AdminController::class, 'userSave'])->name('admin.users.update');
        Route::delete('users/{user}', [AdminController::class, 'userDestroy'])->name('admin.users.destroy');
        Route::get('departments', [AdminController::class, 'departments'])->name('admin.departments');
        Route::post('departments', [AdminController::class, 'departmentSave'])->name('admin.departments.store');
        Route::put('departments/{department}', [AdminController::class, 'departmentSave'])->name('admin.departments.update');
        Route::delete('departments/{department}', [AdminController::class, 'departmentDestroy'])->name('admin.departments.destroy');
        Route::get('sectors', [AdminController::class, 'sectors'])->name('admin.sectors');
        Route::post('sectors', [AdminController::class, 'sectorSave'])->name('admin.sectors.store');
        Route::put('sectors/{sector}', [AdminController::class, 'sectorSave'])->name('admin.sectors.update');
        Route::delete('sectors/{sector}', [AdminController::class, 'sectorDestroy'])->name('admin.sectors.destroy');
    });
    Route::middleware('perm:roles.manage')->prefix('admin')->group(function () {
        Route::get('roles', [AdminController::class, 'roles'])->name('admin.roles');
        Route::put('roles/{role}', [AdminController::class, 'roleUpdate'])->name('admin.roles.update');
    });
});
