<?php

use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SettlementController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Broadcasting auth (custom - no Laravel auth required)
Broadcast::routes(['middleware' => ['web']]);


// Landing / Create Room
Route::get('/', [RoomController::class, 'create'])->name('home');
Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');

// Room routes (using code as the identifier)
Route::get('/{room:code}', [RoomController::class, 'show'])->name('rooms.show');
Route::get('/{room:code}/join', [RoomController::class, 'join'])->name('rooms.join');
Route::post('/{room:code}/join', [RoomController::class, 'storeParticipant'])->name('rooms.join.store');
Route::post('/{room:code}/lock', [RoomController::class, 'lock'])->name('rooms.lock');
Route::post('/{room:code}/unlock', [RoomController::class, 'unlock'])->name('rooms.unlock');

// Participants routes
Route::post('/{room:code}/participants', [RoomController::class, 'storeVirtualParticipant'])->name('participants.store');
Route::put('/{room:code}/participants/alias', [SettlementController::class, 'updateAlias'])->name('participants.updateAlias');
Route::delete('/{room:code}/participants/{participant}', [RoomController::class, 'destroyParticipant'])->name('participants.destroy');

// Expense routes
Route::post('/{room:code}/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
Route::put('/{room:code}/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
Route::delete('/{room:code}/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

// Settlement routes
Route::get('/{room:code}/settlements', [SettlementController::class, 'all'])->name('settlements.all');
Route::get('/{room:code}/settlements/my', [SettlementController::class, 'my'])->name('settlements.my');
Route::post('/{room:code}/settlements/{settlement}/pay', [SettlementController::class, 'markAsPaid'])->name('settlements.pay');
