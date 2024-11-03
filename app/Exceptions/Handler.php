<?php

namespace App\Exceptions;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        // Only check for HTTP exceptions
        if ($exception instanceof HttpException && $exception->getStatusCode() == 419) {
            Toastr::error('Page expired. Please login again', 'Error');
            return redirect()->route('login');
        }

        return parent::render($request, $exception);
    }

    /**
     * Render an exception into an HTTP response.
     */
    /* public function render($request, Throwable $exception)
    {
        // Check if the exception status code is 419 (Page Expired)
        if ($exception->getStatusCode() == 419) {
            Toastr::error('error, Page expired. Please login again','Error');
            return redirect()->route('login');
        }

        return parent::render($request, $exception);
    } */
}
