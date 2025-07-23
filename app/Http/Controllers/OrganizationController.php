<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;

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
        // Logic to show a specific organization
    }

    public function create()
    {
        // Logic to show form for creating a new organization
    }

    public function store(Request $request)
    {
        // Logic to store a new organization
    }

    public function edit($id)
    {
        // Logic to show form for editing an organization
    }

    public function update(Request $request, $id)
    {
        // Logic to update an existing organization
    }

    public function destroy($id)
    {
        // Logic to delete an organization
    }
}
