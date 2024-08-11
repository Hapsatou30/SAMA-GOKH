<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Email extends Mailable
{
    use Queueable, SerializesModels;

    public $habitant;

    public function __construct($habitant)
    {
        $this->habitant = $habitant;
    }
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ajout de Projet',
        );
    }

    public function build()
    {
        return $this->subject('Ajout de Projet')
                    ->html("<h1>Bonjour {$this->habitant->prenom},</h1><p>Votre projet a été ajouté avec succès.</p>");
    }
}
