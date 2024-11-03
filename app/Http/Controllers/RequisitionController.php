<?php

namespace App\Http\Controllers;

use App\Models\FileUpload;
use App\Models\Requisition;
use Auth;
use Carbon\Carbon;
use DataTables;
use Illuminate\Http\Request;

class RequisitionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        /* $matters = Requisition::with('user')->get();
        return response()->json($matters); */
        $query = Requisition::with('user');

        // Apply status filter if provided
        if ($request->status_id) {
            $query->where('status_id', $request->status_id);
        }

        // Apply text search filter if provided
        if ($request->filter_text) {
            $query->where(function ($q) use ($request) {
                $q->where('file_reference', 'LIKE', "%{$request->filter_text}%")
                    ->orWhere('reason', 'LIKE', "%{$request->filter_text}%")
                    ->orWhereHas('user', function ($q) use ($request) {
                        $q->where('name', 'LIKE', "%{$request->filter_text}%");
                    });
            });
        }


        // Return data in DataTables format
        return Datatables::of($query)
            ->addColumn('progress', function ($requisition) {
                $progress = '';
                if ($requisition->authorization_status) {
                    $progress .= '<span class="badge bg-success me-1">Authorized</span>';
                }else{
                    $progress .= '<span class="badge bg-default me-1 disabled">Authorized</span>';
                }
                if ($requisition->funding_status) {
                    $progress .= '<span class="badge bg-success">Funded</span>';
                }else{
                    $progress .= '<span class="badge bg-default">Funded</span>';
                }
                // If no progress status is set, return a default value
                return $progress ?: '<span class="badge bg-default">No Progress</span>';
            })
            /* ->addColumn('progress', function ($requisition) {
                $progress = '';
                if ($requisition->is_authorized) {
                    $progress .= '<span class="badge bg-secondary me-2">Authorized</span>';
                }
                if ($requisition->is_funded) {
                    $progress .= '<span class="badge bg-secondary">Funded</span>';
                }
                return $progress;
            }) */
            ->addColumn('user.name', function ($requisition) {
                return $requisition->user->name;
            })
            ->rawColumns(['progress'])
            ->make(true);
    }
 
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.matters.requisition.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'file_reference' => 'required|string|max:255',
            'reason' => 'nullable|string|max:255',
            'parties' => 'nullable|string|max:255',
            'properties' => 'nullable|string|max:255',
        ]);

        // Create a new requisition (assuming the matter_id is provided in the request)
        $requisition = Requisition::create([
            'file_reference' => $validatedData['file_reference'],
            'reason' => $validatedData['reason'],
            'parties' => $validatedData['parties'],
            'properties' => $validatedData['properties'],
            'matter_id' => $request->input('matter_id'), // assuming 'matter_id' is part of the request
            'created_by' => auth()->user()->id,
            'status_id' => 1, //1 is open by default
        ]);

        // Return the created requisition details
        return response()->json($requisition, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Requisition $requisition)
    {
        
        try {
            // Return the requisition with any required relationships, such as the user who created it
            return response()->json($requisition->load('user', 'authorizedBy', 'firmAccount.institution', 'payments.beneficiaryAccount', 'payments.beneficiaryAccount.institution', 'deposits.firmAccount', 'deposits.user'));
        } catch (\Exception $e) {
            // Log the error message
            \Log::error('Error getting requisition: ' . $e->getMessage());

            // Return an error response to the client
            return response()->json([
                'message' => 'An error occurred while getting requisition.',
                'error' => $e->getMessage() // Optional: for debugging, remove in production
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Requisition $requisition)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Requisition $requisition)
    {
        // Validate the incoming request to ensure source_account_id is set and exists
        $request->validate([
            'firm_account_id' => 'required|exists:firm_accounts,id',
        ]);

        // Find the requisition by ID
        //$requisition = Requisition::findOrFail($requisition);

        // Update the requisition with the new firm_account_id and set status_id to 2
        $requisition->update([
            'firm_account_id' => $request->firm_account_id,
            'status_id' => 2, //set to incompleted
        ]);


        return response()->json([
            'message' => 'Requisition updated successfully',
            'requisition' => $requisition
        ]);
    }

    public function approve(Requisition $requisition)
    {
        // Update the requisition with the new values
        $requisition->update([
            'authorization_status' => 1,                  // Set authorization status to approved
            'status_id' => 5,                             // Update status ID
            'authorized_user_id' => auth()->id(),         // Set the current user as the authorizer
            'authorized_at' => Carbon::now(),            // Set the current time for authorization
            'locked' => 1 
        ]);

        $requisition->calculateTransactionValue();

        return response()->json([
            'message' => 'Requisition approved successfully',
            'data' => $requisition
        ]);
    }

    public function getIncompleteRequisitions(Request $request)
    {
        // Get the currently authenticated user ID
        $user = Auth::user();
        $incompleteStatusId = 2;
        $incompleteCount = 0;
        

         // Check if the user has an 'admin' or 'authorizer' role
        if ($user->hasRole('admin') || $user->hasRole('authoriser')) {
            // If the user is an admin or authorizer, get all incomplete requisitions
            $incompleteCount = Requisition::where('status_id', $incompleteStatusId)->count();
        } else {
            // Otherwise, get incomplete requisitions created by the user
            $incompleteCount = Requisition::where('created_by', $user->id)
                                        ->where('status_id', $incompleteStatusId)
                                        ->count();
        }
        
        // Return the count as a JSON response
        return response()->json([
            'count' => $incompleteCount,
        ]);
    }

    public function countAwaitingFunding()
    {
        // Get the currently authenticated user ID
        $user = Auth::user();

         // Check if the user has an 'admin' or 'authorizer' role
         if ($user->hasRole('admin') || $user->hasRole('authoriser')) {
            // If the user is an admin or authorizer, get all incomplete requisitions
            $count = Requisition::whereHas('deposits', function ($query) {
                $query->where('funded', false);
            })->count();

        } else {
            $count = Requisition::whereHas('deposits', function ($query) use ($user) {
                $query->where('funded', false)->where('created_by', $user->id);
            })->count();
        }

        return response()->json(['count' => $count]);
    }

    public function getAwaitingAuthorization(Request $request)
    {
        // Get the currently authenticated user ID
        $user = Auth::user();
        $awaitingAuthorizationStatusId = 3;
        $awaitingAuthorizationCount = 0;
        

         // Check if the user has an 'admin' or 'authorizer' role
        if ($user->hasRole('admin') || $user->hasRole('authoriser')) {
            // If the user is an admin or authorizer, get all incomplete requisitions
            $awaitingAuthorizationCount = Requisition::where('status_id', $awaitingAuthorizationStatusId)->count();
        } else {
            // Otherwise, get incomplete requisitions created by the user
            $awaitingAuthorizationCount = Requisition::where('created_by', $user->id)
                                        ->where('status_id', $awaitingAuthorizationStatusId)
                                        ->count();
        }
       
        // Return the count as a JSON response
        return response()->json([
            'count' => $awaitingAuthorizationCount,
        ]);
    }
    
    public function getReadyForPayment(Request $request)
    {
        // Get the currently authenticated user ID
        $user = Auth::user();
        $readyForPaymentStatusId = 5;
        $readyForPaymentCount = 0;
        

         // Check if the user has an 'admin' or 'authorizer' role
        if ($user->hasRole('admin') || $user->hasRole('authoriser')) {
            // If the user is an admin or authorizer, get all incomplete requisitions
            $readyForPaymentCount = Requisition::where('status_id', $readyForPaymentStatusId)->count();
        } else {
            // Otherwise, get incomplete requisitions created by the user
            $readyForPaymentCount = Requisition::where('created_by', $user->id)
                                        ->where('status_id', $readyForPaymentStatusId)
                                        ->count();
        }
       
        // Return the count as a JSON response
        return response()->json([
            'count' => $readyForPaymentCount,
        ]);
    }

    /**
     * Generate a file for a given requisition and save the file info in FileUpload.
     */
    public function generateFile(Request $request, $requisitionId)
    {
        $requisition = Requisition::findOrFail($requisitionId);

        // Generate file (this is a placeholder; replace with actual file generation logic)
        $fileName = "RequisitionFile_{$requisitionId}_" . now()->format('YmdHis') . '.pdf';
        $filePath = storage_path("app/public/files/{$fileName}");

        // Create a dummy file for demonstration purposes (replace with actual file generation)
        file_put_contents($filePath, "Generated file for requisition #{$requisitionId}");

        // Save file information in FileUpload model
        $fileUpload = FileUpload::create([
            'requisition_id' => $requisitionId,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_size' => filesize($filePath) / 1024, // File size in KB
            'user_id' => auth()->user()->id,
        ]);

        return response()->json([
            'message' => 'File generated successfully',
            'file' => $fileUpload,
        ]);
    }

    public function getRequisitionsByStatus(Request $request)
    { //dd($request->status, $request->filter_text);
        // Map status names to status IDs (adjust based on your database)
        $statusMapping = [
            'Incomplete' => 2,                // Assuming 1 is 'Incomplete'
            'Awaiting Authorisation' => 3,    // Adjust these values as per your database
            'Awaiting Funding' => 3,
            'Ready for Payment' => 5,
            'Pending Payment Confirmation' => 6,
            'Settled Today' => 7,
            'Settlement Failed' => 8,
        ];

        // Get the currently authenticated user ID
        $user = Auth::user();
        $awaitingAuthorizationStatusId = 3;
        $incompleteCount = 0;
        
        // Check if the user has an 'admin' or 'authorizer' role
        if ($user->hasRole('admin') || $user->hasRole('authoriser')) {
            // Fetch the requisitions matching the status and created by the logged-in user
            $query = Requisition::with('user');
        } else {
            // Fetch the requisitions matching the status and created by the logged-in user
            $query = Requisition::with('user')->where('created_by', $user->id);
        }
    
        

        // Apply text search filter if provided
        if ($request->filter_text) {
            $query->where(function ($q) use ($request) {
                $q->where('file_reference', 'LIKE', "%{$request->filter_text}%")
                    ->orWhere('reason', 'LIKE', "%{$request->filter_text}%")
                    ->orWhereHas('user', function ($q) use ($request) {
                        $q->where('name', 'LIKE', "%{$request->filter_text}%");
                    });
            });
        
        }else{

            // Ensure the status passed exists in the mapping
            if (!isset($statusMapping[$request->status])) {
                return response()->json(['error' => 'Invalid status'], 400);
            }

            // Get the status ID from the mapping
            $statusId = $statusMapping[$request->status]; //dd($statusId);

            $query->where('status_id', $statusId);
        }
    

        // Return data in DataTables format
        
        return Datatables::of($query)
            ->addColumn('progress', function ($requisition) {
                $progress = '';
                if ($requisition->authorization_status) {
                    $progress .= '<span class="badge bg-success me-1">Authorized</span>';
                }
                if ($requisition->funding_status) {
                    $progress .= '<span class="badge bg-success">Funded</span>';
                }
                // If no progress status is set, return a default value
                return $progress ?: '<span class="badge bg-default">No Progress</span>';
            })
            ->addColumn('user.name', function ($requisition) {
                return $requisition->user->name;
            })
            ->rawColumns(['progress'])
            ->make(true);
    
       /*  // Get the total number of records (without filters like pagination)
        $recordsTotal = $query->count();
    
        // Apply DataTables pagination and sorting
        $requisitions = $query->skip($request->start)
            ->take($request->length)
            ->get();
    
        // Format the response to meet DataTables requirements
        $response = [
            'draw' => intval($request->draw), // The draw count for DataTables
            'recordsTotal' => $recordsTotal,  // Total records before pagination
            'recordsFiltered' => $recordsTotal,  // Since there's no filter beyond status
            'data' => $requisitions,  // The actual requisition data
        ];
    
        // Return the JSON response
        return response()->json($response); */
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Requisition $requisition)
    {
        $requisition->delete();
        return response()->json(['message' => 'Requisition deleted successfully.']);
    }
}
