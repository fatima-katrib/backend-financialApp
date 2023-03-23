<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FixedTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Models\Currency;
use App\Models\Category;
use App\Models\Key;

class FixedTransactionController extends Controller
{
    public function addFixedTransaction(Request $request)
    {
        try {
            $fixed_transaction = new FixedTransaction;
            $start_date = $request->input('start_date');
            $amount = $request->input('amount');
            $schedule = $request->input('schedule');
            $is_paid = $request->input('is_paid', false);

            $currency_id = $request->input('currency_id');
            $currency = Currency::find($currency_id);

            $category_id = $request->input('category_id');
            $category = Category::find($category_id);

            $fixed_key_id = $request->input('fixed_key_id');
            $fixed_key = Key::find($fixed_key_id);

            $validator = Validator::make($request->all(), [
                'schedule' => 'required|in:weekly,monthly,yearly',
                // 'fixed_key_id' => 'required|exists:fixed_key,id',
                'amount' => 'required|numeric',
                'is_paid' => 'boolean',
                'currency_id' => 'required|exists:currencies,id',
                'category_id' => 'required|exists:categories,id',
            ]);
            if ($validator->fails()) {
                $respond['message'] = $validator->errors();
                return $respond;
            }


            $fixed_transaction->amount = $amount;
            $fixed_transaction->start_date = $start_date;
            $fixed_transaction->schedule = $schedule;
            $fixed_transaction->is_paid = $is_paid;
            $fixed_transaction->currency()->associate($currency);
            $fixed_transaction->category()->associate($category);
            $fixed_transaction->fixedkey()->associate($fixed_key);
            $fixed_transaction->next_payment_date = Carbon::parse($start_date);

            $fixed_transaction->save();

            if ($schedule === 'weekly') {
                $interval = '1 week';
            } elseif ($schedule === 'monthly') {
                $interval = '1 month';
            } elseif ($schedule === 'yearly') {
                $interval = '1 year';
            }

            $next_date = Carbon::parse($start_date)->add($interval);
            $today = Carbon::today();

            while ($next_date->lte($today)) {
                $next_transaction = new FixedTransaction;
                $next_transaction->amount = $amount;
                $next_transaction->start_date = $next_date->toDateString();
                $next_transaction->schedule = $schedule;
                $next_transaction->currency()->associate($currency);
                $next_transaction->category()->associate($category);
                $next_transaction->fixedkey()->associate($fixed_key);
                $next_transaction->is_paid = false;
                $next_transaction->save();
                $next_date->add($interval);
            }

            return response()->json([
                'message' => $fixed_transaction,
            ]); // successed response
        } catch (\Exception $err) {
            return response()->json([
                'message' => 'Error adding fixed transaction: ' . $err->getMessage(),
            ], 500); // 500 status code indicates internal server error
        }
    }


    public function GetTotal(Request $request)
    {
        $type_code = $request->input('type_code');
        $now = Carbon::now()->format('Y-m-d');
        $start_month = Carbon::now()->startOfMonth()->format('Y-m-d');
        $end_month = Carbon::now()->endOfMonth()->format('Y-m-d');
        $start_year = Carbon::now()->startOfYear()->format('Y-m-d');
        $end_year = Carbon::now()->endOfYear()->format('Y-m-d');

        $this_day = FixedTransaction::whereHas('category', function ($query) use ($type_code) {
            $query->where('type_code', $type_code);
        })->whereDate('start_date', $now)->sum('amount');

        $this_month = FixedTransaction::whereHas('category', function ($query) use ($type_code) {
            $query->where('type_code', $type_code);
        })->whereDate('start_date', '<=', $end_month)->whereDate('start_date', ">=", $start_month)->sum('amount');

        $this_year = FixedTransaction::whereHas('category', function ($query) use ($type_code) {
            $query->where('type_code', $type_code);
        })->whereDate('start_date', '<=', $end_year)->whereDate('start_date', ">=", $start_year)->sum('amount');

        $current = FixedTransaction::whereHas('category', function ($query) use ($type_code) {
            $query->where('type_code', $type_code);
        })->sum('amount');

        return response()->json([
            'status' => 201,
            'message' => "fixed",
            'data' => ["this_day" => $this_day, "this_month" => $this_month, "this_year" => $this_year, "current" => $current]
        ]);
    }

    public function editFixedTransaction(Request $request, $id)
    {
        try {
            $fixed_transaction = FixedTransaction::findOrFail($id);
            $inputs = $request->except('_method');
            $fixed_transaction->update($inputs);

            $validator = Validator::make($request->all(), [
                'schedule' => 'in:weekly,monthly,yearly',
                'fixed_key_id' => 'exists:keys,id',
                'amount' => 'numeric',
                'is_paid' => 'boolean',
                'currency_id' => 'exists:currencies,id',
            ]);
            if ($validator->fails()) {
                $respond['message'] = $validator->errors();
                return $respond;
            }

            return response()->json([
                'message' => $fixed_transaction,
            ]);
        } catch (\Exception $err) {
            return response()->json([
                'message' => 'Error updating fixed transaction: ' . $err->getMessage(),
            ], 500); // 500 status code indicates internal server error
        }
    }
    public function deleteFixedTransaction(Request $request, $id)
    {

        $fixed_transaction = FixedTransaction::findOrFail($id);
        $fixed_transaction->delete();
        return response()->json([
            'message' => 'fixed transaction was deleted Successfully!',

        ]);
    }
    public function getAllFixedTransactions(Request $request)
    {
        try {
            $pagination = $request->input('pagination') ?? 10;
            $fixed_transaction = FixedTransaction::with('currency', 'category')->orderBy('start_date', 'desc')->paginate($pagination);
            return response()->json([
                'message' => $fixed_transaction
            ]);
        } catch (\Exception $err) {
            return response()->json([
                'message' => $err->getMessage(),
            ], 500); // 500 status code indicates internal server error
        }
    }

    public function getFixedTransactionById(Request $request, $id) // returns a Currency by id
    {
        try {
            $fixed_transaction = FixedTransaction::with('currency', 'category', 'key')->findOrFail($id);

            return response()->json([
                'fixed_transaction' => $fixed_transaction,
            ]);
        } catch (\Exception $err) {
            return response()->json([
                'error' => 'Fixed transaction not found',
            ], 404); // 404 status code indicates resource not found
        }
    }

    public function getBy(Request $request)
    {
        $query = FixedTransaction::query();

        if ($request->has('start_date')) {
            $query->where('start_date', $request->input('start_date'));
        }

        if ($request->has('amount')) {
            $query->where('amount', $request->input('amount'));
        }

        if ($request->has('schedule')) {
            $query->where('schedule', $request->input('schedule'));
        }

        if ($request->has('next_payment_date')) {
            $query->where('next_payment_date', $request->input('next_payment_date'));
        }

        if ($request->has('is_paid')) {
            $query->where('is_paid', $request->input('is_paid'));
        }

        $perPage = $request->input('per_page') ?? 10; // set default per page as 10

        $transactions = $query->paginate($perPage);

        return response()->json($transactions);
    }
}