<?php

use App\Exports\RegisteredMembersExport;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\RegisteredMemberController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

Route::middleware(['auth:sanctum', 'active'])->group(function(){
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    //MEMBERS
    Route::get('/dashboard/stats', [MemberController::class, 'getStats']);

    //SETTINGS
    Route::get('/settings', [SettingController::class, 'index']);
    Route::put('/settings', [SettingController::class, 'update']);
    Route::post('/settings', [SettingController::class, 'store']);

    //REGISTERED MEMBERS
    Route::get('/registered-members', [RegisteredMemberController::class, 'index']);
    Route::post('/registered-member/batch-delete', [RegisteredMemberController::class, 'batchDelete']);
    Route::get('/registered-member-an/{account_number}', [RegisteredMemberController::class, 'showUsingAccountNumber']);
    Route::put('/member-update', [RegisteredMemberController::class, 'update']);

    //USER ACCOUNTS
    Route::get('/accounts', [UserController::class, 'index']);
    Route::post('/account', [AuthController::class, 'register']);
    Route::post('/account/batch-delete', [UserController::class, 'batchDelete']);

    //EXPORTS
    Route::prefix('/export')->group(function(){
        Route::get('/csv', function () {
            return Excel::download(new RegisteredMembersExport, 'registered_members.csv');
        });
        
        Route::get('/xlsx', function () {
            return Excel::download(new RegisteredMembersExport, 'registered_members.xlsx');
        });
        
        Route::get('/pdf', [RegisteredMemberController::class, 'exportPdf']);
        
        Route::get('/sql', [RegisteredMemberController::class, 'exportSql']);
    });
    
    //USER ACCOUNTS
    Route::put('/updateuser/{id}', [UserController::class, 'update']);
    Route::put('/changepassword/{id}', [UserController::class, 'changePassword']);
});

//MEMBER VALIDATION AND REGISTRATION FOR ONSITE AND PRE PREG ACCOUNTS
Route::middleware(['auth:sanctum', 'active'])->group(function(){
    Route::prefix('/logged')->group(function(){
        Route::post('/member', [MemberController::class, 'validate']);
        Route::post('/member-register', [RegisteredMemberController::class, 'store']);
    });
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/member', [MemberController::class, 'validate']);

Route::post('/member-register', [RegisteredMemberController::class, 'store']);
Route::get('/registered-member/{reference_number}', [RegisteredMemberController::class, 'show']);

Route::get('/test', function(){
    return response()->json([
        "message" => "success"
    ], 200);
});