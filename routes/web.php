<?php

use App\Http\Controllers\ActionsController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\GenerateInsertController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RolPermissionController;
use App\Http\Controllers\Tool\QueryPersonController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ProfessionController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\AssigneeController;
use App\Http\Controllers\AssistanceController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\CarnetPDFController;
use App\Http\Controllers\GradeScheduleController;
use App\Http\Controllers\ImportStudentsController;
use App\Http\Controllers\Guest\GuestController;
use App\Http\Controllers\Guest\GuestApiController;
use App\Http\Controllers\JustificationController;
use App\Http\Controllers\UserScheduleController;
use App\Http\Controllers\Report\WeekingReportController;

Route::get('/generate-initial-data', [GenerateInsertController::class, 'generate']);
// Al final de todas tus rutas, puedes agregar esto para capturar cualquier 404
Route::fallback(function () {
    return redirect('/'); // Redirige al inicio si la ruta no existe
});
// Ruta para la página de inicio, protegida con middleware de autenticación
Route::get('/', [HomeController::class, 'index'])->name('home')->middleware('auth');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/forgot_password', [LoginController::class, 'forgot_password'])->name('forgot_password');
Route::post('/forgot_password_go', [LoginController::class, 'forgot_password_go'])->name('forgot_password_go');
Route::get('/reset_password', [LoginController::class, 'reset_password'])->name('reset_password');
Route::post('/reset_password_go', [LoginController::class, 'reset_password_go'])->name('reset_password_go');

Route::prefix('actions')->group(function () {
    Route::get('/person', [ActionsController::class, 'person'])->name('person');
});

// Rutas de Autenticación
Route::prefix('login')->group(function () {
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/', [LoginController::class, 'login'])->name('login.post');
});

// Rutas de Registro de Usuario
Route::prefix('register')->group(function () {
    Route::get('/{section?}', [RegisterController::class, 'index'])->name('register');
    Route::post('/send_code', [RegisterController::class, 'send_code'])->name('register.send_code');
    Route::post('/store/{section?}', [RegisterController::class, 'store'])->name('register.store');
    Route::post('/', [RegisterController::class, 'register'])->name('register.post');
});


Route::middleware('auth')->prefix('user')->name('user.')->group(function () {
    Route::get('/list', [UserController::class, 'index'])->name('list');
    Route::get('/password', [UserController::class, 'password'])->name('password');
    Route::post('/change_password/{id?}', [UserController::class, 'change_password'])->name('change_password');
    Route::post('/reset/{id?}', [UserController::class, 'reset'])->name('reset');
    Route::get('/form/{id?}', [UserController::class, 'form'])->name('form');
    Route::post('/edit/{id?}', [UserController::class, 'edit'])->name('edit');
    Route::post('/store/{id?}', [UserController::class, 'store'])->name('store');
    Route::get('/records/{from}/{to}/{keyword?}', [UserController::class, 'records'])->name('records');
    Route::post('/search', [UserController::class, 'search'])->name('search');
    Route::get('/show/{id}', [UserController::class, 'show'])->name('show');
    Route::delete('/destroy/{id}', [UserController::class, 'destroy'])->name('destroy');
});
Route::middleware('auth')->prefix('person')->name('person.')->group(function () {
    Route::get('/list', [PersonController::class, 'index'])->name('list');
    Route::get('/{from}/{to}/{keyword}/records', [PersonController::class, 'records'])->name('records');
    Route::get('/{keyword}/search', [PersonController::class, 'search'])->name('search');
    Route::get('/form/{id?}', [PersonController::class, 'form'])->name('form');
    Route::post('/store/{type?}/{id?}', [PersonController::class, 'store'])->name('store');
    Route::delete('/destroy/{id}', [PersonController::class, 'destroy'])->name('destroy');
});
Route::middleware('auth')->prefix('role')->name('role.')->group(function () {
    Route::get('/list', [RoleController::class, 'index'])->name('list');
    Route::get('/form/{id?}', [RoleController::class, 'form'])->name('form');
    Route::post('/store/{id?}', [RoleController::class, 'store'])->name('store');
    Route::get('/records/{from}/{to}/{keyword?}', [RoleController::class, 'records'])->name('records');
    Route::post('/search', [RoleController::class, 'search'])->name('search');
    Route::get('/show/{id}', [RoleController::class, 'show'])->name('show');
    Route::delete('/destroy/{id}', [RoleController::class, 'destroy'])->name('destroy');
});

