<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\HttpRespones;
use App\Models\Occupation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OccupationController extends Controller
{
    public function index()
    {
        $occupations = Occupation::all();
        if ($occupations->isEmpty()) {
            return response()->json(['message' => 'No occupations found'], 404);
        }
        return response()->json(['occupations' => $occupations], 200);
    }

    public function show($id)
    {
        $occupations = Occupation::find($id);
        if (!$occupations) {
            return response()->json(['message :' => 'o$occupations not found', HttpRespones::NO_CONTENT->value]);
        }
        return response()->json([
            'message' => 'organizaition retrived sucessfully',
            'data' => $occupations,
        ], 200);
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
        $occupation = occupation::create($validator->validated());

        return response()->json([
            'message' => 'occupation created successfully',
            'occupation' => $occupation,
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $occupation = occupation::find($id);
        if (!$occupation) {
            return response()->json(['message' => "occupation not found", HttpRespones::NOT_FOUND->value], 404);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'slug' => 'required|string|unique:occupations,slug',
            
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $occupation->update($validator->validated());

        return response()->json([
            'message' => 'occupation updated successfully',
            'occupation' => $occupation,
        ], 200);
    }

    public function destroy($id)
    {
        $occupation = occupation::find($id);
        if (!$occupation) {
            return response()->json(['message' => 'occupation not found'], 404);
        }

        $occupation->delete();

        return response()->json(['message' => 'occupation deleted successfully'], 200);
    }
}
