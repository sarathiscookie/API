<?php

namespace App\Mail;

use App\Shop;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEmailToSupplier extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $supplier;

    protected $shops;

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
        return $this->markdown('emails.cron.supplier')
            ->to($this->supplier->email)
            ->subject('Test emails to suppliers')
            ->with('supplier', $this->supplier);
    }
}
