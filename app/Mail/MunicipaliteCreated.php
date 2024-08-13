<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MunicipaliteCreated extends Mailable
{
    use Queueable, SerializesModels;
    
    public $municipalite;
    public $password;
    /**
     * Create a new message instance.
     */
    public function __construct($municipalite, $password)
    {
        $this->municipalite = $municipalite;
        $this->password = $password;
    }


    /**
     * Get the message content definition.
     */
    public function build()
    {
        return $this->subject('Création de votre compte municipalité')
                    ->html("<h1>Bonjour,</h1>
                            <p>Nous avons le plaisir de vous informer que votre compte municipalité a été créé avec succès.</p>
                            <p><strong>Nom de la Commune :</strong> {$this->municipalite->nom_commune}</p>
                            <p><strong>Email :</strong> {$this->municipalite->user->email}</p>
                            <p><strong>Mot de passe :</strong> {$this->password}</p>
                            <p>Nous vous recommandons de vous connecter et de changer votre mot de passe dès que possible.</p>
                            <p>Cordialement,<br>Sama Gokh</p>");
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
