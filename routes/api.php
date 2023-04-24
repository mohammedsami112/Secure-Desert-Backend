<?php

use App\Http\Controllers\AchievementsController;
use App\Http\Controllers\BlogsController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CourseEnrollmentController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\FirebaseTokenController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\LessonsController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamMembersController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('login', [SessionsController::class, 'login'])->name('login');
    Route::post('register', [SessionsController::class, 'register'])->name('register');

    // Show and Update Profile
    Route::get('profile', [SessionsController::class, 'view'])->name('profile');
    Route::post('profile/update', [SessionsController::class, 'update'])->name('profile.update');

    // Password
    Route::post('forgot-password', [SessionsController::class, 'forgotPassword'])->name('forgot-password');
    Route::post('reset-password', [SessionsController::class, 'resetPassword'])->name('reset-password');
    Route::get('verify-token', [SessionsController::class, 'verifyToken'])->name('verify-token');

    // Logout && Refresh
    Route::delete('logout', [SessionsController::class, 'logout'])->name('logout');
    Route::patch('refresh', [SessionsController::class, 'refresh'])->name('refresh');

    // Get Session Data && Destroy Session
    Route::get('sessions', [SessionsController::class, 'sessions'])->name('sessions');
    Route::delete('sessions/{session}/destroy', [SessionsController::class, 'destroySession'])->name('sessions.destroy');

    // Verify Email
    Route::get('account/verify/{token}', [SessionsController::class, 'verifyToken'])->name('verify');
    Route::delete('account/verify/again/{email}', [SessionsController::class, 'resendVerification'])->name('resend.verification');
});

// Added By Mohammed
Route::prefix('payment')->group(function () {
    Route::post('enrollment/subscribe', [CourseEnrollmentController::class, 'subscribe'])->name('enrollment.subscribe');
    Route::post('subscription/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
});

Route::middleware(['auth:users'])->group(function () {
    // Team & Team Members
    Route::apiResource('teams', TeamController::class);
    Route::apiResource('teams.members', TeamMembersController::class)->except(['update']);

    // Courese & Chapters $ Lessons
    Route::apiResource('courses', CoursesController::class);
    Route::apiResource('chapters', ChapterController::class);
    Route::apiResource('lessons', LessonsController::class);

    // News
    Route::apiResource('news', NewsController::class);

    // Blogs || Write ups
    Route::apiResource('blogs', BlogsController::class);

    // Tasks
    Route::apiResource('/tasks', TaskController::class);
    Route::post('submit/task', [TaskController::class, 'solve'])->name('task.submit');

    // Achievments
    Route::apiResource('achievments', AchievementsController::class);

    // Categories
    Route::apiResource('categories', CategoriesController::class);

    // Plans
    Route::apiResource('plans', PlanController::class);

    // Get Top Users According to Points
    Route::get('top/users/{limit?}', [UsersController::class, 'topUsers'])->where('limit', '[0-9]*')->name('users.top');

    // Profile & User Achievments and Courses
    Route::get('users', [UsersController::class, 'getAllUsers'])->name('users');
    Route::post('users/check-username', [UsersController::class, 'checkUsername'])->name('users.check-username');
    Route::post('user/toggle', [UsersController::class, 'toggleStatus'])->name('user.toggle'); // Added By Mohammed
    Route::post('user/change-password', [UsersController::class, 'changePassword'])->name('user.change.password'); // Added By Mohammed
    Route::get('user/profile', [ProfileController::class, 'show'])->name('user.profile');
    Route::get('user/achievments', [ProfileController::class, 'userAchievements'])->name('user.achievments');
    Route::get('user/courses', [ProfileController::class, 'userCourses'])->name('user.courses');
    Route::get('user/tasks', [ProfileController::class, 'userTasks'])->name('user.tasks');
    Route::get('user/tasks/achievments', [ProfileController::class, 'userTasksAchievment'])->name('user.tasks.achievments');
    Route::get('user/teams', [ProfileController::class, 'userTeams'])->name('user.teams');
    Route::get('user/admin/teams', [ProfileController::class, 'userAdminTeams'])->name('user.admin.teams');

    // Register firebase token
    Route::post('user/firebase', [FirebaseTokenController::class, 'update'])->name('firebase.token');
    Route::delete('user/firebase', [FirebaseTokenController::class, 'destroy'])->name('firebase.token');

    // Subscription
    Route::post('subscription/create', [SubscriptionController::class, 'create'])->name('subscription.create');
    // Route::get('subscription/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
    Route::get('subscription/check', [SubscriptionController::class, 'check'])->name('subscription.check');

    // Course subscription
    Route::post('enrollment/create', [CourseEnrollmentController::class, 'create'])->name('enrollment.create');
    // Route::get('enrollment/subscribe', [CourseEnrollmentController::class, 'subscribe'])->name('enrollment.subscribe');
    Route::get('enrollment/check', [CourseEnrollmentController::class, 'check'])->name('enrollment.check');

    // Invite actions
    Route::get('invite', [InviteController::class, 'index'])->name('invite.index');
    Route::post('invite/{invite}', [InviteController::class, 'accept'])->name('invite.accept');
    Route::delete('invite/{invite}', [InviteController::class, 'reject'])->name('invite.reject');

    // All Subscribers
    Route::get('subscribers/all', [UsersController::class, 'subscribers'])->name('subscribers.all');
});