Route::middleware('auth')->prefix('module')->name('module.')->group(function () {
    Route::get('/list', [ModuleController::class, 'index'])->name('list');
    Route::get('/form/{id?}', [ModuleController::class, 'form'])->name('form');
    Route::post('/store/{id?}', [ModuleController::class, 'store'])->name('store');
    Route::get('/records/{from}/{to}/{keyword?}', [ModuleController::class, 'records'])->name('records');
    Route::post('/search', [ModuleController::class, 'search'])->name('search');
    Route::get('/show/{id}', [ModuleController::class, 'show'])->name('show');
    Route::delete('/destroy/{id}', [ModuleController::class, 'destroy'])->name('destroy');
});


Route::middleware('auth')->prefix('permission')->name('permission.')->group(function () {
    Route::get('/list', [PermissionController::class, 'index'])->name('list');
    Route::get('/form/{id?}', [PermissionController::class, 'form'])->name('form');
    Route::post('/store/{id?}', [PermissionController::class, 'store'])->name('store');
    Route::get('/records/{from}/{to}/{keyword?}', [PermissionController::class, 'records'])->name('records');
    Route::post('/search', [PermissionController::class, 'search'])->name('search');
    Route::get('/show/{id}', [PermissionController::class, 'show'])->name('show');
    Route::delete('/destroy/{id}', [PermissionController::class, 'destroy'])->name('destroy');
});

Route::middleware('auth')->prefix('rolpermission')->name('rolpermission.')->group(function () {
    Route::get('/list', [RolPermissionController::class, 'index'])->name('list');
    Route::get('/form/{id?}', [RolPermissionController::class, 'form'])->name('form');
    Route::post('/edit/{id?}', [RolPermissionController::class, 'edit'])->name('edit');
    Route::post('/store/{id?}', [RolPermissionController::class, 'store'])->name('store');
    Route::get('/records/{from}/{to}/{keyword?}', [RolPermissionController::class, 'records'])->name('records');
    Route::post('/search', [RolPermissionController::class, 'search'])->name('search');
    Route::get('/show/{id}', [RolPermissionController::class, 'show'])->name('show');
    Route::delete('/destroy/{id}', [RolPermissionController::class, 'destroy'])->name('destroy');
});

// Si necesitas autenticación, usa 'auth' en lugar de 'person'
Route::middleware(['auth'])->prefix('person')->name('person.')->group(function () {
    Route::get('/list', [PersonController::class, 'index'])->name('list');
    Route::get('/form/{id?}', [PersonController::class, 'form'])->name('form');
    Route::post('/store/{id?}', [PersonController::class, 'store'])->name('store');
    Route::get('/records/{from}/{to}/{keyword?}', [PersonController::class, 'records'])->name('records');
    Route::post('/search', [PersonController::class, 'search'])->name('search');
    Route::get('/show/{id}', [PersonController::class, 'show'])->name('show');
    Route::delete('/destroy/{id}', [PersonController::class, 'destroy'])->name('destroy');
    Route::get('/provinces/{coddepartment}', [PersonController::class, 'getProvinces'])->name('provinces');
    Route::get('/districts/{coddepartment}/{codprovince}', [PersonController::class, 'getDistricts'])->name('districts');
});

Route::middleware(['auth'])->prefix('param')->name('param.')->group(function () {
    Route::get('/list', [PersonController::class, 'index'])->name('list');
    Route::get('/form/{id?}', [PersonController::class, 'form'])->name('form');
    Route::post('/store/{id?}', [PersonController::class, 'store'])->name('store');
    Route::get('/records/{from}/{to}/{keyword?}', [PersonController::class, 'records'])->name('records');
    Route::post('/search', [PersonController::class, 'search'])->name('search');
    Route::get('/show/{id}', [PersonController::class, 'show'])->name('show');
    Route::delete('/destroy/{id}', [PersonController::class, 'destroy'])->name('destroy');
    Route::get('/provinces/{coddepartment}', [PersonController::class, 'getProvinces'])->name('provinces');
    Route::get('/districts/{coddepartment}/{codprovince}', [PersonController::class, 'getDistricts'])->name('districts');
});


