<?php

namespace App\Http\Controllers\Api;

use App\Gender;
use App\Models\Occupation;
use App\Models\Organization;
use App\Models\User;
use App\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class AuthenticationController extends Controller

{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
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
            $signUpInput = $request->input('email_phone');
            if (filter_var($signUpInput, FILTER_VALIDATE_EMAIL)) {
                if (User::where('email', $signUpInput)->exists()) {
                    return response()->json(['error' => 'This email is already registered.'], 409);
                }
                $email_phone = 'email';
            } elseif (preg_match('/^\+?[0-9]{7,15}$/',                                          $signUpInput)) {
                if (User::where('phone', $signUpInput)->exists()) {
                    return response()->json(['error' => 'This phone number is already registered.'], 409);
                }
                $email_phone = 'phone';
            } else {
                return response()->json(['error' => 'Please enter a valid email or phone number.'], 422);
            }
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'password' => bcrypt($request->password),
                $email_phone => $signUpInput,
                'gender' => Gender::from($request->gender)->value,
                'birthdate' => $request->birthdate,
                'user_type' => UserType::MEMBER->value,

            ]);
            $organization = Organization::where('identifier', $request->org_identifier)->first();
            $user['organization_id'] = $organization->id;
            $occupation = Occupation::find($request->occupation_id);
            $user['occupation_id'] = $occupation ? $occupation->id : null;
            $user->assingRole('member');
            $authToken = $user->createToken('auth_token')->plainTextToken;
            $refreshToken = Str::random(60);
            $user->save();
            Cache::put('refresh_token_' . $user->id, $refreshToken, now()->addDays(30));

            $result = [
                'user' => $user,
                'access_token' => $authToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
            ];
            return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registration failed'], 500);
        }
    }
    public function login(Request $request) {}
    public function forgotPassword(Request $request)
    {
        // Handle forgot password logic here
    }
    public function logout()
    {
        // Handle user logout logic here
    }
}
