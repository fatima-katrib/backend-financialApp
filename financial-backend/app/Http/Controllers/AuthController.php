<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use Validator;

class AuthController extends Controller
{
    //
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request){
    	$validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->createNewToken($token);
    }

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        $admin = Admin::create(array_merge(
                    $validator->validated(),
                    ['password' => bcrypt($request->password)]
                ));
        return response()->json([
            'message' => 'User successfully registered',
            'user' => $admin
        ], 201);
    }

    public function logout() {
        auth()->logout();
        return response()->json(['message' => 'Admin successfully signed out']);
    }
      
    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }

     
    public function adminProfile() {
        return response()->json(auth()->user());
    }

    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'admin' => auth()->user()
        ]);
    }
    public function getAllAdmins() {
        $admins = Admin::all();
        return response()->json($admins);
    }

    public function deleteAdmin(Request $request, $id) {
        $admin = Admin::find($id);
        $admin->delete();
        return response()->json([
            'message' => 'Admin deleted Successfully!',

        ]);
    }
    public function editAdmin(Request $request, $id) {
        $admin = Admin::find($id);
        if (!$admin) {
            return response()->json([
                'message' => 'Admin not found'
            ], 404);
        }
    
        $validator = Validator::make($request->all(), [
            'name' => 'string|between:2,100',
            'email' => 'string|email|max:100|unique:users,email,'.$admin->id,
            'password' => 'string|min:6',
        ]);
        
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        
        $admin->update(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password ?? $admin->password)]
        ));
        
        return response()->json([
            'message' => 'Admin successfully updated',
            'admin' => $admin
        ], 200);
    }
}
