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
        $transactions = Recurring::with('currency', 'category')
            ->orderBy('start_date', 'desc')
            ->union(FixedTransaction::select('id', 'amount', 'start_date', 'schedule', 'is_paid', 'next_payment_date', 'currency_id', 'category_id', 'updated_at', 'created_at'))
            ->with('currency', 'category')
            ->orderBy('start_date', 'desc')
            ->paginate($pagination);
    
        //*********query currency********* */
        if ($request->has('currency')) {
            $currency = $request->input('currency');
            $transactions = Recurring::with('currency', 'category')
                ->whereHas('currency', function ($query) use ($currency) {
                    $query->where('name', $currency);
                })
                ->union(FixedTransaction::with('currency', 'category')->whereHas('currency', function ($query) use ($currency) {
                    $query->where('name', $currency);
                }))
                ->orderBy('start_date', 'desc')
                ->paginate($pagination);
        }
    
        //*********query income/outcome********* */
        if ($request->has('type_code')) {
            $type_code = $request->input('type_code');
            $transactions = Recurring::with('currency', 'category')
                ->whereHas('category', function ($query) use ($type_code) {
                    $query->where('type_code', $type_code);
                })
                ->union(FixedTransaction::with('currency', 'category')->whereHas('category', function ($query) use ($type_code) {
                    $query->where('type_code', $type_code);
                }))
                ->orderBy('start_date', 'desc')
                ->paginate($pagination);
        }
    
        return response()->json([
            'status' => 201,
            'message' => "transactions",
            'data' => $transactions
        ]);
    }
    
    
}