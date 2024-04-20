<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminCheckMiddleware;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\ProfileController;
use App\Http\Controllers\admin\AuthController as AdminAuthController;
use App\Http\Controllers\admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\admin\CourseController as AdminCourseController;
use App\Http\Controllers\admin\CourseSessionController as AdminCourseSessionController;
use App\Http\Controllers\admin\CourseSectionController as AdminCourseSectionController;
use App\Http\Controllers\admin\CourseCommentController as AdminCourseCommentController;
use App\Http\Controllers\admin\NotificationController as AdminNotificationController;



Route::prefix('v1')->group(function () {
    Route::group(['prefix' => 'auth'], function ($router) {
        Route::post('login', [AuthController::class,'login'])->name('login');
        Route::get('login', [AuthController::class,'loginapp']);
        Route::post('verify', [AuthController::class,'verify']);
        Route::post('user-referral', [AuthController::class,'userReferral'])->middleware('auth:api');
        
        
    });
    Route::group(['prefix' => 'user'], function ($router) {
        Route::get('detail', [AuthController::class,'userDetail'])->middleware('auth:api');
        
    });
    Route::group(['prefix' => 'profile'], function ($router) {
        Route::post('save-avatar', [ProfileController::class,'saveAvatar'])->middleware('auth:api');
        Route::put('update', [ProfileController::class, 'updateUserData'])->middleware('auth:api');
    });
    Route::group(['prefix' => 'category'], function ($router) {
        Route::post('save-avatar', [ProfileController::class,'saveAvatar'])->middleware('auth:api');
        Route::put('update', [ProfileController::class, 'updateUserData'])->middleware('auth:api');
    });
    
});
Route::prefix('admin')->group(function () {
    Route::group(['prefix' => 'auth'], function ($router) {
        Route::post('login', [AdminAuthController::class,'Adminlogin'])->name('login');
    });
    Route::group(['prefix' => 'categories','middleware'=>['auth:admin',AdminCheckMiddleware::class]], function ($router) {
        Route::get('/', [AdminCategoryController::class, 'getCategories']);
        Route::post('/', [AdminCategoryController::class, 'StoreCategory'])->name('categories.StoreCategory');
        Route::get('/{id}', [AdminCategoryController::class, 'singleCategory'])->name('categories.singleCategory');
        Route::put('/{id}', [AdminCategoryController::class, 'updateCategory'])->name('categories.updateCategory');
        Route::delete('/{id}', [AdminCategoryController::class, 'destroyCategory'])->name('categories.destroyCategory');
    });
  
    Route::group(['prefix' => 'courses', 'middleware' => ['auth:admin',AdminCheckMiddleware::class]], function () {
        Route::get('/', [AdminCourseController::class, 'getCourses'])->name('courses.index');
        Route::post('/', [AdminCourseController::class, 'StoreCourse'])->name('courses.store');
        Route::get('/{id}', [AdminCourseController::class, 'singleCourse'])->name('courses.show');
        Route::put('/{id}', [AdminCourseController::class, 'updateCourse'])->name('courses.update');
        Route::post('/{id}/image', [AdminCourseController::class, 'updateCourseImage'])->name('courses.updateImage');
        Route::delete('/{id}', [AdminCourseController::class, 'destroyCourse'])->name('courses.destroy');
    });
    Route::group(['prefix' => 'sectioncourses', 'middleware' => ['auth:admin',AdminCheckMiddleware::class]], function () {
        Route::get('/', [AdminCourseSectionController::class, 'getSectionCourses'])->name('SectionCourse.index');
        Route::post('/', [AdminCourseSectionController::class, 'StoreSectionCourse'])->name('SectionCourse.store');
        Route::get('/{id}', [AdminCourseSectionController::class, 'singleSectionCourse'])->name('SectionCourse.show');
        Route::put('/{id}', [AdminCourseSectionController::class, 'updateSectionCourse'])->name('SectionCourse.update');
        Route::delete('/{id}', [AdminCourseSectionController::class, 'destroySectionCourse'])->name('SectionCourse.destroy');
    });
    Route::group(['prefix' => 'sessioncourses', 'middleware' => ['auth:admin',AdminCheckMiddleware::class]], function () {
        Route::get('/', [AdminCourseSessionController::class, 'getSessionCourses'])->name('SessionCourse.index');
        Route::post('/', [AdminCourseSessionController::class, 'StoreSessionCourse'])->name('SessionCourse.store');
        Route::get('/{id}', [AdminCourseSessionController::class, 'singleSessionCourse'])->name('SessionCourse.show');
        Route::put('/{id}', [AdminCourseSessionController::class, 'updateSessionCourse'])->name('SessionCourse.update');
        Route::post('/{id}/file', [AdminCourseSessionController::class, 'updateCourseFile'])->name('SessionCourse.updateCourseFile');
        Route::delete('/{id}', [AdminCourseSessionController::class, 'destroySessionCourse'])->name('SessionCourse.destroy');
    });
    Route::group(['prefix' => 'commentcourses', 'middleware' => ['auth:admin',AdminCheckMiddleware::class]], function () {
        Route::get('/', [AdminCourseCommentController::class, 'getCommentCourses'])->name('CommentCourse.index');
        Route::get('/{id}', [AdminCourseCommentController::class, 'singleCommentCourse'])->name('CommentCourse.show');
        Route::delete('/{id}', [AdminCourseCommentController::class, 'destroyCommentCourse'])->name('CommentCourse.destroy');
        Route::patch('/{id}/show', [AdminCourseCommentController::class, 'ShowCommentCourse'])->name('CommentCourse.ShowCommentCourse');
        Route::patch('/{id}/hide', [AdminCourseCommentController::class, 'HideCommentCourse'])->name('CommentCourse.HideCommentCourse');
    });
    Route::group(['prefix' => 'notifications', 'middleware' => ['auth:admin',AdminCheckMiddleware::class]], function () {
        Route::get('/', [AdminNotificationController::class, 'getNotifications'])->name('Notification.index');
        Route::post('/', [AdminNotificationController::class, 'StoreNotification'])->name('Notification.store');
        Route::get('/{id}', [AdminNotificationController::class, 'singleNotification'])->name('Notification.show');
        Route::put('/{id}', [AdminNotificationController::class, 'updateNotification'])->name('Notification.update');
        Route::delete('/{id}', [AdminNotificationController::class, 'destroyNotification'])->name('Notification.destroy');
        Route::patch('/{id}/read', [AdminNotificationController::class, 'ReadNotification'])->name('Notification.ReadNotification');
        Route::patch('/{id}/unread', [AdminNotificationController::class, 'InreadNotification'])->name('Notification.InreadNotification');
    });
});