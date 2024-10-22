<?php

namespace App\Http\Controllers;

use App\Models\Document;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function getDocuments($id)
    {
        // Get the currently authenticated user ID
        $userId = Auth::id();
                                      
        // Assuming Document has relationships with User and Requisition
        $documents = Document::where('created_by', $userId)->where('requisition_id', $id)
            ->with('user')
            ->get();

        return DataTables::of($documents)
            ->addColumn('user_name', function ($document) {
                return $document->user->name;
            })
            ->editColumn('uploaded_at', function ($document) {
                return $document->created_at->format('Y-m-d H:i:s');
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
        // Validate the incoming request
        $request->validate([
            'description' => 'required|string|max:255',
            'file' => 'required|mimes:pdf,jpg,jpeg,png|max:10240',  // Max size 10MB, PDF and image files only
            'requisition_id' => 'required|exists:requisitions,id'
        ]);

        // Store the uploaded file
        $filePath = $request->file('file')->store('documents', 'public');

        // Save the document in the database
        $document = Document::create([
            'requisition_id' => $request->input('requisition_id'),
            'user_id' => auth()->id(),  // Assuming the user is authenticated
            'description' => $request->input('description'),
            'file_name' => $filePath
        ]);

        return response()->json([
            'message' => 'Document uploaded successfully',
            'document' => $document
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Document $document)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Document $document)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Document $document)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
        //
    }
}
