<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminCheckMiddleware;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\PaymentController;
use App\Http\Controllers\api\CourseController;
use App\Http\Controllers\api\FAQController;
use App\Http\Controllers\api\ProfileController;
use App\Http\Controllers\api\AboutUsController;
use App\Http\Controllers\api\NotificationController;
use App\Http\Controllers\admin\AuthController as AdminAuthController;
use App\Http\Controllers\admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\admin\CourseController as AdminCourseController;
use App\Http\Controllers\admin\CourseSessionController as AdminCourseSessionController;
use App\Http\Controllers\admin\CourseSectionController as AdminCourseSectionController;
use App\Http\Controllers\admin\CourseCommentController as AdminCourseCommentController;
use App\Http\Controllers\admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\admin\FAQController as AdminFAQController;
use App\Http\Controllers\admin\UserController as AdminUserController;
use App\Http\Controllers\admin\AboutUsController as AdminAboutUsController;



Route::prefix('v1')->group(function () {
    Route::get('/payment/verify', [PaymentController::class,'verifyPayment']);
    Route::group(['prefix' => 'auth'], function ($router) {
        Route::post('login', [AuthController::class,'login'])->name('login');
        Route::get('login', [AuthController::class,'loginapp']);
        Route::post('verify', [AuthController::class,'verify']);
        Route::post('user-referral', [AuthController::class,'userReferral'])->middleware('auth:api');
        
        
    });
    Route::group(['prefix' => 'user'], function ($router) {
        Route::get('detail', [AuthController::class,'userDetail'])->middleware('auth:api');
        Route::get('/notifications', [UserController::class, 'userNotifications'])->middleware('auth:api');
    });
    Route::group(['prefix' => 'profile'], function ($router) {
        Route::post('save-avatar', [ProfileController::class,'saveAvatar'])->middleware('auth:api');
        Route::put('update', [ProfileController::class, 'updateUserData'])->middleware('auth:api');
        Route::get('courses', [ProfileController::class, 'UserCourses'])->middleware('auth:api');
        Route::get('payments', [ProfileController::class, 'paymentHistory'])->middleware('auth:api');
    });
    Route::group(['prefix' => 'category'], function ($router) {
        Route::post('save-avatar', [ProfileController::class,'saveAvatar'])->middleware('auth:api');
        Route::put('update', [ProfileController::class, 'updateUserData'])->middleware('auth:api');
    });
    Route::group(['prefix' => 'notifications'], function () {
        Route::get('/', [NotificationController::class, 'getNotifications'])->name('Notification.index');
        Route::get('/{id}', [NotificationController::class, 'singleNotification'])->name('Notification.show');
    });
    Route::group(['prefix' => 'courses'], function () {
        Route::get('/', [CourseController::class, 'getCourses'])->middleware('auth:api');
        Route::get('/{id}', [CourseController::class, 'singleCourse'])->middleware('auth:api');
        Route::get('/{id}/comments', [CourseController::class, 'getCommentsCourse'])->middleware('auth:api');
        Route::post('/{id}/comments', [CourseController::class, 'setCommentsCourse'])->middleware('auth:api');
        Route::post('/commentlike', [CourseController::class, 'setCommentLikeCourse'])->middleware('auth:api');
        Route::post('/{id}/payment', [CourseController::class, 'setpaymentCourse'])->middleware('auth:api');
    });
    Route::group(['prefix' => 'faqs'], function () {
        Route::get('/', [FAQController::class, 'getFaqs'])->middleware('auth:api');
        Route::get('/{id}', [FAQController::class, 'singleFaq'])->middleware('auth:api');  
    });
    Route::group(['prefix' => 'wallet'], function () {
        Route::post('/deposit', [PaymentController::class, 'deposit'])->middleware('auth:api');
        Route::get('/verify', [PaymentController::class, 'walletverify']);
        Route::post('/withdraw', [PaymentController::class, 'withdraw'])->middleware('auth:api');  
    });
    Route::group(['prefix' => 'aboutus'], function () {
        Route::get('/', [AboutUsController::class, 'singleAboutUs'])->name('AboutUs.show');   
    });

});
Route::prefix('admin')->group(function () {
    Route::group(['prefix' => 'auth'], function ($router) {
        Route::post('login', [AdminAuthController::class,'Adminlogin'])->name('login');
       
    });
    Route::group(['prefix' => 'profile','middleware'=>['auth:admin',AdminCheckMiddleware::class]], function ($router) {
        Route::get('/', [AdminAuthController::class, 'AdminDetail']);

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
    });
    Route::group(['prefix' => 'faqs', 'middleware' => ['auth:admin',AdminCheckMiddleware::class]], function () {
        Route::get('/', [AdminFAQController::class, 'getFaqs'])->name('Faq.index');
        Route::post('/', [AdminFAQController::class, 'StoreFaq'])->name('Faq.store');
        Route::get('/{id}', [AdminFAQController::class, 'singleFaq'])->name('Faq.show');
        Route::put('/{id}', [AdminFAQController::class, 'updateFaq'])->name('Faq.update');
        Route::delete('/{id}', [AdminFAQController::class, 'destroyFaq'])->name('Faq.destroy');
    });
    Route::group(['prefix' => 'aboutus', 'middleware' => ['auth:admin',AdminCheckMiddleware::class]], function () {
        Route::post('/', [AdminAboutUsController::class, 'StoreAboutUs'])->name('AboutUs.store');
        Route::get('/{id}', [AdminAboutUsController::class, 'singleAboutUs'])->name('AboutUs.show');
        Route::put('/{id}', [AdminAboutUsController::class, 'updateAboutUs'])->name('AboutUs.update');
        Route::delete('/{id}', [AdminAboutUsController::class, 'destroyAboutUs'])->name('AboutUs.destroy');
    });
    Route::group(['prefix' => 'users', 'middleware' => ['auth:admin',AdminCheckMiddleware::class]], function () {
        Route::get('/', [AdminUserController::class, 'getUsers'])->name('Users.index');
        Route::get('/{id}', [AdminUserController::class, 'singleUser'])->name('Users.show');
        Route::put('/{id}', [AdminUserController::class, 'updateUser'])->name('Users.update');
        Route::delete('/{id}', [AdminUserController::class, 'destroyUser'])->name('Users.destroy');
    });
});