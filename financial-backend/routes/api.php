<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\RecurringController;
use App\Http\Controllers\FixedTransactionController;
// use App\Http\Controllers\UserController;
use App\Http\Controllers\KeyController;
// use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AuthController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/fixedtransaction',[FixedTransactionController::class,'addFixedTransaction']);
Route::Patch('/fixedtransaction/{id}',[FixedTransactionController::class,'editFixedTransaction']);
Route::delete('/fixedtransaction/{id}',[FixedTransactionController::class,'deleteFixedTransaction']);
Route::get('/fixedtransaction/totals',[FixedTransactionController::class,'GetTotal']);
Route::get('/fixedtransaction',[FixedTransactionController::class,'getAllFixedTransactions']);
Route::get('/fixedtransaction/{id}',[FixedTransactionController::class,'getFixedTransactionById']);
Route::get('/fixedtransaction', [FixedTransactionController::class,'getBy']);



//UserController
// Route::Get('/user',[UserController::class,'getAllUser']);
// Route::Get('/user/{id}',[UserController::class,'getUser']);
// Route::post('/register', [UserController::class, 'register']);
// Route::post('/login', [UserController::class, 'login']);
// Route::middleware('auth:sanctum')->group(function () {
// Route::post('/logout', [UserController::class, 'logout']);
// });
// Route::delete('/user/{id}',[UserController::class,'destroyUser']);
// Route::Patch('/user/{id}',[UserController::class,'editUser']);

//KeyController
Route::Get('/key',[KeyController::class,'getAllFixed_Key']);
Route::Get('/key/{id}',[KeyController::class,'getFixed_key']);
Route::Post('/key',[KeyController::class,'CreatFixed_Key']);
Route::delete('/key/{id}',[KeyController::class,'destroyFixed_Key']);
Route::Patch('/key/{id}',[KeyController::class,'editFixed_Key']);

// AuthenticationController
// Route::post('register', [AuthController::class, 'register']);
// Route::post('login', [AuthController::class, 'login']);

// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('user', [AuthController::class, 'user']);
//     Route::post('logout', [AuthController::class, 'logout']);
// });


//Currency Routes
Route::get('/currency',[currencyController::class,'getAllCurrency']);
Route::get('/currency/{id}',[currencyController::class,'getCurrency']);
Route::post('/currency',[currencyController::class,'addCurrency']);
Route::patch('/currency/{id}',[currencyController::class,'editCurrency']);
Route::delete('/currency/{id}',[currencyController::class,'deleteCurrency']);


//Recurrings Routes
Route::get('/recurrings/totals',[RecurringController::class,'GetTotal']);
Route::get('/recurrings',[RecurringController::class,'index']);
Route::get('/recurrings/{id}',[RecurringController::class,'show']);
Route::post('/recurrings',[RecurringController::class,'store']);
Route::patch('/recurrings/{id}',[RecurringController::class,'edit']);
Route::delete('/recurrings/{id}',[RecurringController::class,'destroy']);


//Cataegories Routes
Route::get('/categories',[categoryController::class,'getAllCategory']);
Route::get('/categories/{id}',[categoryController::class,'getCategory']);
Route::post('/categories',[categoryController::class,'addCategory']);
Route::patch('/categories/{id}',[categoryController::class,'editCategory']);
Route::delete('/categories/{id}',[categoryController::class,'deleteCategory']);

//Goal Routes
Route::get('/goal',[GoalController::class,'getAllgoal']);
Route::get('/goal/{id}',[GoalController::class,'getGoal']);
Route::post('/goal',[GoalController::class,'addGoal']);
Route::patch('/goal/{id}',[GoalController::class,'editGoal']);
Route::delete('/goal/{id}',[GoalController::class,'deleteGoal']);

//alltransactions Routes
Route::get('/alltransactions',[TransactionController::class,'getAllTransactions']);

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/admin-profile', [AuthController::class, 'adminProfile']);  
    Route::get('/admins', [AuthController::class, 'getAllAdmins']);
    Route::delete('/admin/{id}', [AuthController::class, 'deleteAdmin']);
    Route::patch('admin/{id}', [AuthController::class,'editAdmin']);
});

