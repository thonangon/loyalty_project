<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller

{
    public function register(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'email_phone' => 'required|email|max:255',
                'password' => 'required|string|min:8|confirmed',    
                'first_name' => 'required|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'gender' => 'required|integer',
                'birthdate' => 'nullable|date',
                'org_identifier' => 'required|string|exists:organizations,identifier',
                'occupation_id' => 'nullable|exists:occupations,id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $user= User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'phone' => $request->phone,
                'address' => $request->address,
                'gender' => $request->gender,
                'birthdate' => $request->birthdate,
                'profile_picture' => $request->profile_picture,
                'user_type' => $request->user_type,
                'organization_id' => $request->organization_id,
                'occupation_id' => $request->occupation_id,
            ]);
            return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registration failed'], 500);
        }
    }
    public function login(Request $request)
    {
        
    }
    public function forgotPassword(Request $request)
    {
        // Handle forgot password logic here
    }
    public function logout()
    {
        // Handle user logout logic here
    }
}
