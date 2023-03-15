<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FixedTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

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
    
            $validator = Validator::make($request->all(), [
                'schedule' => 'required|in:weekly,monthly,yearly',
                'fixed_key_id' => 'required|exists:fixed_key',
                'amount' => 'required|numeric',
                'is_paid' =>  'boolean',
                'currency_id'=> 'required|exists:currencies,id',
            ]);
            if($validator->fails()){
                $respond['message'] = $validator->errors();
                return $respond;
            }
            
    
            $fixed_transaction->amount = $amount;
            $fixed_transaction->start_date = $start_date;
            $fixed_transaction->schedule = $schedule;
            $fixed_transaction->is_paid = $is_paid;
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
        public function editFixedTransaction(Request $request, $id)
        {
            try {
                $fixed_transaction = FixedTransaction::findOrFail($id);
                $inputs = $request->except('_method');
                $fixed_transaction->update($inputs);
    
                $validator = Validator::make($request->all(), [
                    'schedule' => 'in:weekly,monthly,yearly',
                    'fixed_key_id' => 'exists:fixed_key',
                    'amount' => 'numeric',
                    'is_paid' =>  'boolean',
                    'currency_id'=> 'exists:currencies,id',
                ]);
                if($validator->fails()){
                    $respond['message'] = $validator->errors();
                    return $respond;
                }
                
                return response()->json([
                    'message' => $fixed_transaction,
                ]);
            }catch (\Exception $err) {
                return response()->json([
                    'message' => 'Error updating fixed transaction: ' . $err->getMessage(),  
                ], 500); // 500 status code indicates internal server error
            }
        }
        public function deleteFixedTransaction(Request $request, $id){
             
            $fixed_transaction =  FixedTransaction::find($id);
            $fixed_transaction->delete();
            return response()->json([
                'message' => 'Currency deleted Successfully!',
         
            ]);
        }
        public function getAllFixedTransactions(Request $request){
            $fixed_transaction = FixedTransaction::all();
            return response()->json([
                'fixed_transactions' => $fixed_transaction
            ]);
        }
    
        public function getFixedTransactionById(Request $request, $id) // returns a Currency by id
        {
            try {
                $fixed_transaction = FixedTransaction::findOrFail($id);
        
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