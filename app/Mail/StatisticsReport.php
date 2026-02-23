<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StatisticsReport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $file_content;

    public function __construct($file)
    {
        $this->file_content = $file;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->view('plain_text')
                ->subject('PG - Lookup Summary By Month(App)')
                ->attachData($this->file_content, 'PG - Lookup_Summary_By_Month(App).xlsx');
    }
}
