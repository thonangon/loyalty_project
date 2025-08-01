<?php

namespace App\Http\Controllers\Api;

use App\Gender;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    public function MemberProfile($memberId)
    {
        try {

            $user = User::where('id', $memberId)
                ->where('userType', UserType::MEMBER)
                ->first();
            if (!$user) {
                return response()->json('user not found');
            }
            return response()->json([
                'message' => "Member profile's detail retrived successfully",
                'data' => $user
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return  response()->json([
                'message' => $e->getMessage(),
                'code' => 404
            ]);
        }
    }
    public function updateProfile(Request $request, $memberID)
    {
        try {

            $user = User::where('id', $memberID)
                ->where('user_type', UserType::MEMBER->value)
                ->first();
            if (!$user) return response()->json('Member not found', 404);
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name'  => 'required|string|max:255',
                'username'   => 'required|string|max:255|unique:users,' . $user->id,
                'gender'     => 'nullable|in:' . Gender::FEMALE->value . ',' . Gender::MALE->value . ',' . Gender::UNKNOWN->value,
                'birthdate'  => 'nullable|date',
                'email'      => 'nullable|email|unique:users,email,' . $user->id,
                'phone'      => 'nullable|string|max:20',
                'occupation_id' => 'nullable|integer',
                'address'    => 'nullable|string|max:500',
                'photo'        => ['nullable', 'string', 'regex:/^data:image\/(jpeg|jpg|png|gif|webp|svg\+xml);base64,/'],
            ]);
            $user->fill($request->only([
                'first_name',
                'last_name',
                'username',
                'gender',
                'birthdate',
                'email',
                'phone',
                'occupation_id',
                'address',
                'photo'
            ]));

            $user->save();

            $result = [
                'item' => $user,
                'items' => [],
                'meta' => [],
                'links' => []
            ];

            return $this->sendResponse($result, 'Member retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }
}
