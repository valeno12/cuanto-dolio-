<?php

use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing / Create Room
Route::get('/', [RoomController::class, 'create'])->name('home');
Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');

// Room routes (using code as the identifier)
Route::get('/{room:code}', [RoomController::class, 'show'])->name('rooms.show');
Route::get('/{room:code}/join', [RoomController::class, 'join'])->name('rooms.join');
Route::post('/{room:code}/join', [RoomController::class, 'storeParticipant'])->name('rooms.join.store');

// Participants routes
Route::post('/{room:code}/participants', [RoomController::class, 'storeVirtualParticipant'])->name('participants.store');

// Expense routes
Route::post('/{room:code}/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
Route::put('/{room:code}/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
Route::delete('/{room:code}/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
