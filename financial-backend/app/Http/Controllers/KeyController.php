<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Key;

class KeyController extends Controller
{
    public function getAllFixed_Key()
    {
        $key = Key::all();
        return response()->json(['key' => $key,]);
    }


    public function getFixed_key(Request $request, $id)
    {
        try {
            $key = Key::find($id);

            return response()->json(['message' => $key,]);

        } catch (\Exception $err) {
            return response()->json(['error' => 'Fixed_key NOT FOUND',], 404);

        }

    }




    public function CreatFixed_Key(Request $request)
    {
        try {
            $key = new Key;
            $title = $request->input('title');
            $description = $request->input('description');
            $isActive = $request->input('isActive', true);

            $key->title = $title;
            $key->description = $description;
            $key->isActive = $isActive;
            $key->save();

            return response()->json([
                'message' => 'Fixed_key Added successfully!' . $title . ' ' . $description,
            ]);
        } catch (\Exception $err) {
            return response()->json(['message' => 'Error adding Fixed_key: ' . $err->getMessage(),], 500);
        }
    }




    public function editFixed_Key(Request $request, $id)
    {
        try {
            $key = Key::findOrFail($id);
            $inputs = $request->except('_method');
            if ($request->filled('isActive')) {
                $inputs['isActive'] = (bool) $request->input('isActive');
            }
            $key->update($inputs);

            return response()->json([
                'key' => $key,
            ]);
        } catch (\Exception $err) {
            return response()->json([
                'message' => 'Error updating Fixed_key: ' . $err->getMessage(),
            ], 500);
        }
    }



    public function destroyFixed_Key(Request $request, $id)
    {

        $key = Key::find($id);
        $key->delete();
        return response()->json([
            'message' => 'Fixed_key deleted Successfully!',

        ]);
    }
}