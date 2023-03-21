<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Goal;
use App\Models\Currency;
use Illuminate\Support\Facades\Validator;

class GoalController extends Controller
{
    //********* GetAll Goals *********


    public function getAllGoal()
    {
        try {
            $goal = Goal::with('currency')->get();
            return response()->json([
                'message' => 'Goal loaded successfully',
                'goal' => $goal,

            ]);

        } catch (\Exception $err) {
            return response()->json([
                'message' => $err->getMessage(),
            ], 500); // 500 status code indicates internal server error
        }
    }


    //********* Get Goal *********


    public function getGoal(Request $request, $id)
    {
        try {
            $goal = Goal::where('id', $id)->with('currency')->get();

            return response()->json([
                'message' => $goal
            ]);
        } catch (\Exception $err) {
            return response()->json([
                'message' => $err->getMessage(),
            ], 500); // 500 status code indicates internal server error
        }
    }

    //********* Add Goal *********


    public function addGoal(Request $request)
    {
        try {
            $goal = new Goal;
            $currency_id = $request->input('currency_id');
            $currency = Currency::find($currency_id);
            $amount = $request->input('amount');
            $schedule = $request->input('schedule');
            $validator = Validator::make($request->all(), [
                'schedule' => 'required|in:weekly,monthly,yearly',
                'currency_id' => 'required|exists:currencies,id',
                'amount' => 'required|numeric',
            ]);
            if ($validator->fails()) {
                $respond['message'] = $validator->errors();
                return $respond;
            }

            $goal->amount = $amount;
            $goal->schedule = $schedule;
            $goal->currency()->associate($currency);
            $goal->save();

            return response()->json([
                'message' => $goal

            ]);
        } catch (\Exception $err) {
            return response()->json([
                'message' => $err->getMessage(),
            ], 500); // 500 status code indicates internal server error
        }
    }

    //********* Edit Goal *********

    public function editGoal(Request $request, $id)
    {
        try {
            $goal = Goal::find($id);
            $currency = Currency::find($request->input('currency_id'));
            $inputs = $request->except('_method');
            $validator = Validator::make($request->all(), [
                'schedule' => 'in:weekly,monthly,yearly',
                'currency_id' => 'integer|min:1|exists:currencies,id',
                'amount' => 'numeric',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                ]);
            }
            $goal->currency()->associate($currency);
            $goal = $goal->update($inputs);

            $goal = Goal::find($id);
            $goal->load('currency');
            return response()->json([
                'message' => 'Goal edited successfully!',
                'goal' => $goal,

            ]);

        } catch (\Exception $err) {
            return response()->json([
                'message' => $err->getMessage(),
            ], 500); // 500 status code indicates internal server error
        }
    }


    //*********  Detele Goal *********

    public function deleteGoal(Request $request, $id)
    {
        try {
            $goal = Goal::find($id);
            $goal->delete();

            return response()->json([
                'message' => 'Goal deleted successfully'
            ]);
        } catch (\Exception $err) {
            return response()->json([
                'message' => $err->getMessage(),
            ], 500); // 500 status code indicates internal server error
        }
    }

}