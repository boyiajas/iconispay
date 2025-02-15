<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Category::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function getBeneficiariesByCategory($category_id)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('superadmin');

        $category = Category::findOrFail($category_id);

        // Get beneficiaries based on the user role
        $beneficiariesQuery = $isSuperAdmin
            ? $category->beneficiaries()
            : $category->beneficiaries()->where('organisation_id', $user->organisation_id);

        $firmAccountsQuery = $isSuperAdmin
            ? $category->firmAccounts()
            : $category->firmAccounts()->where('organisation_id', $user->organisation_id);

        // Fetch the filtered results
        $beneficiaries = $beneficiariesQuery->get();
        $firmAccounts = $firmAccountsQuery->get();

        // Merge both collections and return
        return $beneficiaries->merge($firmAccounts);
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
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        //
    }
}
