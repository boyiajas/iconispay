<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class OrganisationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $organisations = Organisation::with('user')->get();
            
            return DataTables::of($organisations)
                ->addColumn('created_by', function ($organisation) {
                    return $organisation->user ? $organisation->user->name : 'N/A';
                })
                ->make(true);
        }
        
        return response()->json(Organisation::with('user')->get());
    }

    /**
     * Get all organisations
     */
    public function getAllOrganisations()
    {
        return response()->json(Organisation::select('id', 'name', 'location')->orderBy('name', 'asc')->get());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Organisation::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'email' => $request->email,
            'location' => $request->location,
            'contact' => $request->contact,
            'user_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'Organisation created successfully!']);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return Organisation::findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Organisation $organisation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Organisation $organisation)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $organisation->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'email' => $request->email,
            'location' => $request->location,
            'contact' => $request->contact,
        ]);

        return response()->json(['message' => 'Organisation updated successfully!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organisation $organisation)
    {
        $organisation->delete();
        return response()->json(['message' => 'Organisation deleted successfully!']);
    }
}
