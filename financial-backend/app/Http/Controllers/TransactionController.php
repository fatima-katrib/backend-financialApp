<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recurring;
use App\Models\FixedTransaction;

class TransactionController extends Controller
{
    public function getAllTransactions(Request $request)
    {
        $pagination = $request->input('pagination') ?? 2;
        $transactions = Recurring::all()->merge(FixedTransaction::all())->with('currency', 'category')->orderBy('start_date', 'desc')->paginate($pagination);


        //*********querry currency********* */
        if ($request->has('currency')) {
            $currency = $request->input('currency');
            $transactions = Recurring::whereHas('currency', function ($query) use ($currency) {
                $query->where('name', $currency);
            })->merge(FixedTransaction::whereHas('currency', function ($query) use ($currency) {
                $query->where('name', $currency);
            }))->orderBy('start_date', 'desc')->paginate($pagination);
        }

        //*********querry income/outcome********* */
        if ($request->has('type_code')) {
            $type_code = $request->input('type_code');
            // $week = $request->input('week');
            // $month = $request->input('month');
            // $year = $request->input('year');
            $transactions = Recurring::whereHas('category', function ($query) use ($type_code) {
                $query->where('type_code', $type_code);
            })->merge(FixedTransaction::whereHas('category', function ($query) use ($type_code) {
                $query->where('type_code', $type_code);
            }))->orderBy('start_date', 'desc')->paginate($pagination);

            return response()->json([
                'status' => 201,
                'message' => "transactions",
                'data' => $transactions
            ]);
        }
    }}