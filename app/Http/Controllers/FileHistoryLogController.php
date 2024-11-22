<?php

namespace App\Http\Controllers;

use App\Models\FileHistoryLog;
use Illuminate\Http\Request;

class FileHistoryLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function show(FileHistoryLog $fileHistoryLog)
    {
        //
    }

    public function getFileHistory($fileUploadId)
    {
        $historyLogs = FileHistoryLog::where('file_upload_id', $fileUploadId)
            ->with('user')
            ->orderBy('log_date', 'desc')
            ->get();

        return response()->json($historyLogs);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FileHistoryLog $fileHistoryLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FileHistoryLog $fileHistoryLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FileHistoryLog $fileHistoryLog)
    {
        //
    }
}
