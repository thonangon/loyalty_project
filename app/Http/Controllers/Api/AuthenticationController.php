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
use App\Http\Resources\UserResource;
use App\HttpRespones;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


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
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email_phone' => 'required|string',
                'password' => 'required|string'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], HttpRespones::VALIDATION_ERROR->value);
            };
            $loginInput = $request->input('email_phone');
            $password = $request->input('password');
            $loginTypes = [];
            if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
                $loginTypes['email'] = $loginInput;
            } elseif (preg_match('/^\+?[0-9]{7,15}$/', $loginInput)) {
                $loginTypes['phone'] = $loginInput;
            } else {
                return response()->json('Please enter a valid email or phone number.', HttpRespones::VALIDATION_ERROR->value);
            }

            $loginTypes['password'] = $password;
            if (!Auth::attempt($loginTypes)) {
                return response()->json('Invalid credentials', HttpRespones::UNAUTHORIZED->value);
            }
            $user = Auth::user();
            $authToken = $user->createToken('auth_token')->plainTextToken;
            Cache::forget('refresh_token_' . $user->id);
            $refreshToken = Str::random(60);
            Cache::put('refresh_token_' . $user->id, $refreshToken, now()->addDays(30));
            $result = [
                'user' => $user,
                'access_token' => $authToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
            ];
            return response()->json(['message' => 'Login successful', 'data' => $result], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function refreshToken(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'refresh_token' => 'required|string'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], HttpRespones::VALIDATION_ERROR->value);
            };
            $refreshToken = $request->input('refresh_token');
            $userId = Cache::get('refresh_token' . $refreshToken);
            if (!$userId) {
                return response()->json('Invalid or Expired refresh token', HttpRespones::UNAUTHORIZED->value);
            };
            $user = User::find($userId);
            if (!$user) {
                return response()->json('user not found', HttpRespones::NOT_FOUND);
            }
            Cache::forget('refresh_token_' . $refreshToken);
            $newRefreshToken = Str::random(60);
            Cache::put('refresh_token_' . $newRefreshToken, $user->id);

            $accessToken = $user->createToken('authToken')->plainTextToken;

            return $this->sendResponse([
                'access_token' => $accessToken,
                'refresh_token' => $newRefreshToken,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
            ], 'Token refreshed successfully');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], $e->getCode());
        }
    }
    public function logout()
    {
        $user = User::find(Auth::use()->id);
        $user->token()->delete();
        return response()->json([
            'success' => true,
            'message' => 'logged out successfully'
        ], 200);
    }
    public function deleteAccount(Request $request)
    {
        try {
            $request->validate([
                'member_id' => 'required|exists:users,id',
                'email_phone' => 'required|string|max:255',
                'password' => 'required|string',
            ]);

            $user = User::find($request->member_id);
            if ($user->email !== $request->email_phone && $user->phone !== $request->email_phone) {
                return $this->sendError(
                    'Invalid email or phone number',
                    ['email_phone' => ['Email or phone number does not match the account']],
                    HttpRespones::VALIDATION_ERROR->value
                );
            }

            if (!Hash::check($request->password, $user->password)) {
                return $this->sendError(
                    'Incorrect password',
                    ['password' => ['The provided password is incorrect']],
                    HttpRespones::UNAUTHORIZED->value
                );
            }

            $user->tokens()->delete();
            $user->delete();

            return $this->sendResponse([], 'Account deleted successfully');
        } catch (\Exception $e) {
            return $this->sendError(
                'Account deletion failed',
                ['error' => $e->getMessage()],
                $e->getCode() ?: 500
            );
        }
    }
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|exits:users,email'
        ]);
        if (!$validator->fails()) {
            return response()->json('feild email is required');
        }
        $otps = str(10000, 999999);
    }
}
