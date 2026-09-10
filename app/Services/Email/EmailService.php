<?php

namespace App\Services\Email;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Envoyer un email de validation du cabinet
     */
    public function sendCabinetVerified(User $user, string $cabinetNom): void
    {
        if (empty($user->email)) {
            Log::warning('Email non envoyé - utilisateur sans email', [
                'user_id' => $user->id,
                'user_nom' => $user->nom,
                'cabinet' => $cabinetNom,
            ]);
            return;
        }

        try {
            $subject = 'Votre cabinet Opticare est validé !';
            $message = $this->getCabinetVerifiedMessage($user, $cabinetNom);
            $this->sendEmail($user->email, $user->prenom, $subject, $message);
        } catch (\Exception $e) {
            Log::error('Erreur envoi email validation : ' . $e->getMessage());
        }
    }

    /**
     * Envoyer un email de refus du cabinet
     */
    public function sendCabinetRejected(User $user, string $cabinetNom, string $motif): void
    {
        if (empty($user->email)) {
            Log::warning('Email non envoyé - utilisateur sans email', [
                'user_id' => $user->id,
                'user_nom' => $user->nom,
                'cabinet' => $cabinetNom,
            ]);
            return;
        }

        try {
            $subject = 'Statut de votre demande Opticare';
            $message = $this->getCabinetRejectedMessage($user, $cabinetNom, $motif);
            $this->sendEmail($user->email, $user->prenom, $subject, $message);
        } catch (\Exception $e) {
            Log::error('Erreur envoi email refus : ' . $e->getMessage());
        }
    }

    /**
     * Envoyer un email de confirmation de RDV
     */
    public function sendRdvConfirmation(string $to, string $name, string $cabinetNom, string $date, string $heure): void
    {
        if (empty($to)) {
            Log::warning('Email non envoyé - destinataire sans email');
            return;
        }

        try {
            $subject = '📅 Confirmation de votre RDV Opticare';
            $message = $this->getRdvConfirmationMessage($name, $cabinetNom, $date, $heure);
            $this->sendEmail($to, $name, $subject, $message);
        } catch (\Exception $e) {
            Log::error('Erreur envoi email confirmation RDV : ' . $e->getMessage());
        }
    }

    /**
     * Envoyer un email d'annulation de RDV
     */
    public function sendRdvAnnule(string $to, string $name, string $cabinetNom, string $date, string $heure): void
    {
        if (empty($to)) {
            Log::warning('Email non envoyé - destinataire sans email');
            return;
        }

        try {
            $subject = '❌ Annulation de votre RDV Opticare';
            $message = $this->getRdvAnnuleMessage($name, $cabinetNom, $date, $heure);
            $this->sendEmail($to, $name, $subject, $message);
        } catch (\Exception $e) {
            Log::error('Erreur envoi email annulation RDV : ' . $e->getMessage());
        }
    }

    /**
     * Envoyer un email de modification de RDV
     */
    public function sendRdvModifie(string $to, string $name, string $cabinetNom, string $date, string $heure, string $ancienneDate, string $ancienneHeure): void
    {
        if (empty($to)) {
            Log::warning('Email non envoyé - destinataire sans email');
            return;
        }

        try {
            $subject = '📅 Modification de votre RDV Opticare';
            $message = $this->getRdvModifieMessage($name, $cabinetNom, $date, $heure, $ancienneDate, $ancienneHeure);
            $this->sendEmail($to, $name, $subject, $message);
        } catch (\Exception $e) {
            Log::error('Erreur envoi email modification RDV : ' . $e->getMessage());
        }
    }

    /**
     * Envoyer un email de commande terminée
     */
    public function sendCommandeTerminee(string $to, string $name, int $commandeId, string $cabinetNom): void
    {
        if (empty($to)) {
            Log::warning('Email non envoyé - destinataire sans email');
            return;
        }

        try {
            $subject = '👓 Votre commande Opticare est terminée !';
            $message = $this->getCommandeTermineeMessage($name, $commandeId, $cabinetNom);
            $this->sendEmail($to, $name, $subject, $message);
        } catch (\Exception $e) {
            Log::error('Erreur envoi email commande terminée : ' . $e->getMessage());
        }
    }

    // ============================================
    // MÉTHODES PRIVÉES - TEMPLATES
    // ============================================

    private function getCabinetVerifiedMessage(User $user, string $cabinetNom): string
    {
        return "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #2d3748; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; background: #f7fafc; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #718096; }
                    .btn { display: inline-block; padding: 10px 20px; background: #48bb78; color: white; text-decoration: none; border-radius: 5px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'><h1>🎉 Félicitations !</h1></div>
                    <div class='content'>
                        <p>Bonjour <strong>{$user->prenom} {$user->nom}</strong>,</p>
                        <p>Nous avons le plaisir de vous informer que votre cabinet <strong>\"{$cabinetNom}\"</strong> a été <strong>validé</strong> par notre équipe.</p>
                        <p>Vous pouvez maintenant vous connecter à votre espace Opticare et commencer à gérer votre cabinet.</p>
                        <p style='text-align: center;'>
                            <a href='https://opticare.com/login' class='btn'>Se connecter</a>
                        </p>
                        <p>Cordialement,<br><strong>L'équipe Opticare</strong></p>
                    </div>
                    <div class='footer'><p>&copy; 2026 Opticare. Tous droits réservés.</p></div>
                </div>
            </body>
            </html>
        ";
    }

    private function getCabinetRejectedMessage(User $user, string $cabinetNom, string $motif): string
    {
        return "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #e53e3e; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; background: #f7fafc; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #718096; }
                    .motif { background: #fff; border-left: 4px solid #e53e3e; padding: 15px; margin: 15px 0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'><h1>❌ Demande non validée</h1></div>
                    <div class='content'>
                        <p>Bonjour <strong>{$user->prenom} {$user->nom}</strong>,</p>
                        <p>Nous avons examiné votre demande pour le cabinet <strong>\"{$cabinetNom}\"</strong>.</p>
                        <p>Malheureusement, nous ne pouvons pas la valider pour le moment.</p>
                        <div class='motif'>
                            <p><strong>Motif du refus :</strong></p>
                            <p>{$motif}</p>
                        </div>
                        <p>Cordialement,<br><strong>L'équipe Opticare</strong></p>
                    </div>
                    <div class='footer'><p>&copy; 2026 Opticare. Tous droits réservés.</p></div>
                </div>
            </body>
            </html>
        ";
    }

    private function getRdvConfirmationMessage(string $name, string $cabinetNom, string $date, string $heure): string
    {
        return "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #2d3748; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; background: #f7fafc; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #718096; }
                    .info { background: #e2e8f0; padding: 15px; border-radius: 5px; margin: 10px 0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'><h1>📅 RDV confirmé</h1></div>
                    <div class='content'>
                        <p>Bonjour <strong>{$name}</strong>,</p>
                        <p>Votre RDV a été confirmé.</p>
                        <div class='info'>
                            <p><strong>Cabinet :</strong> {$cabinetNom}</p>
                            <p><strong>Date :</strong> {$date}</p>
                            <p><strong>Heure :</strong> {$heure}</p>
                        </div>
                        <p>Cordialement,<br><strong>L'équipe Opticare</strong></p>
                    </div>
                    <div class='footer'><p>&copy; 2026 Opticare. Tous droits réservés.</p></div>
                </div>
            </body>
            </html>
        ";
    }

    private function getRdvAnnuleMessage(string $name, string $cabinetNom, string $date, string $heure): string
    {
        return "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #e53e3e; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; background: #f7fafc; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #718096; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'><h1>❌ RDV annulé</h1></div>
                    <div class='content'>
                        <p>Bonjour <strong>{$name}</strong>,</p>
                        <p>Votre RDV du <strong>{$date}</strong> à <strong>{$heure}</strong> chez <strong>{$cabinetNom}</strong> a été annulé.</p>
                        <p>Cordialement,<br><strong>L'équipe Opticare</strong></p>
                    </div>
                    <div class='footer'><p>&copy; 2026 Opticare. Tous droits réservés.</p></div>
                </div>
            </body>
            </html>
        ";
    }

    private function getRdvModifieMessage(string $name, string $cabinetNom, string $date, string $heure, string $ancienneDate, string $ancienneHeure): string
    {
        return "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #3182ce; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; background: #f7fafc; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #718096; }
                    .info { background: #e2e8f0; padding: 15px; border-radius: 5px; margin: 10px 0; }
                    .old { text-decoration: line-through; color: #e53e3e; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'><h1>📅 RDV modifié</h1></div>
                    <div class='content'>
                        <p>Bonjour <strong>{$name}</strong>,</p>
                        <p>Votre RDV a été modifié.</p>
                        <div class='info'>
                            <p><strong>Cabinet :</strong> {$cabinetNom}</p>
                            <p><strong>Ancienne date :</strong> <span class='old'>{$ancienneDate} à {$ancienneHeure}</span></p>
                            <p><strong>Nouvelle date :</strong> {$date} à {$heure}</p>
                        </div>
                        <p>Cordialement,<br><strong>L'équipe Opticare</strong></p>
                    </div>
                    <div class='footer'><p>&copy; 2026 Opticare. Tous droits réservés.</p></div>
                </div>
            </body>
            </html>
        ";
    }

    private function getCommandeTermineeMessage(string $name, int $commandeId, string $cabinetNom): string
    {
        return "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #48bb78; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; background: #f7fafc; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #718096; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'><h1>👓 Commande terminée</h1></div>
                    <div class='content'>
                        <p>Bonjour <strong>{$name}</strong>,</p>
                        <p>Votre commande #<strong>{$commandeId}</strong> est terminée et prête à être récupérée chez <strong>{$cabinetNom}</strong>.</p>
                        <p>Cordialement,<br><strong>L'équipe Opticare</strong></p>
                    </div>
                    <div class='footer'><p>&copy; 2026 Opticare. Tous droits réservés.</p></div>
                </div>
            </body>
            </html>
        ";
    }

    /**
     * Méthode générique d'envoi d'email
     */
    private function sendEmail(string $to, string $name, string $subject, string $htmlContent): void
    {
        try {
            Mail::send([], [], function ($message) use ($to, $name, $subject, $htmlContent) {
                $message->to($to, $name)
                        ->subject($subject)
                        ->html($htmlContent);
            });

            Log::info('Email envoyé avec succès', [
                'to' => $to,
                'subject' => $subject,
                'timestamp' => now()->toDateTimeString()
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur envoi email', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage(),
                'timestamp' => now()->toDateTimeString()
            ]);
            throw $e;
        }
    }
    /**
 * Envoyer un email de nouveau RDV au propriétaire du cabinet
 */
public function sendNouveauRdvCabinet(string $to, string $name, string $patientNom, string $date, string $heure): void
{
    if (empty($to)) {
        Log::warning('Email non envoyé - propriétaire sans email');
        return;
    }

    try {
        $subject = '📅 Nouveau RDV - Opticare';
        $message = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #3182ce; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; background: #f7fafc; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #718096; }
                    .info { background: #e2e8f0; padding: 15px; border-radius: 5px; margin: 10px 0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'><h1>📅 Nouveau RDV</h1></div>
                    <div class='content'>
                        <p>Bonjour <strong>{$name}</strong>,</p>
                        <p>Un nouveau RDV a été pris dans votre cabinet.</p>
                        <div class='info'>
                            <p><strong>Patient :</strong> {$patientNom}</p>
                            <p><strong>Date :</strong> {$date}</p>
                            <p><strong>Heure :</strong> {$heure}</p>
                        </div>
                        <p>Cordialement,<br><strong>L'équipe Opticare</strong></p>
                    </div>
                    <div class='footer'><p>&copy; 2026 Opticare. Tous droits réservés.</p></div>
                </div>
            </body>
            </html>
        ";

        $this->sendEmail($to, $name, $subject, $message);
    } catch (\Exception $e) {
        Log::error('Erreur envoi email nouveau RDV cabinet : ' . $e->getMessage());
    }
}

/**
 * Envoyer un email d'annulation de RDV au propriétaire
 */
public function sendRdvAnnuleCabinet(string $to, string $name, string $patientNom, string $date, string $heure): void
{
    if (empty($to)) {
        Log::warning('Email non envoyé - propriétaire sans email');
        return;
    }

    try {
        $subject = '❌ RDV annulé - Opticare';
        $message = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #e53e3e; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; background: #f7fafc; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #718096; }
                    .info { background: #e2e8f0; padding: 15px; border-radius: 5px; margin: 10px 0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'><h1>❌ RDV annulé</h1></div>
                    <div class='content'>
                        <p>Bonjour <strong>{$name}</strong>,</p>
                        <p>Un RDV a été annulé dans votre cabinet.</p>
                        <div class='info'>
                            <p><strong>Patient :</strong> {$patientNom}</p>
                            <p><strong>Date :</strong> {$date}</p>
                            <p><strong>Heure :</strong> {$heure}</p>
                        </div>
                        <p>Cordialement,<br><strong>L'équipe Opticare</strong></p>
                    </div>
                    <div class='footer'><p>&copy; 2026 Opticare. Tous droits réservés.</p></div>
                </div>
            </body>
            </html>
        ";

        $this->sendEmail($to, $name, $subject, $message);
    } catch (\Exception $e) {
        Log::error('Erreur envoi email annulation RDV cabinet : ' . $e->getMessage());
    }
}

/**
 * Envoyer un email de modification de RDV au propriétaire
 */
public function sendRdvModifieCabinet(string $to, string $name, string $patientNom, string $date, string $heure, string $ancienneDate, string $ancienneHeure): void
{
    if (empty($to)) {
        Log::warning('Email non envoyé - propriétaire sans email');
        return;
    }

    try {
        $subject = '📅 RDV modifié - Opticare';
        $message = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #3182ce; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; background: #f7fafc; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #718096; }
                    .info { background: #e2e8f0; padding: 15px; border-radius: 5px; margin: 10px 0; }
                    .old { text-decoration: line-through; color: #e53e3e; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'><h1>📅 RDV modifié</h1></div>
                    <div class='content'>
                        <p>Bonjour <strong>{$name}</strong>,</p>
                        <p>Un RDV a été modifié dans votre cabinet.</p>
                        <div class='info'>
                            <p><strong>Patient :</strong> {$patientNom}</p>
                            <p><strong>Ancienne date :</strong> <span class='old'>{$ancienneDate} à {$ancienneHeure}</span></p>
                            <p><strong>Nouvelle date :</strong> {$date} à {$heure}</p>
                        </div>
                        <p>Cordialement,<br><strong>L'équipe Opticare</strong></p>
                    </div>
                    <div class='footer'><p>&copy; 2026 Opticare. Tous droits réservés.</p></div>
                </div>
            </body>
            </html>
        ";

        $this->sendEmail($to, $name, $subject, $message);
    } catch (\Exception $e) {
        Log::error('Erreur envoi email modification RDV cabinet : ' . $e->getMessage());
    }
}

/**
 * Envoyer un email de rappel de RDV
 */
public function sendRdvRappel(string $to, string $name, string $cabinetNom, string $cabinetAdresse, string $date, string $heure): void
{
    if (empty($to)) {
        Log::warning('Email non envoyé - destinataire sans email');
        return;
    }

    try {
        $subject = '⏰ Rappel : votre RDV Opticare demain';
        $message = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #d69e2e; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; background: #f7fafc; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #718096; }
                    .info { background: #fefcbf; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #d69e2e; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'><h1>⏰ Rappel de RDV</h1></div>
                    <div class='content'>
                        <p>Bonjour <strong>{$name}</strong>,</p>
                        <p>Nous vous rappelons que vous avez un RDV <strong>demain</strong>.</p>
                        <div class='info'>
                            <p><strong>Cabinet :</strong> {$cabinetNom}</p>
                            <p><strong>Adresse :</strong> {$cabinetAdresse}</p>
                            <p><strong>Date :</strong> {$date}</p>
                            <p><strong>Heure :</strong> {$heure}</p>
                        </div>
                        <p>Merci de vous présenter 10 minutes avant l'heure prévue.</p>
                        <p>Si vous ne pouvez pas venir, merci d'annuler votre RDV depuis l'application.</p>
                        <p>Cordialement,<br><strong>L'équipe Opticare</strong></p>
                    </div>
                    <div class='footer'><p>&copy; 2026 Opticare. Tous droits réservés.</p></div>
                </div>
            </body>
            </html>
        ";

        $this->sendEmail($to, $name, $subject, $message);
    } catch (\Exception $e) {
        Log::error('Erreur envoi email rappel RDV : ' . $e->getMessage());
    }
}
}