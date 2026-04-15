<?php
namespace App\Libraries\Services;
use Illuminate\Support\Facades\Mail;

class MailService
{
    /**
     * Centralized function to send emails.
     *
     * @param string $view The email blade view.
     * @param array $data Data to pass to the view.
     * @param string $to Recipient's email address.
     * @param string $subject Subject of the email.
     * @param string $from Sender's email address (optional).
     * @param string $fromName Sender's name (optional).
     * @param array|null $cc CC recipients (optional).
     * @param array|null $bcc BCC recipients (optional).
     * @return void
     */
    public function sendMail($emailData)
    {
        $view = $emailData['template'];
        $to = $emailData['to'];
        $subject = $emailData['subject'];
        $from = $emailData['fromEmail'];
        $fromName = $emailData['fromName'];
        $cc = $emailData['cc'];
        $bcc = $emailData['bcc'];
        $mailTemplate = $emailData['mailTemplate'];
        $mailData = $emailData['mailData'];

        Mail::send($mailTemplate, $emailData, function ($message) use ($to, $subject, $from, $fromName, $cc, $bcc, $emailData) {
            $message->to($to)
                ->subject($subject)
                ->from($from, $fromName);

            // Add CC if provided
            if (!empty($cc)) {
                $message->cc($cc);
            }

            // Add BCC if provided
            if (!empty($bcc)) {
                $message->bcc($bcc);
            }

            if (isset($emailData['pdfPath'])) {
                if (!empty($emailData['pdfPath']) && file_exists($emailData['pdfPath'])) {
                    $message->attach($emailData['pdfPath'], [
                        'as' => $emailData['mailData']['emp_first_name'] . '.pdf', // Custom name for the PDF
                        'mime' => 'application/pdf',
                    ]);
                }
            }


        });
    }
}
