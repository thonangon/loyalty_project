<?php

namespace App\Http\Controllers\Api;

use App\Models\Organization;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\HttpRespones;
use Illuminate\Support\Facades\Validator;

class OrganizationController extends Controller
{
    public function index()
    {
        $organization = Organization::all();
        if ($organization->isEmpty()) {
            return response()->json(['message' => 'No organizations found'], 404);
        }
        return response()->json(['organizations' => $organization], 200);
    }

    public function show($id)
    {
        $organization = Organization::find($id);
        if (!$organization) return response()->json('organization not found', 404);
        $result = $organization;
        return response()->json([
            $result
        ], 'organization detail successfully');
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'slug' => 'required|string|unique:organizations,slug',

        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->error()], 422);
        }
        $organization = Organization::create($validator->validated());

        return response()->json([
            'message' => 'Organization created successfully',
            'organization' => $organization,
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $organization = Organization::find($id);
        if (!$organization) {
            return response()->json(['message' => "organization not found", HttpRespones::NOT_FOUND->value], 404);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'slug' => 'required|string|unique:organizations,slug',

        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $organization->update($validator->validated());

        return response()->json([
            'message' => 'Organization updated successfully',
            'organization' => $organization,
        ], 200);
    }

    public function destroy($id)
    {
        $organization = Organization::find($id);
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $organization->delete();

        return response()->json(['message' => 'Organization deleted successfully'], 200);
    }
}
