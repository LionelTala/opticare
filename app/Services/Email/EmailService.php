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
        try {
            $subject = 'Votre cabinet Opticare est validé !';
            $message = "
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
                        <div class='header'>
                            <h1>🎉 Félicitations !</h1>
                        </div>
                        <div class='content'>
                            <p>Bonjour <strong>{$user->prenom} {$user->nom}</strong>,</p>
                            <p>Nous avons le plaisir de vous informer que votre cabinet <strong>\"{$cabinetNom}\"</strong> a été <strong>validé</strong> par notre équipe.</p>
                            <p>Vous pouvez maintenant vous connecter à votre espace Opticare et commencer à gérer votre cabinet.</p>
                            <p style='text-align: center;'>
                                <a href='https://opticare.com/login' class='btn'>Se connecter</a>
                            </p>
                            <p>Nous vous souhaitons une excellente expérience sur Opticare !</p>
                            <p>Cordialement,<br><strong>L'équipe Opticare</strong></p>
                        </div>
                        <div class='footer'>
                            <p>Cet email est envoyé automatiquement, merci de ne pas y répondre.</p>
                            <p>&copy; 2026 Opticare. Tous droits réservés.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

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
        try {
            $subject = 'Statut de votre demande Opticare';
            $message = "
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
                        <div class='header'>
                            <h1>❌ Demande non validée</h1>
                        </div>
                        <div class='content'>
                            <p>Bonjour <strong>{$user->prenom} {$user->nom}</strong>,</p>
                            <p>Nous avons examiné votre demande pour le cabinet <strong>\"{$cabinetNom}\"</strong>.</p>
                            <p>Malheureusement, nous ne pouvons pas la valider pour le moment.</p>
                            <div class='motif'>
                                <p><strong>Motif du refus :</strong></p>
                                <p>{$motif}</p>
                            </div>
                            <p>N'hésitez pas à nous contacter pour plus d'informations ou pour déposer une nouvelle demande après correction.</p>
                            <p>Cordialement,<br><strong>L'équipe Opticare</strong></p>
                        </div>
                        <div class='footer'>
                            <p>Cet email est envoyé automatiquement, merci de ne pas y répondre.</p>
                            <p>&copy; 2026 Opticare. Tous droits réservés.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            $this->sendEmail($user->email, $user->prenom, $subject, $message);
        } catch (\Exception $e) {
            Log::error('Erreur envoi email refus : ' . $e->getMessage());
        }
    }

    /**
     * Méthode générique d'envoi d'email
     */
    private function sendEmail(string $to, string $name, string $subject, string $htmlContent): void
    {
        try {
            $data = [
                'name' => $name,
                'subject' => $subject,
                'content' => $htmlContent,
            ];

            Mail::send([], [], function ($message) use ($to, $name, $subject, $htmlContent) {
                $message->to($to, $name)
                        ->subject($subject)
                        ->html($htmlContent);
            });

            // Log de succès
            Log::info('Email envoyé avec succès', [
                'to' => $to,
                'subject' => $subject,
                'timestamp' => now()->toDateTimeString()
            ]);

        } catch (\Exception $e) {
            // Log d'erreur détaillé
            Log::error('Erreur envoi email', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->toDateTimeString()
            ]);
            throw $e;
        }
    }
}