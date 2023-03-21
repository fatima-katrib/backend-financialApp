<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recurring;
use App\Models\Currency;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class RecurringController extends Controller
{
    //********Get All Recurrings******** */

    public function index(Request $request)
    {
        $pagination = $request->input('pagination') ?? 10;
        $recurring = Recurring::with('currency', 'category')->orderBy('start_date', 'desc')->paginate($pagination);

        //*********querry currency********* */
        if ($request->has('currency')) {
            $currency = $request->input('currency');
            $recurring = Recurring::whereHas('currency', function ($query) use ($currency) {
                $query->where('name', $currency);
            })->orderBy('start_date', 'desc')->paginate($pagination);
        }

        //*********querry income/outcome********* */
        if ($request->has('type_code')) {
            $type_code = $request->input('type_code');
            // $week = $request->input('week');
            // $month = $request->input('month');
            // $year = $request->input('year');
            $recurring = Recurring::whereHas('category', function ($query) use ($type_code) {
                $query->where('type_code', $type_code);
            })->orderBy('start_date', 'desc')->paginate($pagination);

            // if ($week) { 
            //     $recurring = Recurring::whereWeek('start_date', $week)->orderBy('start_date', 'desc')->paginate($pagination)->get();
            // }
            // if ($month) { 
            //     $recurring = Recurring::whereMonth('start_date', $month)->orderBy('start_date', 'desc')->paginate($pagination)->get();
            // }
            // if ($year) { 
            //     $recurring = Recurring::whereYear('start_date', $year)->get()->orderBy('start_date', 'desc')->paginate($pagination);
            // }
        }


        return response()->json([
            'status' => 201,
            'message' => "recurrings",
            'data' => $recurring
        ]);
    }

    //********Get Recurring by Id******** */
    public function show(Request $request, $id)
    {
        $recurring = Recurring::findOrFail($id);
        return response()->json([
            'status' => 201,
            'message' => "recurring",
            'data' => $recurring
        ]);

    }

    public function GetTotal(Request $request)
    {
        $type_code = $request->input('type_code');
        $now = Carbon::now()->format('Y-m-d');
        $start_month = Carbon::now()->startOfMonth()->format('Y-m-d');
        $end_month = Carbon::now()->endOfMonth()->format('Y-m-d');
        $start_year = Carbon::now()->startOfYear()->format('Y-m-d');
        $end_year = Carbon::now()->endOfYear()->format('Y-m-d');

        $this_day = Recurring::whereHas('category', function ($query) use ($type_code) {
            $query->where('type_code', $type_code);
        })->whereDate('start_date', $now)->sum('amount');

        $this_month = Recurring::whereHas('category', function ($query) use ($type_code) {
            $query->where('type_code', $type_code);
        })->whereDate('start_date', '<=', $end_month)->whereDate('start_date', ">=", $start_month)->sum('amount');

        $this_year = Recurring::whereHas('category', function ($query) use ($type_code) {
            $query->where('type_code', $type_code);
        })->whereDate('start_date', '<=', $end_year)->whereDate('start_date', ">=", $start_year)->sum('amount');

        $current = Recurring::whereHas('category', function ($query) use ($type_code) {
            $query->where('type_code', $type_code);
        })->sum('amount');

        return response()->json([
            'status' => 201,
            'message' => "recurrings",
            'data' => ["this_day" => $this_day, "this_month" => $this_month, "this_year" => $this_year, "current" => $current]
        ]);
    }

    //********create Recurring******** */
    public function store(Request $request)
    {
        $recurring = new Recurring();
        $title = $request->input('title');
        $description = $request->input('description');
        $amount = $request->input('amount');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $currency_id = $request->input('currency_id');
        $currency = Currency::find($currency_id);
        $category_id = $request->input('category_id');
        $category = Category::find($category_id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:100',
            'description' => 'required|string|max:250',
            'amount' => 'required|numeric|gt:0',
            'currency_id' => 'required|integer|min:1|exists:currencies,id',
            'category_id' => 'required|integer|min:1|exists:categories,id',
            "start_date" => 'required|date_format:Y-m-d',
            "end_date" => 'required|date_format:Y-m-d|after:start_date',
        ]);
        if ($validator->fails()) {
            $respond['message'] = $validator->errors();
            return $respond;
        }

        $recurring->title = $title;
        $recurring->description = $description;
        $recurring->amount = $amount;
        $recurring->currency()->associate($currency);
        $recurring->category()->associate($category);
        $recurring->start_date = $start_date;
        $recurring->end_date = $end_date;

        $recurring->save();
        $recurring->load('currency', 'category');
        return response()->json([
            'message' => 'recurring added successfully!',
            'recurring' => $recurring
        ]);
    }

    //********Edit Recurring******** */
    public function edit(Request $request, $id)
    {
        $recurring = Recurring::findOrFail($id);
        $currency_id=$request->input('currency_id');
        $category_id=$request->input('category_id');

        $inputs = $request
            ->except('_method')
        ;
        $validator = Validator::make($request->all(), [
            'title' => 'string|max:100',
            'description' => 'string|max:250',
            'amount' => 'numeric|gt:0',
            'currency_id' => 'integer|min:1|exists:currencies,id',
            'category_id' => 'integer|min:1|exists:categories,id',
            "start_date" => 'date_format:Y-m-d',
            "end_date" => 'date_format:Y-m-d|after:start_date',
        ]);
        if ($validator->fails()) {
            $respond['message'] = $validator->errors();
            return $respond;
        };
        $currency_id ? $recurring->currency()->associate(Currency::findOrFail($currency_id)): null;
        $category_id ? $recurring->category()->associate(Category::findOrFail($category_id)) : null;
        $recurring->update($inputs);
        $recurring->load('currency', 'category');
        return response()->json([
            'message' => 'recurring edited successfully!',
            'recurring' => $recurring,

        ]);
    }

    //********Delete Recurring******** */
    public function destroy(Request $request, $id)
    {

        $recurring = Recurring::findOrFail($id);
        $recurring->delete();
        return response()->json([
            'message' => 'recurring deleted Successfully!',

        ]);
    }
}