<?php

namespace App\Http\Controllers;
use App\Models\Category;


use Illuminate\Http\Request;

class CategoryController extends Controller
{
//********* Add category *********

public function addCategory(Request $request){
    $category = new Category();
    $request->validate([
        'name'=>'required|max:100',
        'type_code' => 'required|in:incomes,expenses'
    ]);
    $category->name = $request->input('name');
    $category->type_code = $request->input('type_code');
    $category->save();
    return response()->json([
        'message' => $request->all()
    ]);
}

//********* Get All categories *********

public function getAllCategory() // returns all currencies
{
    $categories = Category::all();
    return response()->json([
        'categories' => $categories,
    ]);
}

//********* Get category *********


public function getCategory(Request $request, $id){
    $category = Category::find($id);

    return response()->json([
        'message' => $category
    ]);
}


//********* Edit category *********

public function editCategory(Request $request, $id){

    $category = Category::find($id);
    $inputs = $request->except('_method');
    $category->update($inputs);
    $inputs = $request;
    return response()->json([
        'message' => 'category updated successfully',
        'category' => $category
    ]);
}

//*********  Detele category *********

public function deleteCategory(Request $request, $id){
    $category = Category::find($id);
    $category->delete();

    return response()->json([
        'message' => 'category deleted successfully',
    ]);
}}