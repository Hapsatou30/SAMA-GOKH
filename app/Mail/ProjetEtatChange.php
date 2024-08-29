<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjetEtatChange extends Mailable
{
    use Queueable, SerializesModels;
    public $habitant;
    public $projetNom;
    /**
     * Create a new message instance.
     */
    public function __construct($habitant, $projetNom)
    {
        $this->habitant = $habitant;
        $this->projetNom = $projetNom;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Approbation de projet",
        );
    }

    /**
     * Get the message content definition.
     */
    public function build()
    {
        return $this->subject('Votre Projet a été Approuvé et Publié')
        ->html("<h1>Bonjour {$this->habitant->prenom} {$this->habitant->nom},</h1>
                <p>Nous avons le plaisir de vous informer que votre projet <strong>{$this->projetNom}</strong> a été approuvé et est désormais publié sur la plateforme Sama Gokh.</p>
                <p>Félicitations pour cette réussite ! Nous apprécions grandement votre contribution et votre engagement envers la commune.</p>
                <p>Cordialement,<br>Équipe Sama Gokh</p>");
    
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
