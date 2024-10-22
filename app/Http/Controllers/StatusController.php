<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    // Fetch all statuses
    public function index()
    {
        $statuses = Status::all();
        return response()->json($statuses);
    }

    // Create a new status
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:statuses',
            'description' => 'nullable|string',
        ]);

        $status = Status::create($request->all());
        return response()->json($status, 201);
    }

    // Fetch a specific status by ID
    public function show($id)
    {
        $status = Status::findOrFail($id);
        return response()->json($status);
    }

    // Update a status by ID
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:statuses,name,' . $id,
            'description' => 'nullable|string',
        ]);

        $status = Status::findOrFail($id);
        $status->update($request->all());
        return response()->json($status);
    }

    // Delete a status by ID
    public function destroy($id)
    {
        $status = Status::findOrFail($id);
        $status->delete();
        return response()->json(['message' => 'Status deleted successfully.']);
    }
}
