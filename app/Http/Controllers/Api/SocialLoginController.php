<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Illuminate\Support\Facades\Validator;
use App\Enums\HttpResponse;
use App\Roles;
use App\UserType;
use App\Http\Resources\UserResource;
use App\HttpRespones;
use App\Models\Organization;
use Exception;
use Illuminate\Support\Facades\Cache;

class SocialLoginController extends Controller
{
    protected $firebaseAuth;
    public function __construct(FirebaseAuth $firebaseAuth)
    {
        $this->firebaseAuth = $firebaseAuth;
    }
    public function socialLogin(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'id_token' => 'required|string',
                'org_identifier' => 'required|string',
                'provider' => 'required|in:google,facebook,apple',
            ]);

            if ($validator->fails()) {
                return $this->sendError(
                    HttpRespones::BAD_REQUEST->getMessage(),
                    $validator->errors()->toArray(),
                    HttpRespones::BAD_REQUEST->value
                );
            }

            $validated = $validator->validated();

            $organization = Organization::where('identifier', $validated['org_identifier'])->first();
            if (!$organization) {
                return response()->json('Organization not found', HttpRespones::NOT_FOUND->value);
            }

            $verifiedIdToken = $this->firebaseAuth->verifyIdToken($validated['id_token']);
            $claims = $verifiedIdToken->claims()->all();

            $name = $claims['name'] ?? 'Unknown User';
            $nameParts = explode(' ', $name, 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';

            $uid = $verifiedIdToken->claims()->get('sub');
            $firebaseUser = $this->firebaseAuth->getUser($uid);

            $email = $firebaseUser->email;
            $phone = null;
            $photoUrl = null;

            // Fallback from providerData
            if (!empty($firebaseUser->providerData)) {
                foreach ($firebaseUser->providerData as $provider) {
                    $email ??= $provider->email;
                    $phone ??= $provider->phoneNumber;
                    $photoUrl ??= $provider->photoUrl;
                }
            }

            if (!$email && !$phone) {
                return response()->json('Neither email nor phone number is available from the provider.', HttpRespones::BAD_REQUEST->value);
            }

            $user = User::where(function ($query) use ($email, $phone) {
                if ($email) $query->orWhere('email', $email);
                if ($phone) $query->orWhere('phone', $phone);
            })->first();

            if (!$user) {

                $user = User::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'email_verified_at' => now(),
                    'provider' => $validated['provider'],
                    'provider_id' => $claims['sub'],
                    'organization_id' => $organization->id,
                    'user_type' => UserType::MEMBER->value,
                    'password' => Hash::make(Str::random(16)),
                    'photo' => $photoUrl,
                    'phone' => $phone
                ]);

                $user->assignRole(Roles::USER->value);
            } else {
                $user->update([
                    'provider' => $validated['provider'],
                    'provider_id' => $claims['sub'],
                    'email_verified_at' => $user->email_verified_at ?? ($email ? now() : null),
                    'phone' => $user->phone ?? $phone,
                    'email' => $user->email ?? $email,
                    'photo' => $user->photo ?? $photoUrl,
                ]);
            }

            $token = $user->createToken('social-auth')->plainTextToken;
            $refreshToken = Str::random(60);
            Cache::put('refresh_token_' . $refreshToken, $user->id);
            $result = new UserResource($user);
            return $this->sendResponse([
                'access_token' => $token,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'user' => $result
            ], 'Authenticated');
        } catch (Exception $e) {
            return $this->sendError(
                $e->getMessage(),
                [],
                $e->getCode() ?: HttpRespones::INTERNAL_SERVER_ERROR->value
            );
        }
    }
}