Route::middleware(['auth'])->prefix('assistance')->name('assistance.')->group(function () {
    Route::get('/list', [StudentController::class, 'index'])->name('list');
    Route::get('/form/{id?}', [StudentController::class, 'form'])->name('form');
    Route::post('/store/{id?}', [StudentController::class, 'store'])->name('store');
    Route::get('/records/{from}/{to}/{keyword?}', [StudentController::class, 'records'])->name('records');
    Route::post('/search', [StudentController::class, 'search'])->name('search');
    Route::get('/show/{id}', [StudentController::class, 'show'])->name('show');
    Route::delete('/destroy/{id}', [StudentController::class, 'destroy'])->name('destroy');
    Route::get('/provinces/{coddepartment}', [StudentController::class, 'getProvinces'])->name('provinces');
    Route::get('/districts/{coddepartment}/{codprovince}', [StudentController::class, 'getDistricts'])->name('districts');

    Route::get('/export/excel', [AssistanceController::class, 'exportExcel'])->name('export.excel');
    Route::get('/export/pdf',   [AssistanceController::class, 'exportPdf'])->name('export.pdf');
});


Route::middleware(['auth'])->prefix('student')->name('student.')->group(function () {
    Route::get('/list', [StudentController::class, 'index'])->name('list');
    Route::get('/form/{id?}', [StudentController::class, 'form'])->name('form');
    Route::post('/store/{id?}', [StudentController::class, 'store'])->name('store');
    Route::get('/records/{from}/{to}/{keyword?}', [StudentController::class, 'records'])->name('records');
    Route::post('/search', [StudentController::class, 'search'])->name('search');
    Route::get('/show/{id}', [StudentController::class, 'show'])->name('show');
    Route::delete('/destroy/{id}', [StudentController::class, 'destroy'])->name('destroy');

    Route::get('/export/excel', [StudentController::class, 'exportExcel'])->name('export.excel');
    Route::get('/export/pdf',   [StudentController::class, 'exportPdf'])->name('export.pdf');

    // Carnet individual
    Route::get('/carnet/{id}', [CarnetPDFController::class, 'generateCarnet'])
        ->name('carnet');

    // Carnet masivo (NUEVA RUTA)
    Route::post('/carnet-masivo', [CarnetPDFController::class, 'generateMassiveCarnets'])
        ->name('carnet.masivo');


    Route::get('/import',              [ImportStudentsController::class, 'form'])->name('import.form');
    Route::post('/import/upload',      [ImportStudentsController::class, 'upload'])->name('import.upload');
    Route::get('/import/status/{batchId}', [ImportStudentsController::class, 'status'])->name('import.status');
});

Route::middleware(['auth'])->prefix('profession')->name('profession.')->group(function () {
    Route::get('/list', [ProfessionController::class, 'index'])->name('list');
    Route::get('/form/{id?}', [ProfessionController::class, 'form'])->name('form');
    Route::post('/store/{id?}', [ProfessionController::class, 'store'])->name('store');
    Route::get('/records/{from}/{to}/{keyword?}', [ProfessionController::class, 'records'])->name('records');
    Route::post('/search', [ProfessionController::class, 'search'])->name('search');
    Route::get('/show/{id}', [ProfessionController::class, 'show'])->name('show');
    Route::delete('/destroy/{id}', [ProfessionController::class, 'destroy'])->name('destroy');
});

Route::middleware(['auth'])->prefix('grade')->name('grade.')->group(function () {
    Route::get('/list', [GradeController::class, 'index'])->name('list');
    Route::get('/form/{id?}', [GradeController::class, 'form'])->name('form');
    Route::post('/store/{id?}', [GradeController::class, 'store'])->name('store');
    Route::get('/records/{from}/{to}/{keyword?}', [GradeController::class, 'records'])->name('records');
    Route::post('/search', [GradeController::class, 'search'])->name('search');
    Route::get('/show/{id}', [GradeController::class, 'show'])->name('show');
    Route::delete('/destroy/{id}', [GradeController::class, 'destroy'])->name('destroy');
});

