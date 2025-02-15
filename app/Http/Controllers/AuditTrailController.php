<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class AuditTrailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('superadmin');

        // Initialize query with relationship and latest order
        $query = AuditTrail::with('user')->latest();

        // Apply organization restriction for non-superadmin users
        if (!$isSuperAdmin) {
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('organisation_id', $user->organisation_id);
            });
        }

        // Apply date range filter if provided
        if ($request->has('fromDate') && $request->has('toDate')) {
            $query->whereBetween('created_at', [$request->fromDate, $request->toDate]);
        }

        return DataTables::of($query)->toJson(); // ✅ Returns DataTables JSON format
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
        /* $validated = $request->validate([
            'user_name' => 'required|string|max:255',
            'action' => 'required|string|max:255',
            'details' => 'required|string',
        ]);

        $auditTrail = AuditTrail::create($validated);

        return response()->json($auditTrail, 201); */
    }

    /**
     * Display the specified resource.
     */
    public function show(AuditTrail $auditTrail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AuditTrail $auditTrail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AuditTrail $auditTrail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AuditTrail $auditTrail)
    {
        //
    }
}
