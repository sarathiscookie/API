<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEmailToSupplier extends Mailable
{
    use Queueable, SerializesModels;

    protected $suppliers;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $supplier)
    {
        $this->supplier = $supplier;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        Log::info('Mailable - Supplier Details: '. $this->supplier);
        return $this->markdown('emails.cron.supplier')
            ->to($this->supplier->email)
            ->subject('Test emails to supplier')
            ->with('supplier', $this->supplier);


        /* return $this->view('emails.sendInvoice')
            ->to($userDetails->usrEmail)
            ->bcc(env('MAIL_BCC_PAYMENT'))
            ->subject('Ihre Buchungsbelege von Huetten-Holiday.de')
            ->attach(public_path('/storage/Huetten-Holiday-AGB.pdf'), [
                'mime' => 'application/pdf',
            ])
            ->attach(public_path("/storage/Gutschein-". $this->bookingDetails->invoice_number . ".pdf"), [
                'mime' => 'application/pdf',
            ])
            ->with([
                'firstname' => $userDetails->usrFirstname,
                'lastname' => $userDetails->usrLastname,
                'userID' => $userDetails->_id,
                'subject' => 'Ihre Buchungsbelege von Huetten-Holiday.de'
            ]); */    
    }
}