Route::middleware(['auth'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/list', [TeacherController::class, 'index'])->name('list');
    Route::get('/form/{id?}', [TeacherController::class, 'form'])->name('form');
    Route::post('/store/{id?}', [TeacherController::class, 'store'])->name('store');
    Route::get('/records/{from}/{to}/{keyword?}', [TeacherController::class, 'records'])->name('records');
    Route::post('/search', [TeacherController::class, 'search'])->name('search');
    Route::get('/show/{id}', [TeacherController::class, 'show'])->name('show');
    Route::delete('/destroy/{id}', [TeacherController::class, 'destroy'])->name('destroy');
});

Route::middleware(['auth'])->prefix('assignee')->name('assignee.')->group(function () {
    Route::get('/list', [AssigneeController::class, 'index'])->name('list');
    Route::get('/form/{id?}', [AssigneeController::class, 'form'])->name('form');
    Route::post('/store/{id?}', [AssigneeController::class, 'store'])->name('store');
    Route::get('/records/{from}/{to}/{keyword?}', [AssigneeController::class, 'records'])->name('records');
    Route::post('/search', [AssigneeController::class, 'search'])->name('search');
    Route::get('/show/{id}', [AssigneeController::class, 'show'])->name('show');
    Route::delete('/destroy/{id}', [AssigneeController::class, 'destroy'])->name('destroy');
});


Route::middleware(['auth'])->prefix('assistance')->name('assistance.')->group(function () {
    Route::get('/list', [AssistanceController::class, 'index'])->name('list');
    Route::get('/form/{id?}', [AssistanceController::class, 'form'])->name('form');
    Route::post('/store/{id?}', [AssistanceController::class, 'store'])->name('store');
    Route::get('/records/{from}/{to}/{keyword?}', [AssistanceController::class, 'records'])->name('records');
    Route::post('/search', [AssistanceController::class, 'search'])->name('search');
    Route::get('/show/{id}', [AssistanceController::class, 'show'])->name('show');
    Route::delete('/destroy/{id}', [AssistanceController::class, 'destroy'])->name('destroy');
    Route::post('/opening', [AssistanceController::class, 'opening'])->name('opening');
    Route::post('/closing', [AssistanceController::class, 'closing'])->name('closing');
    //Route::get('attendance/validate/{dni}/{late?}',  [AssistanceController::class, 'validateAttendance'])->name('attendance.validate');
    Route::get(
        '/absents/records/{from}/{to}/{keyword?}',
        [AssistanceController::class, 'absents']
    )->name('absents.records');
    Route::get('/take', [AssistanceController::class, 'take'])->name('take');
    Route::post('/attendance/validate', [AssistanceController::class, 'validateAttendance'])->name('attendance.validate');
});


Route::middleware(['auth'])->prefix('schedule')->name('schedule.')->group(function () {
    Route::get('/list', [ScheduleController::class, 'index'])->name('list');
    Route::get('/form/{id?}', [ScheduleController::class, 'form'])->name('form');
    Route::post('/store/{id?}', [ScheduleController::class, 'store'])->name('store');
    Route::get('/records/{from}/{to}/{keyword?}', [ScheduleController::class, 'records'])->name('records');
    Route::post('/search', [ScheduleController::class, 'search'])->name('search');
    Route::get('/show/{id}', [ScheduleController::class, 'show'])->name('show');
    Route::delete('/destroy/{id}', [ScheduleController::class, 'destroy'])->name('destroy');
});


