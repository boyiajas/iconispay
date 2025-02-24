<?php

namespace App\Http\Controllers;

use App\Enums\NotificationType;
use App\Mail\NotificationMail; // Assuming this is the new mailable class
use App\Models\Deposit;
use App\Models\FileHistoryLog;
use App\Models\FileUpload;
use App\Models\Payment;
use App\Models\Requisition;
use Auth;
use Carbon\Carbon;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class RequisitionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if ($user->hasRole('superadmin')) {
            // If the user is an admin or authorizer, get all incomplete requisitions
            $query = Requisition::with('user','lockedBy','payments','payments.beneficiaryAccount')->get();

        }else if ($user->hasRole('admin') || $user->hasRole('authoriser') || $user->hasRole('bookkeeper')) {
           // If the user is an admin or authorizer, get all incomplete requisitions
           $query = Requisition::with('user','lockedBy','payments','payments.beneficiaryAccount')->whereOrganisationId($user->organisation->id)->get();
       } else {
           // Otherwise, get incomplete requisitions created by the user
           $query = Requisition::with('user','lockedBy','payments','payments.beneficiaryAccount')
                                ->where('created_by', $user->id)
                                ->whereOrganisationId($user->organisation->id)->get();
           //$matters = Matter::with('status', 'user')::where('created_by', $user->id)->get();
       }
        /* $matters = Requisition::with('user')->get();
        return response()->json($matters); */
        

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
                if($requisition->status_id == 7){
                    return '<span class="badge bg-success me-1">Settled Successfully</span>';
                }
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

    public function getRequisitionHistory(Requisition $requisition)
    {
        $user = Auth::user();
        // Retrieve the history related to the requisition
        /* $histories = $requisition->histories()
            ->with('user:id,email,name') // Eager load user details
            ->orderBy('created_at', 'desc')
            ->get();

        // Return the history as JSON
        return response()->json($histories); */
        
        $histories = $user->hasRole('superadmin') 
                    ? $requisition->histories()->with('user:id,email') // Load user details
                        ->orderBy('created_at', 'desc')
                    : $requisition->histories()->whereOrganisationId($user->organisation->id)
                        ->with('user:id,email') // Load user details
                        ->orderBy('created_at', 'desc');

        return datatables()->eloquent($histories)->toJson();
    }

    public function searchRequisition(Request $request)
    {
        $request->validate([
            'file_reference' => 'required|string|max:150',
        ]);

        $searchTerm = strtolower($request->file_reference);

        $user = Auth::user();
        $requisitions = [];

        if ($user->hasRole('superadmin')) {
            
            $requisitions = Requisition::whereRaw("LOWER(file_reference) LIKE ?", ["%{$searchTerm}%"])
            ->with('user')
            ->latest('created_at')
            ->get();
        // Check if the user has an 'admin' or 'authorizer' role
        }else if ($user->hasRole('admin') || $user->hasRole('authoriser') || $user->hasRole('bookkeeper')) {
            // If the user is an admin or authorizer, get all incomplete requisitions
            //$query = Requisition::with('user','lockedBy','payments','payments.beneficiaryAccount');
            $requisitions = Requisition::whereRaw("LOWER(file_reference) LIKE ?", ["%{$searchTerm}%"])->whereOrganisationId($user->organisation->id)
            ->with('user')
            ->latest('created_at')
            ->get();
        } else {
            // Otherwise, get incomplete requisitions created by the user
            $requisitions = Requisition::whereRaw("LOWER(file_reference) LIKE ?", ["%{$searchTerm}%"])->whereOrganisationId($user->organisation->id)
            ->where('created_by', $user->id)
            ->with('user')
            ->latest('created_at')
            ->get();
            //$query = Requisition::with('user','lockedBy','payments','payments.beneficiaryAccount')->where('created_by', $user->id)->get();
            //$matters = Matter::with('status', 'user')::where('created_by', $user->id)->get();
        }
    
        

        if ($requisitions->count() === 1) {
            return response()->json([
                'redirect' => url("/matters/requisitions/{$requisitions->first()->id}/details"),
            ]);
        }

        return response()->json([
            'requisitions' => $requisitions,
        ]);
    }

    public function fetchAutoFillRequisition(Request $request)
    {
        $request->validate([
            'file_reference' => 'required|string|max:150',
        ]);

        $user = Auth::user();
        $query = Requisition::where('file_reference', $request->file_reference);

        // Apply filtering based on user role
        if ($user->hasRole('superadmin')) {
            // Superadmin can fetch any requisition
            $requisition = $query->latest('created_at')->first();
        } elseif ($user->hasRole('admin') || $user->hasRole('authoriser') || $user->hasRole('bookkeeper')) {
            // Admins, Authorisers, and Bookkeepers can fetch requisitions within their organization
            $requisition = $query->where('organisation_id', $user->organisation->id)
                ->latest('created_at')
                ->first();
        } else {
            // Regular users can only fetch their own requisitions within their organization
            $requisition = $query->where('organisation_id', $user->organisation->id)
                ->where('created_by', $user->id)
                ->latest('created_at')
                ->first();
        }

        if ($requisition) {
            return response()->json([
                'reason' => $requisition->reason,
                'parties' => $requisition->parties,
            ]);
        }

        return response()->json(null, 202);
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
            'organisation_id' => Auth::user()->organisation->id,
        ]);

        logHistory($requisition->id, 'Create Requisition', 'Requisition was created.');

        // Return the created requisition details
        return response()->json($requisition, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Requisition $requisition)
    {
        
        try {
            // Eager load the relationships
            $requisition->load(
                'user',
                'authorizedBy',
                'lockedBy',
                'firmAccount.institution',
                'payments.beneficiaryAccount.institution',
                'payments.payToFirmAccount.institution',
                'payments.beneficiaryAccount.accountType',
                'payments.payToFirmAccount.accountType',
                //'payments.payToFirmAccount',
                'payments.sourceFirmAccount',
                'deposits.firmAccount',
                'deposits.user',
            );

            // Transform the requisition data to include payToAccount details
            $requisitionData = $requisition->toArray();

            // Add the file upload ID to the data
            $requisitionData['file_upload_id'] = $requisition->getFileUploadId();

            // Add payToAccount details for each payment
            $requisitionData['payments'] = $requisition->payments->map(function ($payment) {
                $paymentData = $payment->toArray();

                // Get the payToAccount and include its institution details
                $payToAccount = $payment->payToAccount;
                $payToAccountData = $payToAccount ? $payToAccount->toArray() : null;

                if ($payToAccount && $payToAccount->institution) {
                    $payToAccountData['institution'] = $payToAccount->institution->toArray();
                }

                if ($payToAccount && $payToAccount->accountType) {
                    $payToAccountData['account_type'] = $payToAccount->accountType->toArray();
                }

                // Include the transformed payToAccount in the payment data
                $paymentData['beneficiary_account'] = $payToAccountData;
               
                return $paymentData;
            });

            return response()->json($requisitionData);
            
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

    public function updateRequisition(Request $request, Requisition $requisition)
    {
        $validatedData = $request->validate([
            'file_reference' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:255',
            'parties' => 'nullable|string|max:255',
            'properties' => 'nullable|string|max:255',
            'status_id' => 'nullable|integer|exists:statuses,id',
            'firm_account_id' => 'nullable|integer|exists:firm_accounts,id',
            'transaction_value' => 'nullable|numeric',
            'capturing_status' => 'nullable|string|max:255',
            'authorization_status' => 'nullable|string|max:255',
            'locked' => 'nullable|boolean',
            'completed_at' => 'nullable|date',
        ]);

        // Update the requisition with validated data
        $requisition->update($validatedData);

        // Load related data if needed
        $requisition->load('user', 'authorizedBy', 'lockedBy', 'firmAccount', 'payments', 'deposits');

        return response()->json([
            'message' => 'Requisition updated successfully',
            'requisition' => $requisition,
        ]);
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
            //'status_id' => 2, //set to incompleted
        ]);

         // Retrieve all deposits associated with the requisition
        $deposits = Deposit::where('requisition_id', $requisition->id)->get();
        if ($deposits->isNotEmpty()) {
            // Loop through each deposit and update the firm_account_id
            foreach ($deposits as $deposit) {
                $deposit->update([
                    'firm_account_id' => $request->firm_account_id,
                ]);
            }
    
            // Optionally update the requisition status to 3 if deposits exist
            $requisition->update(['status_id' => 3]);
        }

        // Log the history
        logHistory($requisition->id, 'Source Account Updated', "Requisition source account # {$requisition->firmAccount->display_text} - {$requisition->firmAccount->account_number} was successfully updated.");

        return response()->json([
            'message' => 'Requisition updated successfully',
            'requisition' => $requisition
        ]);
    }

    public function updateStatus(Request $request)
    {
        // Validate the request
        $request->validate([
            'requisitionIds' => 'required|array',
            'requisitionIds.*' => 'integer'
        ]);

        // Update the status of the requisitions to 7
        Requisition::whereIn('id', $request->requisitionIds)->update([
            'status_id' => 7,
            'completed_at' => Carbon::now(), 
        ]);

        return response()->json(['message' => 'Requisitions updated successfully.']);
    }


    public function approve(Requisition $requisition)
    {
        // Update the requisition with the new values
        /* $requisition->update([
            'authorization_status' => 1,                  // Set authorization status to approved
            'status_id' => 5,                             // Update status ID
            'authorized_user_id' => auth()->id(),         // Set the current user as the authorizer
            'authorized_at' => Carbon::now(),            // Set the current time for authorization
            'locked' => 1, 
            'locked_at' => Carbon::now(),
            'locked_by' => auth()->id(),
        ]); */

        // Check the category_id of the firm account
        $firmAccount = $requisition->firmAccount;

        if ($firmAccount) {
            if ($firmAccount->category_id == 14) { //if the firm account is a Trust account 
                $requisition->status_id = 4;
            } elseif ($firmAccount->category_id == 12) { //if the firm account is a bussiness account
                $requisition->status_id = 5;
            }
        }

        // Update the requisition with the new values
        $requisition->update([
            'authorization_status' => 1,                  // Set authorization status to approved
            'status_id' => $requisition->status_id,       // Use the updated status based on firmAccount category_id
            'authorized_user_id' => auth()->id(),         // Set the current user as the authorizer
            'authorized_at' => now(),            // Set the current time for authorization
            'locked' => 1, 
            'locked_at' => Carbon::now(),
            'locked_by' => auth()->id(),
        ]);

        $requisition->calculateTransactionValue();
        $requisition->load(
            'user', 
            'authorizedBy', 
            'lockedBy',
            'firmAccount.institution', 
            'payments.beneficiaryAccount.institution',
            'payments.payToFirmAccount.institution',
            'payments.beneficiaryAccount.accountType',
            'payments.payToFirmAccount.accountType',
            'payments.sourceFirmAccount',
            'deposits.firmAccount',
            'deposits.user'
        );

        // Transform the requisition data to include payToAccount details
        $requisitionData = $requisition->toArray();

        // Add payToAccount details for each payment
        $requisitionData['payments'] = $requisition->payments->map(function ($payment) {
            $paymentData = $payment->toArray();

            // Get the payToAccount and include its institution details
            $payToAccount = $payment->payToAccount;
            $payToAccountData = $payToAccount ? $payToAccount->toArray() : null;

            if ($payToAccount && $payToAccount->institution) {
                $payToAccountData['institution'] = $payToAccount->institution->toArray();
            }

            if ($payToAccount && $payToAccount->accountType) {
                $payToAccountData['account_type'] = $payToAccount->accountType->toArray();
            }

            // Include the transformed payToAccount in the payment data
            $paymentData['beneficiary_account'] = $payToAccountData;
        
            return $paymentData;
        });

        // Send notification emails to all users subscribed to matter_authorized start here
            $users = $requisition->notifications()
            ->where('notification_type', NotificationType::MATTER_AUTHORISED)
            ->with('user')
            ->get()
            ->pluck('user');

        foreach ($users as $user) {
            $emailData = [
                'subject' => 'Requisition Approved',
                'greeting' => 'Hello ' . $user->name,
                'message' => "The requisition #{$requisition->file_reference} has been approved.",
                //'url' => route('requisition.details', ['requisition' => $requisition->id]),
                'url' => url("/matters/requisitions/{$requisition->id}/details"),
                'senderName' => 'Iconis Pay',
                /* 'unsubscribeLink' => route('notifications.unsubscribe', ['user' => $user->id]),
                'preferencesLink' => route('notifications.preferences', ['user' => $user->id]), */
            ];
            Mail::to($user->email)->queue(new NotificationMail($emailData));
        }
        // Send notification emails to all users subscribed to matter_authorized ends here

        // Log the history
        logHistory($requisition->id, 'Approve Requisition', 'Requisition was approved.');
        \Log::info("After Approval: authorized_at = " . $requisition->authorized_at);

        return response()->json($requisitionData);

        //return response()->json($requisition);
    }

    public function unlockRequisition(Requisition $requisition)
    {
        // Update the requisition with the new values
        $requisition->update([
            'authorization_status' => null,                  // Set authorization status to approved
            'status_id' => 3,                             // Update status ID
            'authorized_user_id' => null,         // Set the current user as the authorizer
            'authorized_at' => null,            // Set the current time for authorization
            'locked' => null,
            'locked_at' => null,
            'locked_by' => null,
        ]);

        $requisition->calculateTransactionValue();
        $requisition->load(
            'user', 
            'authorizedBy', 
            'lockedBy',
            'firmAccount.institution', 
            'payments.beneficiaryAccount.institution',
            'payments.payToFirmAccount.institution',
            'payments.beneficiaryAccount.accountType',
            'payments.payToFirmAccount.accountType',
            'payments.sourceFirmAccount',
            'deposits.firmAccount',
            'deposits.user'
        );

        // Transform the requisition data to include payToAccount details
        $requisitionData = $requisition->toArray();

        // Add payToAccount details for each payment
        $requisitionData['payments'] = $requisition->payments->map(function ($payment) {
            $paymentData = $payment->toArray();

            // Get the payToAccount and include its institution details
            $payToAccount = $payment->payToAccount;
            $payToAccountData = $payToAccount ? $payToAccount->toArray() : null;

            if ($payToAccount && $payToAccount->institution) {
                $payToAccountData['institution'] = $payToAccount->institution->toArray();
            }

            if ($payToAccount && $payToAccount->accountType) {
                $payToAccountData['account_type'] = $payToAccount->accountType->toArray();
            }

            // Include the transformed payToAccount in the payment data
            $paymentData['beneficiary_account'] = $payToAccountData;
        
            return $paymentData;
        });

        // Send notification emails to all users subscribed to matter_unlocked start here
            $users = $requisition->notifications()
            ->where('notification_type', NotificationType::MATTER_UNLOCKED)
            ->with('user')
            ->get()
            ->pluck('user');

        foreach ($users as $user) {
            $emailData = [
                'subject' => 'Requisition Unlocked',
                'greeting' => 'Hello ' . $user->name,
                'message' => "The requisition #{$requisition->file_reference} has been unlocked.",
                //'url' => route('requisition.details', ['requisition' => $requisition->id]),
                'url' => url("/matters/requisitions/{$requisition->id}/details"),
                'senderName' => 'Iconis Pay',
                /* 'unsubscribeLink' => route('notifications.unsubscribe', ['user' => $user->id]),
                'preferencesLink' => route('notifications.preferences', ['user' => $user->id]), */
            ];
            Mail::to($user->email)->queue(new NotificationMail($emailData));
        }
        // Send notification emails to all users subscribed to matter_unlocked ends here

        // Log the history
        logHistory($requisition->id, 'Unlock Requisition', 'Requisition was unlocked.');

        return response()->json($requisitionData);

        //$requisition->load('user', 'authorizedBy', 'firmAccount.institution', 'payments.beneficiaryAccount', 'payments.beneficiaryAccount.institution', 'deposits.firmAccount', 'deposits.user');

        //return response()->json($requisition);
    }

    public function lockRequisition(Requisition $requisition)
    {
        // Update the requisition with the new values
        $requisition->update([
            'locked' => 1,
            'locked_at' => Carbon::now(),
            'locked_by' => auth()->id(),
        ]);

        $requisition->calculateTransactionValue();
        $requisition->load(
            'user', 
            'authorizedBy',
            'lockedBy',
            'firmAccount.institution', 
            'payments.beneficiaryAccount.institution',
            'payments.payToFirmAccount.institution',
            'payments.beneficiaryAccount.accountType',
            'payments.payToFirmAccount.accountType',
            'payments.sourceFirmAccount',
            'deposits.firmAccount',
            'deposits.user'
        );

        // Log the history
        logHistory($requisition->id, 'lock Requisition', 'Requisition was locked.');

        // Transform the requisition data to include payToAccount details
        $requisitionData = $requisition->toArray();

        // Add payToAccount details for each payment
        $requisitionData['payments'] = $requisition->payments->map(function ($payment) {
            $paymentData = $payment->toArray();

            // Get the payToAccount and include its institution details
            $payToAccount = $payment->payToAccount;
            $payToAccountData = $payToAccount ? $payToAccount->toArray() : null;

            if ($payToAccount && $payToAccount->institution) {
                $payToAccountData['institution'] = $payToAccount->institution->toArray();
            }

            if ($payToAccount && $payToAccount->accountType) {
                $payToAccountData['account_type'] = $payToAccount->accountType->toArray();
            }

            // Include the transformed payToAccount in the payment data
            $paymentData['beneficiary_account'] = $payToAccountData;
        
            return $paymentData;
        });

        return response()->json($requisitionData);

        //$requisition->load('user', 'authorizedBy', 'firmAccount.institution', 'payments.beneficiaryAccount', 'payments.beneficiaryAccount.institution', 'deposits.firmAccount', 'deposits.user');

        //return response()->json($requisition);
    }

    

    public function getIncompleteRequisitions(Request $request)
    {
        // Get the currently authenticated user ID
        $user = Auth::user();
        $incompleteStatusId = 2;
        $incompleteCount = 0;
        $defaultStatusId = 1;
        

        if ($user->hasRole('superadmin')) {
            // If the user is an admin or authorizer, get all incomplete requisitions
            $incompleteCount = Requisition::whereIn('status_id', [$incompleteStatusId, $defaultStatusId])->count();
         // Check if the user has an 'admin' or 'authorizer' role
        }else if ($user->hasRole('admin') || $user->hasRole('authoriser') || $user->hasRole('bookkeeper')) {
            // If the user is an admin or authorizer, get all incomplete requisitions
            $incompleteCount = Requisition::whereOrganisationId($user->organisation->id)
                                            ->whereIn('status_id', [$incompleteStatusId, $defaultStatusId])->count();
        } else {
            // Otherwise, get incomplete requisitions created by the user
            $incompleteCount = Requisition::whereCreatedBy($user->id)
                                        ->whereOrganisationId($user->organisation->id)
                                        ->whereIn('status_id', [$incompleteStatusId, $defaultStatusId])
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

        if ($user->hasRole('superadmin')) {
         // Check if the user has an 'admin' or 'authorizer' role
            $count = Requisition::whereHas('deposits', function ($query) {
                $query->whereFunded(false);
        })->count();
        }else if ($user->hasRole('admin') || $user->hasRole('authoriser') || $user->hasRole('bookkeeper')) {
            // If the user is an admin or authorizer, get all incomplete requisitions
            $count = Requisition::whereOrganisationId($user->organisation->id)->whereHas('deposits', function ($query) {
                $query->whereFunded(false);
            })->count();

        } else {
            $count = Requisition::whereOrganisationId($user->organisation->id)->whereHas('deposits', function ($query) use ($user) {
                $query->whereFunded(false)->whereCreatedBy($user->id);
            })->count();
        }

        return response()->json(['count' => $count]);
    }

    public function getAwaitingAuthorization(Request $request)
    {
        // Get the currently authenticated user ID
        $user = Auth::user();
        $awaitingAuthorizationStatusId = 3;
        $awaitingFundingStatusId = 4;
        $awaitingAuthorizationCount = 0;
      

        if ($user->hasRole('superadmin')) {
            // Check if the user has an 'admin' or 'authorizer' role
            $awaitingAuthorizationCount = Requisition::whereIn('status_id', [$awaitingAuthorizationStatusId, $awaitingFundingStatusId])
                ->whereNull('authorization_status')
                ->count();
          
         // Check if the user has an 'admin' or 'authorizer' role
        }else if ($user->hasRole('admin') || $user->hasRole('authoriser') || $user->hasRole('bookkeeper')) {
            // If the user is an admin or authorizer, get all incomplete requisitions
            $awaitingAuthorizationCount = Requisition::whereOrganisationId($user->organisation->id)
                                          ->whereIn('status_id', [$awaitingAuthorizationStatusId, $awaitingFundingStatusId])
                                          ->whereNull('authorization_status')
                                          ->count();
        } else {
            // Otherwise, get incomplete requisitions created by the user
            $awaitingAuthorizationCount = Requisition::whereCreatedBy($user->id)->whereOrganisationId($user->organisation->id)
                                        ->whereIn('status_id', [$awaitingAuthorizationStatusId, $awaitingFundingStatusId])
                                        ->count();
        }
       
        // Return the count as a JSON response
        return response()->json([
            'count' => $awaitingAuthorizationCount,
        ]);
    }

    public function getSettledTodayRequisitions()
    {
        // Get the currently authenticated user
        $user = Auth::user();
        $statusId = 7; // Status ID for "settled" requisitions
        $today = Carbon::today();

        // Count of requisitions settled today
        $settledTodayCount = 0;

        if ($user->hasRole('superadmin')) {
        // Check if the user has an 'admin' or 'authorizer' role
            $settledTodayCount = Requisition::whereStatusId($statusId)
            ->whereDate('updated_at', $today)
            ->count();
        }else if ($user->hasRole('admin') || $user->hasRole('authoriser') || $user->hasRole('bookkeeper')) {
            // If the user is an admin or authorizer, get all settled requisitions for today
            $settledTodayCount = Requisition::whereOrganisationId($user->organisation->id)->whereStatusId($statusId)
                ->whereDate('updated_at', $today)
                ->count();
        } else {
            // Otherwise, get settled requisitions created by the user for today
            $settledTodayCount = Requisition::whereOrganisationId($user->organisation->id)->whereCreatedBy($user->id)
                ->whereStatusId($statusId)
                ->whereDate('updated_at', $today)
                ->count();
        }

        // Return the count as a JSON response
        return response()->json([
            'count' => $settledTodayCount,
        ]);
    }
    
    public function getReadyForPayment(Request $request)
    {
        // Get the currently authenticated user ID
        $user = Auth::user();
        $readyForPaymentStatusId = 5;
        $readyForPaymentCount = 0;
        
        if ($user->hasRole('superadmin')) {
         // Check if the user has an 'admin' or 'authorizer' role
            $readyForPaymentCount = Requisition::whereStatusId($readyForPaymentStatusId)->count();
        }else if ($user->hasRole('admin') || $user->hasRole('authoriser') || $user->hasRole('bookkeeper')) {
            // If the user is an admin or authorizer, get all incomplete requisitions
            $readyForPaymentCount = Requisition::whereOrganisationId($user->organisation->id)->whereStatusId($readyForPaymentStatusId)->count();
        } else {
            // Otherwise, get incomplete requisitions created by the user
            $readyForPaymentCount = Requisition::whereOrganisationId($user->organisation->id)->whereCreatedBy($user->id)
                                        ->whereStatusId($readyForPaymentStatusId)
                                        ->count();
        }
       
        // Return the count as a JSON response
        return response()->json([
            'count' => $readyForPaymentCount,
        ]);
    }

    public function getPendingPaymentConfirmation(Request $request)
    {
        // Get the currently authenticated user ID
        $user = Auth::user();
        $pendingPaymentConfirmationStatusId = 6;
        $pendingPaymentConfirmationCount = 0;
        
        if ($user->hasRole('superadmin')) {
         // Check if the user has an 'admin' or 'authorizer' role
            $pendingPaymentConfirmationCount = Requisition::whereStatusId($pendingPaymentConfirmationStatusId)->count();
        }else if ($user->hasRole('admin') || $user->hasRole('authoriser') || $user->hasRole('bookkeeper')) {
            // If the user is an admin or authorizer, get all incomplete requisitions
            $pendingPaymentConfirmationCount = Requisition::whereOrganisationId($user->organisation->id)->whereStatusId($pendingPaymentConfirmationStatusId)->count();
        } else {
            // Otherwise, get incomplete requisitions created by the user
            $pendingPaymentConfirmationCount = Requisition::whereOrganisationId($user->organisation->id)->whereCreatedBy($user->id)
                                        ->whereStatusId($pendingPaymentConfirmationStatusId)
                                        ->count();
        }
       
        // Return the count as a JSON response
        return response()->json([
            'count' => $pendingPaymentConfirmationCount,
        ]);
    }  


    public function secureDownload($fileId)
    {
        $user = Auth::user();
        // Find the file upload record and include the associated FirmAccount
        $fileUpload = $user->hasRole('superadmin') 
                        ? FileUpload::with('firmAccount.institution')->findOrFail($fileId)
                        : FileUpload::whereOrganisationId($user->organisation->id)->with('firmAccount.institution')->findOrFail($fileId);

        // Use policy to authorize
        $this->authorize('download', $fileUpload);

        // Check if the authenticated user has permission to access the file
        // You can customize this logic based on your requirements
        if (auth()->user()->cannot('download', $fileUpload)) {
            abort(403, 'Unauthorized access');
        }

        // Path to the private file in storage/app/files
        $filePath = 'files/' . $fileUpload->file_name;

        // Ensure the file exists before downloading
        if (!Storage::disk('local')->exists($filePath)) {
            abort(404, 'File not found');
        }   
        // Clean the output buffer to prevent file corruption
        if (ob_get_level()) {
            ob_end_clean();
        }

        $firmAccount = $fileUpload->firmAccount;

        //here we want to insert the export file date
        

        FileHistoryLog::logFileHistory($fileUpload->id, 'Downloaded Payaway File', "Downloaded the payaway file {$firmAccount->institution->short_name} - {$firmAccount->account_number} ({$fileUpload->generated_at->format('Ymd Hi')})");

        // Stream the file to the browser
        return Storage::disk('local')->download($filePath, $fileUpload->file_name);
    }


    public function getRequisitionsByStatus(Request $request)
    { //dd($request->status, $request->filter_text);
        // Map status names to status IDs (adjust based on your database)
        $statusMapping = [
            'Incomplete' => 2,                // Assuming 1 is 'Incomplete'
            'Awaiting Authorisation' => 3,    // Adjust these values as per your database
            'Awaiting Funding' => 4,
            'Ready for Payment' => 5,
            'Pending Payment Confirmation' => 6,
            'Settled Today' => 7,
            'Settlement Failed' => 8,
        ];

        $awaitingFundingStatusId = 4;

        // Get the currently authenticated user ID
        $user = Auth::user();
        $awaitingAuthorizationStatusId = 3;
        $incompleteCount = 0;
        
        if ($user->hasRole('superadmin')) {
        // Check if the user has an 'admin' or 'authorizer' role
            $query = Requisition::with('user');
        }else if ($user->hasRole('admin') || $user->hasRole('authoriser') || $user->hasRole('bookkeeper')) {
            // Fetch the requisitions matching the status and created by the logged-in user
            $query = Requisition::whereOrganisationId($user->organisation->id)->with('user');
        } else {
            // Fetch the requisitions matching the status and created by the logged-in user
            $query = Requisition::whereOrganisationId($user->organisation->id)->with('user')->where('created_by', $user->id);
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
            
            $today = Carbon::today();


            // Get the status ID from the mapping
            $statusId = $statusMapping[$request->status]; //dd($statusId);

            if($statusId == 2){
                $query->whereIn('status_id', [$statusId, 1]);
            }else if($statusId == 3){
                $query->whereIn('status_id', [$statusId, $awaitingFundingStatusId])->whereNull('authorization_status'); // Fixed
            }else if($statusId == 4){
                $query->whereIn('status_id', [$statusId, $awaitingAuthorizationStatusId])->whereNull('funding_status');
            }else if($statusId == 7){
                $query->where('status_id', $statusId)->whereDate('updated_at', $today);
            }else{
                $query->where('status_id', $statusId);
            }

            
        }
    

        // Return data in DataTables format
        
        return Datatables::of($query)
            ->addColumn('progress', function ($requisition) {
                $progress = '';

                if($requisition->status_id == 7){
                    return '<span class="badge bg-success me-1">Settled Successfully</span>';
                }
                if ($requisition->authorization_status) {
                    $progress .= '<span class="badge bg-success me-1">Authorized</span>';
                }
                if($requisition->status_id == 3 && !$requisition->authorization_status){
                    $progress .= '<span class="badge bg-default me-1">Authorized</span>';
                }
                if($requisition->status_id == 3 && !$requisition->authorization_status && !$requisition->funding_status){
                    $progress .= '<span class="badge bg-default me-1">Funded</span>';
                }
               /*  if ($requisition->status_id == 4) {
                    $progress .= '<span class="badge bg-default me-1">Funded</span>';
                } */
                if($requisition->status_id == 4 && !$requisition->authorization_status){
                    $progress .= '<span class="badge bg-default me-1">Authorized</span>';
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

    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Requisition $requisition)
    {
        // Delete associated Notifications first
        $requisition->notifications()->delete();
       // Retrieve and delete associated FileUpload records
        $fileUploads = $requisition->fileUploads;

        foreach ($fileUploads as $fileUpload) {
             //$sizedFilePath = $originalFilePath . $size . '.' . $extension;
            if (File::exists($fileUpload->file_path)) {
                File::delete($fileUpload->file_path);
            }
            $fileUpload->delete();
        }

        Payment::whereRequisitionId($requisition->id)->delete();
        // Delete associated Deposit records
        Deposit::where('requisition_id', $requisition->id)->delete();

        logHistory($requisition->id, 'Delete Requisition', "Requisition with file reference # {$requisition->file_reference} and parties {$requisition->parties} was deleted.");

        $requisition->delete();

        
        return response()->json(['message' => 'Requisition deleted successfully.']);
    }
}
