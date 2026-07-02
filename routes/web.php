<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\MemberDashboardController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\PenaltyController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

// GUEST ROUTES
Route::middleware('guest')->group(function () {
    Route::get('/', fn() => redirect()->route('login'));
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

// AUTH ROUTES
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    
    // ADMIN ROUTES
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('books', BookController::class);
        Route::get('members', [MemberController::class, 'index'])->name('members.index');
        Route::get('members/create', [MemberController::class, 'create'])->name('members.create');
        Route::post('members', [MemberController::class, 'store'])->name('members.store');
        Route::put('members/{member}/toggle-status', [MemberController::class, 'toggleStatus'])->name('members.toggle-status');
        Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
        Route::post('transactions', [TransactionController::class, 'store'])->name('transactions.store');
        Route::put('transactions/{transaction}/return', [TransactionController::class, 'returnBook'])->name('transactions.return');
        Route::get('penalties', [PenaltyController::class, 'index'])->name('penalties.index');
        Route::put('penalties/{penalty}/pay', [PenaltyController::class, 'payPenalty'])->name('penalties.pay');
    });
    
    // MEMBER ROUTES
    Route::middleware('member')->prefix('member')->name('member.')->group(function () {
    Route::get('/dashboard', [MemberDashboardController::class, 'index'])->name('dashboard');
    Route::get('/books', [BookController::class, 'memberIndex'])->name('books.index');
    Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
    Route::get('/my-borrows', [MemberDashboardController::class, 'myBorrows'])->name('borrows');
    Route::get('/my-penalties', [MemberDashboardController::class, 'myPenalties'])->name('penalties');
});
    
    // Redirect
    Route::get('/dashboard', function () {
        return auth()->user()->isAdmin 
            ? redirect()->route('admin.dashboard') 
            : redirect()->route('member.dashboard');
    })->name('dashboard');
});