Route::middleware(['auth'])->prefix('grade_schedule')->name('grade_schedule.')->group(function () {
    Route::get('/list', [GradeScheduleController::class, 'index'])->name('list');
    Route::get('/form/{id?}', [GradeScheduleController::class, 'form'])->name('form');
    Route::post('/store/{id?}', [GradeScheduleController::class, 'store'])->name('store');
    Route::get('/records/{from}/{to}/{keyword?}', [GradeScheduleController::class, 'records'])->name('records');
    Route::post('/search', [GradeScheduleController::class, 'search'])->name('search');
    Route::get('/show/{id}', [GradeScheduleController::class, 'show'])->name('show');
    Route::delete('/destroy/{id}', [GradeScheduleController::class, 'destroy'])->name('destroy');

    Route::get('/sections', [GradeScheduleController::class, 'sections']);
    Route::get('/by-schedule/{codschedule}', [GradeScheduleController::class, 'gradesBySchedule'])->name('by-schedule');
});


Route::middleware(['auth'])->prefix('period')->name('period.')->group(function () {
    Route::get('/list', [GradeScheduleController::class, 'index'])->name('list');
    Route::get('/form/{id?}', [GradeScheduleController::class, 'form'])->name('form');
    Route::post('/store/{id?}', [GradeScheduleController::class, 'store'])->name('store');
    Route::get('/records/{from}/{to}/{keyword?}', [GradeScheduleController::class, 'records'])->name('records');
    Route::post('/search', [GradeScheduleController::class, 'search'])->name('search');
    Route::get('/show/{id}', [GradeScheduleController::class, 'show'])->name('show');
    Route::delete('/destroy/{id}', [GradeScheduleController::class, 'destroy'])->name('destroy');

});

Route::middleware(['auth'])->prefix('justification')->name('justification.')->group(function () {
    Route::get('/list', [JustificationController::class, 'index'])->name('list');
    Route::get('/form/{id?}', [JustificationController::class, 'form'])->name('form');
    Route::post('/store/{id?}', [JustificationController::class, 'store'])->name('store');
    Route::get('/records/{from}/{to}/{keyword?}', [JustificationController::class, 'records'])->name('records');
    Route::post('/search', [JustificationController::class, 'search'])->name('search');
    Route::get('/show/{id}', [JustificationController::class, 'show'])->name('show');
    Route::delete('/destroy/{id}', [JustificationController::class, 'destroy'])->name('destroy');

});


Route::middleware(['auth'])->prefix('user_schedule')->name('user_schedule.')->group(function () {
    Route::get('/list', [UserScheduleController::class, 'index'])->name('list');
    Route::get('/form/{id?}', [UserScheduleController::class, 'form'])->name('form');
    Route::post('/store/{id?}', [UserScheduleController::class, 'store'])->name('store');
    Route::get('/records/{from}/{to}/{keyword?}', [UserScheduleController::class, 'records'])->name('records');
    Route::post('/search', [UserScheduleController::class, 'search'])->name('search');
    Route::get('/show/{id}', [UserScheduleController::class, 'show'])->name('show');
    Route::delete('/destroy/{id}', [UserScheduleController::class, 'destroy'])->name('destroy');

});

///---------- RUTAS PARA REPORTES ---------
Route::middleware(['auth'])->prefix('report')->name('report.')->group(function () {
    Route::get('/weeking',            [WeekingReportController::class, 'index'])->name('weeking.list');
    Route::get('/weeking/summary/records/{from}/{to}/{keyword?}', [WeekingReportController::class, 'summary'])->name('weeking.summary');
    Route::get('/weeking/records/{from}/{to}/{keyword?}',         [WeekingReportController::class, 'records'])->name('weeking.records');
    Route::get('/weeking/sections',   [WeekingReportController::class, 'sectionsByGrade'])->name('weeking.sections');
    Route::get('/weeking/export/pdf', [WeekingReportController::class, 'exportPdf'])->name('weeking.export.pdf');
});



///---------- RUTAS PARA INVITADO---------

Route::prefix('guest')->name('guest.')->group(function () {

    // Vista principal (ya la tienes)x
    Route::get('/', [GuestController::class, 'index'])->name('index');

    // ── API endpoints públicos para la vista de invitado ──────────────────
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('sessions', [GuestApiController::class, 'sessions'])->name('sessions');
        Route::get('records',  [GuestApiController::class, 'records'])->name('records');
        Route::get('student',  [GuestApiController::class, 'student'])->name('student');
        Route::get('sections', [GuestApiController::class, 'sections'])->name('sections');
    });
});
