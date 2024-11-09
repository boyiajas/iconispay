<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Brian2694\Toastr\Facades\Toastr;
use Throwable;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
            // You can log or handle other exceptions here if needed
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Handle 419 Page Expired exception
        if ($exception instanceof HttpException && $exception->getStatusCode() === 419) {
            Toastr::error('Page expired. Please log in again.', 'Error');
            return redirect()->route('login');
        }

        // Handle TokenMismatchException
        if ($exception instanceof \Illuminate\Session\TokenMismatchException) {
            Toastr::error('Page expired. Please log in again.', 'Error');
            return redirect()->route('login');
        }

        return parent::render($request, $exception);
    }
}
