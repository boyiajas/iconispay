<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Jobs\SendNewUserNotificationJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get only active users with their roles
        $users = $user->hasRole('superadmin') 
                ? User::where('status', 'active')->with('roles', 'organisation', 'latestCertificate')->get()
                : User::whereOrganisationId(Auth::user()->organisation->id)->where('status', 'active')->with('roles', 'latestCertificate')->get();

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
        $user = Auth::user();
        
        // Get only active users with their roles
        return $user->hasRole('superadmin') 
               ? User::where('id', '!=', $user->id)->with('roles')->get()
               : User::whereOrganisationId($user->organisation->id)->where('id', '!=', $user->id)->with('roles')->get();
    }

    public function deactivateAccount(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->status = 'inactive'; // Assuming status field exists
        $user->save();

        return response()->json(['message' => 'User account has been locked.']);
    }

    public function activateAccount(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->status = 'active'; // Assuming status field exists
        $user->save();

        return response()->json(['message' => 'User account has been locked.']);
    }

    public function resetAccount(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user) {
            $user->google2fa_secret = null;
            $user->save();

            // Forward the request to ForgotPasswordController
            return app(ForgotPasswordController::class)->sendResetLinkEmail($request);
        }

        return response()->json(['error' => 'Unable to reset password, Email does not exist'], 400);

    }

    public function deactivatedUsers(Request $request)
    {
        $user = Auth::user();
        
        // Get only active users with their roles
        $users = $user->hasRole('superadmin') 
                ? User::where('status', 'inactive')->with('roles', 'latestCertificate')->get()
                : User::whereOrganisationId(Auth::user()->organisation->id)->where('status', 'inactive')->with('roles', 'latestCertificate')->get();

        // Use the DataTables facade to format the data
        return DataTables::of($users)
            ->addColumn('role', function ($user) {
                // Combine all role names into a single string
                return $user->roles->map(function ($role) {
                    return '<span class="badge badge-primary btn-primary">' . $role->name . '</span>';
                })->implode(' ');
            })
            ->rawColumns(['role']) // Ensure the HTML for badges is rendered
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function importUsers(Request $request)
    {
        // Validate the file upload
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv|max:2048',
        ]);

        $file = $request->file('file');
        $data = Excel::toArray([], $file)[0]; // Read the first sheet of the file

        $importedUsers = [];
        $errors = [];

        $loginUser = Auth::user();

        foreach ($data as $index => $row) {
            if ($index === 0) continue; // Skip the header row

            // Extract fields from the row
            $email = strtolower($row[2]) ?? null;
            $name = $row[1] ?? null;
            $roles = $row[3] ?? null;
            $password = isset($row[4]) ? strval($row[4]) : Str::random(8); // Generate a random password if not provided

            // Validate user data
            $validator = Validator::make([
                'name' => $name,
                'email' => $email,
                'password' => $password
            ], [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                $errors[] = [
                    'row' => $index + 1,
                    'errors' => $validator->errors()->all(),
                ];
                continue; // Skip invalid rows
            }

            // Create a new user
            $user = new User();
            $user->name = $name;
            $user->email = $email;
            $user->status = 'active';
            $user->password = Hash::make($password);
            $user->organisation_id = $loginUser->organisation->id;
            $user->syncRoles('user'); // Default role

            // Assign additional roles
            if (str_contains(strtolower($roles), 'admin')) {
                $user->syncRoles('admin');
            }
            if (str_contains(strtolower($roles), 'authoriser')) {
                $user->syncRoles('authoriser');
            }
            if (str_contains(strtolower($roles), 'bookkeeper')) {
                $user->syncRoles('bookkeeper');
            }

            $user->save();
            $importedUsers[] = $user;

            // Dispatch the notification job
            $emailData = [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'url' => route('login'),
                'senderName' => 'Iconis Pay',
            ];

            SendNewUserNotificationJob::dispatch($emailData);
            
        }

        return response()->json([
            'message' => 'Import process completed.',
            'imported_users' => $importedUsers,
            'errors' => $errors,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    { 
        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|string|email|max:255|unique:users',
            'password'     => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        $password = isset($request->password) ? strval($request->password) : Str::random(8); // Generate a random password if not provided

        $user = new User();
        $user->name      = $request->name;
        $user->email     = $request->email;
        $user->status    = 'active';
        $user->password  = Hash::make($password); 
        $user->organisation_id = $request->organisation_id ? $request->organisation_id : Auth::user()->organisation->id;
        $user->syncRoles('user');

        if($request->input('is_superadmin')){
            $user->syncRoles('superadmin');
        }
        if($request->input('is_admin')){
            $user->syncRoles('admin');
        }
        if($request->input('authoriser_role')){
            $user->syncRoles('authoriser');
        }
        if($request->input('bookkeeper_role')){
            $user->syncRoles('bookkeeper');
        }

        $user->save();

        // Dispatch the notification job
        $emailData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $password,
            'url' => route('login'),
            'senderName' => 'Iconis Pay',
        ];

        SendNewUserNotificationJob::dispatch($emailData);

        // Return the created requisition details
        return response()->json($user, 201);

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
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'roles' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->organisation_id = $request->organisation_id ? $request->organisation_id : Auth::user()->organisation->id;

        // Update password if provided
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

         // Sync roles
        if ($request->roles) {
            $roles = array_map('trim', explode(',', $request->roles)); // Parse comma-separated roles
            $user->syncRoles($roles);
        } else {
            // Assign the default 'user' role if no roles are provided
            $user->syncRoles(['user']);
        }

        return response()->json(['message' => 'User updated successfully!', 'user' => $user]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Delete certificates explicitly if needed
        $user->certificates()->each(function ($certificate) {
            // Optionally delete the certificate file from storage
            if ($certificate->file_path) {
                \Storage::delete($certificate->file_path);
            }
            $certificate->delete();
        });

        $user->delete();
        
        return response()->json(['message' => 'User deleted successfully.']);
    }
}
