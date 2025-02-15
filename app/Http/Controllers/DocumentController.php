<?php

namespace App\Http\Controllers;

use App\Models\Document;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

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
        $user = Auth::user();
         
        // Check if the user has an 'admin' or 'authorizer' role
        if ($user->hasRole('admin') || $user->hasRole('authoriser') || $user->hasRole('superadmin')) {
        
            $documents = Document::where('requisition_id', $id)->get();
        } else {
            // Assuming Document has relationships with User and Requisition
            $documents = Document::where('created_by', $user->id)->where('requisition_id', $id)
            ->with('user')
            ->get();
        }
                                      
        

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
    
    public function store(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'description' => 'required|string|max:255',
            'file' => 'required|mimes:pdf,jpg,jpeg,png|max:20480',  // Max size 20MB, PDF and image files only
            'requisition_id' => 'required|exists:requisitions,id'
        ]);
    
        try {
            // Generate a new unique file name with the original file extension
            $file = $request->file('file');
            $newFileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
    
            // Define the directory to store the file
            $directory = 'documents';
    
            // Check if the directory exists, if not, create it
            if (!Storage::exists($directory)) {
                Storage::makeDirectory($directory);
            }
    
            // Store the file securely in the specified directory
            $filePath = $file->storeAs($directory, $newFileName);
            $user = Auth::user();
    
            // Save the document in the database
            $document = Document::create([
                'requisition_id' => $request->input('requisition_id'),
                'created_by' => $user->id,  // Assuming the user is authenticated
                'description' => $request->input('description'),
                'file_name' => $newFileName,
                'file_path' => $filePath,
                'file_type' => $file->getClientMimeType(),
                'organisation_id' => $user->organisation->id
            ]);
            
            logHistory($request->input('requisition_id'), 'Document Added to Requisition', "document # {$newFileName} was added to this requisition.");
    
            return response()->json([
                'message' => 'Document uploaded successfully',
                'document' => $document
            ], 201);
        } catch (Exception $e) {
            // Handle any errors or exceptions
            return response()->json([
                'message' => 'An error occurred while uploading the document',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    // Method to download the file securely
    public function download(Document $document)
    {
        // Get the currently authenticated user ID
        $user = Auth::user();
         
        // Check if the user has an 'admin' or 'authorizer' role
        if ($user->hasRole('admin') || $user->hasRole('authoriser') || $user->hasRole('superadmin')) {
            // Serve the file using Storage, making sure it's only accessible to the authenticated user
            return Storage::download($document->file_path, $document->file_name);

        } else {
            // Check if the user is authorized to download the file
            if ($document->created_by !== auth()->id()) {
                abort(403, 'Unauthorized access to the file');
            }
            // Serve the file using Storage, making sure it's only accessible to the authenticated user
            return Storage::download($document->file_path, $document->file_name);
        }        
    }
    
    public function view(Document $document)
    {

        // Get the currently authenticated user ID
        $user = Auth::user();
         
        // Check if the user has an 'admin' or 'authorizer' role
        if ($user->hasRole('admin') || $user->hasRole('authoriser') || $user->hasRole('superadmin')) {
             // Get the file's full path from storage
            $filePath = Storage::path($document->file_path);

        } else {
            // Check if the user is authorized to download the file
            if ($document->created_by !== auth()->id()) {
                abort(403, 'Unauthorized access to the file');
            }
             // Get the file's full path from storage
            $filePath = Storage::path($document->file_path);
        } 

        // Return the file for viewing
        return response()->file($filePath);
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
        try {
           
            // Delete the file from storage
            if (Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }

            // Delete the document record from the database
            $document->delete();

            return response()->json(['message' => 'Document deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'An error occurred while deleting the document', 'error' => $e->getMessage()], 500);
        }
    }
}
