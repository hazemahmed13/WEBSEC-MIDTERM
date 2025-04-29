<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\UsersController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\UserCreditController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProductLikeController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\GithubAuthController;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\FacebookAuthController;

Route::get('register', [UsersController::class, 'register'])->name('register');
Route::post('register', [UsersController::class, 'doRegister'])->name('do_register');
Route::get('login', [UsersController::class, 'login'])->name('login');
Route::post('login', [UsersController::class, 'doLogin'])->name('do_login');
Route::post('logout', [UsersController::class, 'doLogout'])->name('logout');
Route::get('users', [UsersController::class, 'list'])->name('users');
Route::get('users/profile/{user?}', [UsersController::class, 'profile'])->name('users.profile');
Route::get('users/edit/{user?}', [UsersController::class, 'edit'])->name('users_edit');
Route::post('users/save/{user}', [UsersController::class, 'save'])->name('users_save');
Route::get('users/delete/{user}', [UsersController::class, 'delete'])->name('users_delete');
Route::get('users/edit_password/{user?}', [UsersController::class, 'editPassword'])->name('edit_password');
Route::post('users/save_password/{user}', [UsersController::class, 'savePassword'])->name('save_password');

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/multable', function (Request $request) {
    $j = $request->number??5;
    $msg = $request->msg;
    return view('multable', compact("j", "msg"));
})->name('multiplication-table');

Route::get('/even', function () {
    return view('even');
})->name('even-numbers');

Route::get('/prime', function () {
    return view('prime');
})->name('prime-numbers');

Route::get('/test', function () {
    return view('test');
});

// Email Verification Routes
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('products.index')->with('success', 'Email verified successfully!');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('success', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Protected Routes that require email verification
Route::middleware(['auth', 'verified'])->group(function () {
    // Product routes
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

    // Purchase routes
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::post('/products/{product}/purchase', [PurchaseController::class, 'store'])->name('purchases.store');

    // Product like routes
    Route::post('/products/{product}/like', [ProductLikeController::class, 'toggleLike'])
        ->name('products.like');

    // Employee routes for product management
    Route::middleware(['can:manage-products'])->group(function () {
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::post('/products/{product}/hold', [ProductController::class, 'toggleHold'])
            ->name('products.hold')
            ->middleware('can:hold_products');
    });

    // Credit management routes
    Route::middleware(['can:manage-customer-credits'])->group(function () {
        Route::get('/users/{user}/credits', [UserCreditController::class, 'show'])->name('credits.show');
        Route::put('/users/{user}/credits', [UserCreditController::class, 'update'])->name('credits.update');
    });

    // User Management Routes
    Route::get('/users/manage', [UsersController::class, 'manageUsers'])->name('users.manage');
    Route::post('/users/{user}/manage-credit', [UsersController::class, 'manageCredit'])->name('users.manage-credit');

    // Employee Management Routes
    Route::middleware(['can:manage-employees'])->group(function () {
        Route::resource('employees', EmployeeController::class);
    });

    Route::get('/password/change', [PasswordController::class, 'showChangeForm'])->name('password.change');
    Route::post('/password/update', [PasswordController::class, 'update'])->name('password.update');
});

// Google Authentication Routes
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('google.callback');

// GitHub Authentication Routes
Route::get('auth/github', [GithubAuthController::class, 'redirect'])->name('github.login');
Route::get('auth/github/callback', [GithubAuthController::class, 'callback'])->name('github.callback');

// Facebook Authentication Routes
Route::get('auth/facebook', [FacebookAuthController::class, 'redirect'])->name('facebook.login');
Route::get('auth/facebook/callback', [FacebookAuthController::class, 'callback'])->name('facebook.callback');

Route::get('/auth/{provider}/redirect', function ($provider) {
    return Socialite::driver($provider)->redirect();
})->name('social.redirect');

Route::get('/auth/{provider}/callback', 'App\Http\Controllers\Auth\SocialAuthController@callback')
    ->name('social.callback');

// Password Reset Routes
Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
    ->middleware('guest')
    ->name('password.request');

Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->middleware('guest')
    ->name('password.email');

Route::get('reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])
    ->middleware('guest')
    ->name('password.reset');

Route::post('reset-password', [ForgotPasswordController::class, 'reset'])
    ->middleware('guest')
    ->name('password.update');

