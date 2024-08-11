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
    public $projetNom; 

    public function __construct($habitant, $projetNom)
    {
        $this->habitant = $habitant;
        $this->projetNom = $projetNom; 
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmation de Soumission de Projet',
        );
    }

    public function build()
    {
        return $this->subject('Confirmation de Soumission de Projet')
        ->html("<h1>Bonjour {$this->habitant->prenom} {$this->habitant->nom},</h1>
                            <p>Nous avons le plaisir de vous informer que votre projet <strong>{$this->projetNom}</strong> a été ajouté avec succès sur la plateforme Sama Gokh.</p>
                            <p>Votre contribution est précieuse pour nous et pour la commune. Nous vous remercions pour votre engagement et vous tiendrons informé des prochaines étapes concernant votre projet.</p>
                            <p>Pour toute question ou information supplémentaire, n'hésitez pas à nous contacter.</p>
                            <p>Cordialement,<br>Équipe Sama Gokh</p>");
    }
}
