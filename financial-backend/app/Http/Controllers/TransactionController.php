<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recurring;
use App\Models\FixedTransaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function getAllTransactions(Request $request)
    {
        $pagination = $request->input('pagination') ?? 10;
        // $transactions = Recurring::with('currency', 'category')
        //     ->orderBy('start_date', 'desc')
        //     ->union(FixedTransaction::select('id', 'amount', 'start_date', 'schedule', 'is_paid', 'next_payment_date', 'currency_id', 'category_id', 'updated_at', 'created_at'))
        //     ->with('currency', 'category')
        //     ->orderBy('start_date', 'desc')
        //     ->paginate($pagination);
        $recurrings = Recurring::select(
            DB::raw("'recurring' as transaction_type"),
            'recurrings.id as id',
            'title',
            'description',
            'amount',
            'currencies.name as currency_name',
            'currencies.rate',
            'categories.name as category_name',
            'categories.type_code',
            'start_date',
            DB::raw('NULL as next_payment_date'),
            'end_date',
            DB::raw('NULL as is_paid'),
            'recurrings.created_at as created_at',
            'recurrings.updated_at as updated_at',
        )
            ->join('currencies', 'recurrings.currency_id', '=', 'currencies.id')
            ->join('categories', 'recurrings.category_id', '=', 'categories.id');

        $fixedTransactions = FixedTransaction::join('keys', 'fixed_transactions.fixed_key_id', '=', 'keys.id')
            ->select(
                DB::raw("'fixed' as transaction_type"),
                'fixed_transactions.id as id',
                'keys.title',
                'keys.description',
                'amount',
                'currencies.name as currency_name',
                'currencies.rate',
                'categories.name as category_name',
                'categories.type_code',
                'start_date',
                'next_payment_date',
                DB::raw('NULL as end_date'),
                'is_paid',
                'fixed_transactions.created_at as created_at',
                'fixed_transactions.updated_at as updated_at',
            )
            ->join('currencies', 'fixed_transactions.currency_id', '=', 'currencies.id')
            ->join('categories', 'fixed_transactions.category_id', '=', 'categories.id');

        $transactions = $recurrings->union($fixedTransactions)->orderBy('start_date', 'desc')
            ->paginate($pagination);


        $now = Carbon::now()->format('Y-m-d');
        $start_month = Carbon::now()->startOfMonth()->format('Y-m-d');
        $end_month = Carbon::now()->endOfMonth()->format('Y-m-d');
        $start_year = Carbon::now()->startOfYear()->format('Y-m-d');
        $end_year = Carbon::now()->endOfYear()->format('Y-m-d');

        //getting totalincome
        $recurring_incomes_this_day = Recurring::whereHas('category', function ($query) {
            $query->where('type_code', 'incomes');
        })->whereDate('start_date', $now)->sum('amount');
        $recurring_incomes_this_month = Recurring::whereHas('category', function ($query) {
            $query->where('type_code', 'incomes');
        })->whereDate('start_date', '<=', $end_month)->whereDate('start_date', ">=", $start_month)->sum('amount');
        $recurring_incomes_this_year = Recurring::whereHas('category', function ($query) {
            $query->where('type_code', 'incomes');
        })->whereDate('start_date', '<=', $end_year)->whereDate('start_date', ">=", $start_year)->sum('amount');
        $recurring_incomes_current = Recurring::whereHas('category', function ($query) {
            $query->where('type_code', 'incomes');
        })->sum('amount');

        $fixed_incomes_this_day = FixedTransaction::whereHas('category', function ($query) {
            $query->where('type_code', 'incomes');
        })->whereDate('start_date', $now)->sum('amount');
        $fixed_incomes_this_month = FixedTransaction::whereHas('category', function ($query) {
            $query->where('type_code', 'incomes');
        })->whereDate('start_date', '<=', $end_month)->whereDate('start_date', ">=", $start_month)->sum('amount');
        $fixed_incomes_this_year = FixedTransaction::whereHas('category', function ($query) {
            $query->where('type_code', 'incomes');
        })->whereDate('start_date', '<=', $end_year)->whereDate('start_date', ">=", $start_year)->sum('amount');
        $fixed_incomes_current = FixedTransaction::whereHas('category', function ($query) {
            $query->where('type_code', 'incomes');
        })->sum('amount');

        $incomes_this_day = $recurring_incomes_this_day + $fixed_incomes_this_day;
        $incomes_this_month = $recurring_incomes_this_month + $fixed_incomes_this_month;
        $incomes_this_year = $recurring_incomes_this_year + $fixed_incomes_this_year;
        $incomes_current = $recurring_incomes_current + $fixed_incomes_current;


        //getting totaloutcome
        $recurring_expenses_this_day = Recurring::whereHas('category', function ($query) {
            $query->where('type_code', 'expenses');
        })->whereDate('start_date', $now)->sum('amount');
        $recurring_expenses_this_month = Recurring::whereHas('category', function ($query) {
            $query->where('type_code', 'expenses');
        })->whereDate('start_date', '<=', $end_month)->whereDate('start_date', ">=", $start_month)->sum('amount');
        $recurring_expenses_this_year = Recurring::whereHas('category', function ($query) {
            $query->where('type_code', 'expenses');
        })->whereDate('start_date', '<=', $end_year)->whereDate('start_date', ">=", $start_year)->sum('amount');
        $recurring_expenses_current = Recurring::whereHas('category', function ($query) {
            $query->where('type_code', 'expenses');
        })->sum('amount');

        $fixed_expenses_this_day = FixedTransaction::whereHas('category', function ($query) {
            $query->where('type_code', 'expenses');
        })->where('is_paid', '1')->whereDate('start_date', $now)->sum('amount');
        $fixed_expenses_this_month = FixedTransaction::whereHas('category', function ($query) {
            $query->where('type_code', 'expenses');
        })->where('is_paid', '1')->whereDate('start_date', '<=', $end_month)->whereDate('start_date', ">=", $start_month)->sum('amount');
        $fixed_expenses_this_year = FixedTransaction::whereHas('category', function ($query) {
            $query->where('type_code', 'expenses');
        })->where('is_paid', '1')->whereDate('start_date', '<=', $end_year)->whereDate('start_date', ">=", $start_year)->sum('amount');
        $fixed_expenses_current = FixedTransaction::whereHas('category', function ($query) {
            $query->where('type_code', 'expenses');
        })->where('is_paid', '1')->sum('amount');

        $expenses_this_day = $recurring_expenses_this_day + $fixed_expenses_this_day;
        $expenses_this_month = $recurring_expenses_this_month + $fixed_expenses_this_month;
        $expenses_this_year = $recurring_expenses_this_year + $fixed_expenses_this_year;
        $expenses_current = $recurring_expenses_current + $fixed_expenses_current;

        // getting totalBalance
        $balance_this_day = $incomes_this_day - $expenses_this_day;
        $balance_this_month = $incomes_this_month - $expenses_this_month;
        $balance_this_year = $incomes_this_year - $expenses_this_year;
        $balance_current = $incomes_current - $expenses_current;

        // //*********query currency********* */
        // if ($request->has('currency')) {
        //     $currency = $request->input('currency');
        //     $transactions = Recurring::with('currency', 'category')
        //         ->whereHas('currency', function ($query) use ($currency) {
        //             $query->where('name', $currency);
        //         })
        //         ->union(FixedTransaction::with('currency', 'category')->whereHas('currency', function ($query) use ($currency) {
        //             $query->where('name', $currency);
        //         }))
        //         ->orderBy('start_date', 'desc')
        //         ->paginate($pagination);
        // }

        // //*********query income/outcome********* */
        // if ($request->has('type_code')) {
        //     $type_code = $request->input('type_code');
        //     $transactions = Recurring::with('currency', 'category')
        //         ->whereHas('category', function ($query) use ($type_code) {
        //             $query->where('type_code', $type_code);
        //         })
        //         ->union(FixedTransaction::with('currency', 'category')->whereHas('category', function ($query) use ($type_code) {
        //             $query->where('type_code', $type_code);
        //         }))
        //         ->orderBy('start_date', 'desc')
        //         ->paginate($pagination);
        // }

        return response()->json([
            'status' => 201,
            'message' => "all transactions",
            'totalIncome' => [
                'this_day' => $incomes_this_day,
                'this_month' => $incomes_this_month,
                'this_year' => $incomes_this_year,
                'current'=>$incomes_current,
            ],
            'totalOutcome' => [
                'this_day' => $expenses_this_day,
                'this_month' => $expenses_this_month,
                'this_year' => $expenses_this_year,
                'current'=> $expenses_current,
            ],
            'totalBalance' => [
                'this_day' => $balance_this_day,
                'this_month' => $balance_this_month,
                'this_year' => $balance_this_year,
                'current'=>$balance_current,
            ],
            'transactions' => $transactions,
        ]);

    }


}