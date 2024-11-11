<?php

namespace App\Http\Controllers;

use App\Models\Matter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MatterController extends Controller
{
    // Fetch all matters with status relationship
    public function index()
    {

        $user = Auth::user();
        

         // Check if the user has an 'admin' or 'authorizer' role
        if ($user->hasRole('admin') || $user->hasRole('authoriser')) {
            // If the user is an admin or authorizer, get all incomplete requisitions
            $matters = Matter::with('status', 'user')->get();
        } else {
            // Otherwise, get incomplete requisitions created by the user
            $matters = Matter::with('status', 'user')::where('created_by', $user->id)->get();
        }

        return response()->json($matters);
        
    }

    // Create a new matter
    public function store(Request $request)
    {
        $request->validate([
            'created_by' => 'required|exists:users,id',
            'status_id' => 'required|exists:statuses,id',
            'file_reference' => 'required|string|max:255',
            'reason' => 'required|string|max:255',
            'properties' => 'nullable|string',
            'parties' => 'nullable|string',
        ]);

        $matter = Matter::create($request->all());
        return response()->json($matter, 201);
    }

    // Fetch a specific matter by ID
    public function show($id)
    {
        $matter = Matter::with('status', 'user')->findOrFail($id);
        return response()->json($matter);
    }

    // Update a matter by ID
    public function update(Request $request, $id)
    {
        $request->validate([
            'created_by' => 'required|exists:users,id',
            'status_id' => 'required|exists:statuses,id',
            'file_reference' => 'required|string|max:255',
            'reason' => 'required|string|max:255',
            'properties' => 'nullable|string',
            'parties' => 'nullable|string',
        ]);

        $matter = Matter::findOrFail($id);
        $matter->update($request->all());
        return response()->json($matter);
    }

    // Delete a matter by ID
    public function destroy($id)
    {
        $matter = Matter::findOrFail($id);
        $matter->delete();
        return response()->json(['message' => 'Matter deleted successfully.']);
    }
}
