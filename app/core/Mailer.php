<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    public static function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody
    ): bool {
        $mail = new PHPMailer(true);
        
        

        try {

            $mail->isSMTP();

            $mail->Host =
                SMTP_HOST;

            $mail->SMTPAuth =
                true;

            $mail->Username =
                SMTP_USERNAME;

            $mail->Password =
                SMTP_PASSWORD;

            $mail->SMTPSecure =
                PHPMailer::ENCRYPTION_STARTTLS;

            $mail->Port =
                SMTP_PORT;


            $fromEmail =
                defined('SMTP_FROM_EMAIL') &&
                trim((string) SMTP_FROM_EMAIL) !== ''
                    ? trim((string) SMTP_FROM_EMAIL)
                    : trim((string) SMTP_USERNAME);

            $fromName =
                defined('SMTP_FROM_NAME') &&
                trim((string) SMTP_FROM_NAME) !== ''
                    ? trim((string) SMTP_FROM_NAME)
                    : 'RedAlien';


            $mail->setFrom(
                $fromEmail,
                $fromName
            );


            $mail->addAddress(
                $toEmail,
                $toName
            );


            $mail->isHTML(true);

            $mail->Subject =
                $subject;

            $mail->Body =
                $htmlBody;

            $mail->AltBody =
                strip_tags(
                    $htmlBody
                );


            $mail->send();

            return true;

        } catch (Exception $exception) {

            error_log(
                'RedAlien Mail Error: ' .
                $mail->ErrorInfo
            );

            error_log(
                'RedAlien Mail Exception: ' .
                $exception->getMessage()
            );


            return false;
        }
    }
}