<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\IndexController;
use App\Http\Controllers\Api\LeavesController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\TaskController;
use App\Models\Designation;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('admin')->middleware(['auth:api', 'isAdmin'])->group(function () {

    Route::post('/admin/signup', 'App\Http\Controllers\Api\IndexController@adminsignup');
    Route::get('applied/leave', [LeavesController::class, 'userAppliedLeaves']);
    Route::put('leave/approved/reject/{id}', [LeavesController::class, 'leaveApprovedReject']);
    Route::get('users', [UserController::class, 'allUsers']);
    Route::post('notification', 'App\Http\Controllers\Api\NotificationController@notification');
    Route::post('time/schedule', 'App\Http\Controllers\Api\ScheduleController@createSchedule');
    Route::get('time/schedule', 'App\Http\Controllers\Api\ScheduleController@showSchedule');
    Route::get('time/schedule/{id}', 'App\Http\Controllers\Api\ScheduleController@showSingleSchedule');
    Route::put('time/schedule/{id}', 'App\Http\Controllers\Api\ScheduleController@updateSchedule');
    Route::delete('time/schedule/{id}', 'App\Http\Controllers\Api\ScheduleController@deleteSchedule');
    Route::get('/today/leave/checkIn', [AttendanceController::class, 'todayLeaveOrCheckIn']);
    Route::get('monthly/data/{id}', 'App\Http\Controllers\Api\AttendanceController@getMonthlyData');
    Route::get('weekly/data/{id}', 'App\Http\Controllers\Api\AttendanceController@weeklyAttendance');
    Route::post('task', 'App\Http\Controllers\Api\TaskController@createTask');
    Route::get('task', 'App\Http\Controllers\Api\TaskController@showTask');
    Route::get('task/{id}', 'App\Http\Controllers\Api\TaskController@showSingleTask');
    Route::put('task/status/{id}', 'App\Http\Controllers\Api\TaskController@updateStatus');
    Route::put('task/{id}', 'App\Http\Controllers\Api\TaskController@updateTask');
    Route::delete('task/{id}', 'App\Http\Controllers\Api\TaskController@deleteTask');
    // Route::get('task/user/{id}', 'App\Http\Controllers\Api\TaskController@singleUserTaskDuration');
    // Route::get('user/week/duration/{id}', 'App\Http\Controllers\Api\IndexController@userdetails');
    Route::get('user/details/{id}', 'App\Http\Controllers\Api\ProfileController@userDetailsShowToAdmin');
    Route::get('show/contactUs', 'App\Http\Controllers\Api\ContactUsController@showMessageToAdmin');
    Route::post('user/signup', 'App\Http\Controllers\Api\IndexController@usersignup');
    Route::post('designation', 'App\Http\Controllers\Api\DesignationController@createDesignation');
    Route::get('designation', 'App\Http\Controllers\Api\DesignationController@showDesignation');
    Route::get('designation/{id}', 'App\Http\Controllers\Api\DesignationController@showSingleDesignation');
    Route::put('designation/{id}', 'App\Http\Controllers\Api\DesignationController@updateDesignation');
    Route::delete('designation/{id}', 'App\Http\Controllers\Api\DesignationController@deleteDesignation');
    Route::put('user/details/{id}', 'App\Http\Controllers\Api\ProfileController@updateUserDetails');
    Route::post('leave/type', 'App\Http\Controllers\Api\LeaveTypesController@createLeaveType');
    Route::get('leave/type', 'App\Http\Controllers\Api\LeaveTypesController@showLeaveTypeToAdmin');
    Route::get('leave/type/{id}', 'App\Http\Controllers\Api\LeaveTypesController@showSingleLeaveTypeToAdmin');
    Route::put('leave/type/{id}', 'App\Http\Controllers\Api\LeaveTypesController@updateLeaveType');
    Route::delete('leave/type/{id}', 'App\Http\Controllers\Api\LeaveTypesController@deleteLeaveType');
    Route::put('user/leave/{id}', 'App\Http\Controllers\Api\UserLeaveController@updateUserLeave');
    Route::get('users/leave', 'App\Http\Controllers\Api\UserLeaveController@showUsersLeave');
    Route::get('user/leave/{id}', 'App\Http\Controllers\Api\UserLeaveController@showSingleUserLeave');
    Route::get('admin', 'App\Http\Controllers\Api\AdminController@getAdmin');
    Route::get('admin/{id}', 'App\Http\Controllers\Api\AdminController@getSingleAdmin');
    Route::put('admin/{id}', 'App\Http\Controllers\Api\AdminController@updateAdmin');
    Route::get('image/requests', 'App\Http\Controllers\Api\ImageController@imageRequests');
    Route::get('image/request/{id}', 'App\Http\Controllers\Api\ImageController@getSingleImageRequest');
    Route::put('image/approve/reject/{id}', 'App\Http\Controllers\Api\ImageController@imageApproveReject');
    Route::put('employee/status/{id}', 'App\Http\Controllers\Api\ProfileController@updateEmployeeStatus');
    Route::get('checkIn/time/{id}', 'App\Http\Controllers\Api\ProfileController@checkInTimeShowToAdmin');
    Route::get('dashboard', 'App\Http\Controllers\Api\DashboardController@dashboardCount');
    Route::get('dashboard/statistics', 'App\Http\Controllers\Api\DashboardController@dashboardStatistics');
    Route::get('dateWise/{id}', 'App\Http\Controllers\Api\ProfileController@getCheckBreakTimefromDatePicker');
    Route::get('active/users', 'App\Http\Controllers\Api\AttendanceController@activeUser');
    Route::put('checkInOut/time/{id}', 'App\Http\Controllers\Api\AttendanceController@updateCheckInOutTime');

});

