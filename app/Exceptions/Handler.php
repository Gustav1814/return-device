<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Support\Facades\Mail;

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
            if ($this->shouldReport($e)) {
                try {
                    Mail::send('mails.error', ['exception' => $e], function ($message) {
                        $message->to('shahnawaz.phpdev@gmail.com') // Replace with your admin email
                            ->subject('Application Error');
                    });
                } catch (Throwable $mailException) {
                    // Log mail failures to avoid breaking the error reporting
                    \Log::error('Error sending exception email: ' . $mailException->getMessage());
                }
            }
        });
    }
}
