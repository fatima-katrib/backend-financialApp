<?php

namespace App\Http\Controllers;
use App\Models\Goal;


use Illuminate\Http\Request;

class GoalController extends Controller
{
 //********* Add Goal *********

 public function addGoal(Request $request){
    $goal = new goal();
    $goal->amount = $request->input('amount');
    $goal->currency_id = $request->input('currency_id');
    $goal->schedule = $request->input('schedule');
    $goal->save();
    return response()->json([
        'message' => $request->all()
    ]);
}

//********* Get All Goals *********

public function getAllgoal() // returns all currencies
{
    $goals = goal::all();
    return response()->json([
        'goals' => $goals,
    ]);
}

//********* Get Goal *********


public function getGoal(Request $request, $id){
    try {
    $goal = goal::findOrFail($id);

    return response()->json([
        'message' => $goal
    ]);
} catch (\Exception $e) {
    return response()->json([
        'error' => 'goal failed',
    ], 404);
    }
}


//********* Edit Goal *********

public function editGoal(Request $request, $id){

    $goal = goal::find($id);
    $inputs = $request->except('_method');
    $goal->update($inputs);
    $inputs = $request;
    return response()->json([
        'message' => 'Goal updated successfully',
        'goal' => $goal
    ]);
}

//*********  Detele Goal *********

public function deleteGoal(Request $request, $id){
    $goal = goal::find($id);
    $goal->delete();

    return response()->json([
        'message' => 'Goal deleted successfully',
    ]);
}}