Route::prefix('user')->middleware(['auth:api', 'isUser'])->group(function () {
    Route::post('check/in/out', [AttendanceController::class, 'userCheckInOut']);
    // Route::get('break/in/out', [AttendanceController::class, 'breakInOut']);
    Route::get('notification', 'App\Http\Controllers\Api\NotificationController@showNotification');
    Route::post('apply/leave', 'App\Http\Controllers\Api\LeavesController@applyLeaves');
    Route::get('leaves', 'App\Http\Controllers\Api\LeavesController@showleaves');
    Route::put('leaves/{id}', 'App\Http\Controllers\Api\LeavesController@updateleaves');
    Route::get('task', 'App\Http\Controllers\Api\TaskController@showTask');
    Route::get('task/{id}', 'App\Http\Controllers\Api\TaskController@showSingleTask');
    Route::post('task/duration/{id}', 'App\Http\Controllers\Api\TaskDurationController@taskDuration');
    // Route::get('user/details', 'App\Http\Controllers\Api\IndexController@userdetails');
    Route::get('task/notification', 'App\Http\Controllers\Api\TaskNotificationController@showTaskNotification');
    Route::post('contactUs', 'App\Http\Controllers\Api\ContactUsController@createMessage');
    Route::get('contactUs', 'App\Http\Controllers\Api\ContactUsController@showMessageToUser');
    // Route::put('update/contact/us/{id}', [ConatctUsController::class, 'updateMessage']);
    // Route::delete('delete/contact/us/{id}', [ConatctUsController::class, 'deleteMessage']);
    Route::get('remaining/leaves', 'App\Http\Controllers\Api\UserLeaveController@userRemainingLeaves');
    Route::post('change/image', 'App\Http\Controllers\Api\ImageController@changeImage');
    Route::get('user/details', 'App\Http\Controllers\Api\ProfileController@userDetailsShowToUser');
    Route::get('checkIn/time', 'App\Http\Controllers\Api\ProfileController@checkInTimeShowToUser');
    Route::get('weekly/data', 'App\Http\Controllers\Api\AttendanceController@weeklyAttendance');

});

Route::post('/admin/signup', 'App\Http\Controllers\Api\IndexController@adminsignup');
Route::post('admin/login', 'App\Http\Controllers\Api\IndexController@loginadmin');
Route::post('user/login', 'App\Http\Controllers\Api\IndexController@loginuser');
Route::get('checkIn/csv/{data}', 'App\Http\Controllers\Api\ExportController@getCheckInCSV');


// Route::put('user/change/password/{id}', 'App\Http\Controllers\Api\IndexController@updateUserDetails');
// Route::post('user/new/password', 'App\Http\Controllers\Api\IndexController@newPassword');
// Route::post('user/forget', 'App\Http\Controllers\Api\IndexController@forgetPassword');
