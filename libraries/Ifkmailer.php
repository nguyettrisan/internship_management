<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Ifkmailer
{
    protected $CI;
    protected $mail;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->mail = new PHPMailer(true);

        // Load SMTP Config
        $smtp_host   = get_option('ifk_smtp_host');
        $smtp_port   = get_option('ifk_smtp_port');
        $smtp_user   = get_option('ifk_smtp_user');
        $smtp_pass   = get_option('ifk_smtp_pass');
        $smtp_secure = get_option('ifk_smtp_secure');
        $sender_name = get_option('ifk_sender_name');
        $sender_mail = get_option('ifk_sender_email');

        if (!$smtp_host || !$smtp_user) {
            log_message('error', 'IFKMAILER ERROR: Missing SMTP configuration.');
            return;
        }

        try {
            $this->mail->isSMTP();
            $this->mail->SMTPAuth   = true;
            $this->mail->Host       = $smtp_host;
            $this->mail->Username   = $smtp_user;
            $this->mail->Password   = $smtp_pass;
            $this->mail->Port       = $smtp_port ?: 587;

            // Secure
            if ($smtp_secure === 'ssl') {
                $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($smtp_secure === 'tls') {
                $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $this->mail->CharSet  = 'UTF-8';
            $this->mail->Encoding = 'base64';
            $this->mail->isHTML(true);

            // Sender
            $this->mail->setFrom(
                $sender_mail ?: 'noreply@ifkgroup.net',
                $sender_name ?: 'IFK GROUP'
            );

        } catch (Exception $e) {
            log_message('error', 'IFKMAILER INIT ERROR: ' . $e->getMessage());
        }
    }

    /* ==========================================================
       SEND EMAIL + LOGGING
    ========================================================== */
    public function send($to, $subject, $body, $attachments = [])
    {
        /** ─── CREATE LOG ROW FIRST ───────────────────────────── */
        $this->CI->db->insert(db_prefix() . '_ifk_email_logs', [
            'email_to'  => $to,
            'subject'   => $subject,
            'body'      => $body,
            'status'    => 'processing',
            'date_sent' => date('Y-m-d H:i:s')
        ]);

        $log_id = $this->CI->db->insert_id();

        try {
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();

            $this->mail->addAddress($to);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $this->apply_template($body);

            foreach ($attachments as $file) {
                if (file_exists($file)) {
                    $this->mail->addAttachment($file);
                }
            }

            $this->mail->send();

            /** ─── SUCCESS UPDATE ─────────────────────────────── */
            $this->CI->db->where('id', $log_id)
                ->update(db_prefix() . '_ifk_email_logs', [
                    'status' => 'success',
                    'error_message' => null
                ]);

            return true;

        } catch (Exception $e) {

            /** ─── FAILED UPDATE ──────────────────────────────── */
            $this->CI->db->where('id', $log_id)
                ->update(db_prefix() . '_ifk_email_logs', [
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);

            log_message('error', 'IFKMAILER SEND ERROR: ' . $e->getMessage());

            return $e->getMessage();
        }
    }

    /* ==========================================================
       IFK GLOBAL MAIL TEMPLATE
    ========================================================== */
    private function apply_template($content)
    {
        return "
        <style>
            @media only screen and (max-width: 600px) {
                .ifk-wrapper { padding: 15px !important; }
                .ifk-box { padding: 20px !important; border-radius: 0 !important; }
            }
        </style>

        <div class='ifk-wrapper' style=\"background:#f2f4f8;padding:28px;\">
            <div class='ifk-box' style=\"
                max-width:680px;margin:auto;background:#ffffff;
                padding:30px 35px;border-radius:16px;
                box-shadow:0 5px 25px rgba(0,0,0,0.08);
                font-family:-apple-system,Roboto,Helvetica,Arial;
                color:#333;line-height:1.6;\">

                <div style='text-align:center;margin-bottom:25px;'>
                    <img src='https://translationifk.com/wp-content/uploads/2020/02/logo_ifk-1.svg'
                         style='max-width:180px;'>
                </div>

                <div style='text-align:center;color:#0b4da2;font-size:22px;font-weight:700;margin-bottom:6px;'>
                    IFK Education & Translation
                </div>

                <div style='text-align:center;color:#555;font-size:14px;margin-bottom:25px;'>
                    Gắn kết con người, sáng tạo tương lai
                </div>

                <div style='font-size:16px;'>
                    {$content}
                </div>

                <hr style='margin:25px 0;border-color:#e5e5e5;'>

                <p style='text-align:center;font-size:12px;color:#888;margin-top:10px;'>
                    Email được gửi tự động từ IFK Education & Translation – vui lòng không phản hồi email này.
                </p>
            </div>
        </div>";
    }
}