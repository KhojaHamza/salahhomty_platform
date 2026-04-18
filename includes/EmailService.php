<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/mail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {

    private $cancellable = ['NEW_CLAIM', 'NEW_SERVICE'];

    private $subjects = [
        'NEW_CLAIM'       => 'Réclamation enregistrée - Salahhomty',
        'NEW_SERVICE'     => 'Demande de service enregistrée - Salahhomty',
        'CONFIRM_CLAIM'   => 'Réclamation confirmée - Salahhomty',
        'CONFIRM_SERVICE' => 'Demande de service confirmée - Salahhomty',
        'CANCEL_SERVICE'  => 'Annulation confirmée - Salahhomty',
    ];

    private $templates = [
        'NEW_CLAIM'       => 'new_claim',
        'NEW_SERVICE'     => 'new_service',
        'CONFIRM_CLAIM'   => 'confirm_claim',
        'CONFIRM_SERVICE' => 'confirm_service',
        'CANCEL_SERVICE'  => 'cancel_service',
    ];

    public function send(string $type, array $user, array $object): bool {

        // Préparer les données pour le template
        $data = [
            'user'   => $user,
            'object' => $object,
        ];

        // Générer le lien d'annulation si nécessaire
        if (in_array($type, $this->cancellable)) {
            $token = $this->generateToken($user['id'], $object['id'], $type);
            $data['cancellation_url']      = APP_URL . '/actions/cancel_request.php?token=' . $token;
            $data['cancellation_deadline'] = date('d/m/Y à H:i', strtotime('+2 hours'));
        }

        // Générer le HTML depuis le template
        $html = $this->renderTemplate($this->templates[$type], $data);

        // Envoyer l'email
        return $this->sendMail($user['email'], $this->subjects[$type], $html);
    }

    private function renderTemplate(string $name, array $data): string {
        extract($data);
        ob_start();
        include __DIR__ . '/../templates/emails/' . $name . '.php';
        return ob_get_clean();
    }

    private function sendMail(string $to, string $subject, string $html): bool {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = MAIL_ENCRYPTION;
            $mail->Port       = MAIL_PORT;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(MAIL_USERNAME, MAIL_FROM_NAME);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $html;

            $mail->send();
            $this->logEmail($to, $subject, true);
            return true;

        } catch (Exception $e) {
            $this->logEmail($to, $subject, false, $mail->ErrorInfo);
            return false;
        }
    }

    
    private function generateToken(int $userId, int $objectId, string $type): string {
      require_once __DIR__ . '/../config/database.php';
      global $pdo; // ← ajouter cette ligne

      $token   = bin2hex(random_bytes(32));
      $expires = date('Y-m-d H:i:s', strtotime('+2 hours'));

      $stmt = $pdo->prepare("
        INSERT INTO cancellation_tokens 
        (token, user_id, object_id, object_type, expires_at)
        VALUES (?, ?, ?, ?, ?)
    ");
      $stmt->execute([$token, $userId, $objectId, $type, $expires]);

      return $token;
}

    private function logEmail(string $to, string $subject, bool $success, string $error = ''): void {
        $status  = $success ? 'SUCCESS' : 'FAILED';
        $message = "[EMAIL $status] To: $to | Subject: $subject";
        if (!$success) $message .= " | Error: $error";
        $message .= " | " . date('Y-m-d H:i:s');
        error_log($message);
    }
}
?>