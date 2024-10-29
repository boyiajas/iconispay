<?php

namespace App\Http\Controllers;

use App\Models\User;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get only active users with their roles
        $users = User::where('status', 'active')->with('roles')->get();

        // Use the DataTables facade to format the data
        return DataTables::of($users)
        ->addColumn('role', function ($user) {
            // Wrap each role name in a Bootstrap badge
            return $user->roles->map(function ($role) {
                return '<span class="badge badge-primary btn-primary">' . $role->name . '</span>';
            })->implode(' ');
        })
        ->rawColumns(['role']) // Ensure the HTML for badges is rendered
        ->make(true);
    }

    public function getRecipients()
    {
        return User::where('id', '!=', Auth::user()->id)->get();
    }

    public function deactivatedUsers(Request $request)
    {
        // Get only active users with their roles
        $users = User::where('status', 'inactive')->with('roles')->get();

        // Use the DataTables facade to format the data
        return DataTables::of($users)
            ->addColumn('role', function ($user) {
                // Combine all role names into a single string
                return $user->roles->pluck('name')->implode(', ');
            })
            ->make(true);
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
