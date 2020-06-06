<?php

namespace App\Jobs;
use App\Models\UserNotify;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class sendMailNotifyFromSystemToUser extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $data = null;
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $subject = $this->data['subject'];
        $mail = new PHPMailer(true);
        $user_notify = UserNotify::find($this->data['id']);
        try {
            //Server settings
            $mail->CharSet = 'UTF-8';
            $mail->SMTPDebug = 1;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = config('phpmailer.host');  // Specify main and backup SMTP servers
            $mail->SMTPAuth = config('phpmailer.smtp_auth');                               // Enable SMTP authentication
            $mail->Username = config('phpmailer.user_name');                 // SMTP username
            $mail->Password = config('phpmailer.password');                           // SMTP password
            $mail->SMTPSecure = config('phpmailer.smtp_secure');                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = config('phpmailer.port');                                    // TCP port to connect to

            //Recipients
            $mail->setFrom(config('phpmailer.user_name'), config('app.app_name'));
            $mail->addAddress(trim($this->data['user_email']),$this->data['user_name']);     // Add a recipient
            $mail->addReplyTo(config('phpmailer.user_name'), config('app.app_name'));
            if(isset($this->data['email_cc'])){
                foreach ($this->data['email_cc'] as $email){
                    $mail->addCC(trim($email));
                }
            }
            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $this->data['body'];
            $mail->AltBody = 'Email send from website: https://sosanhnha.com';
            if($mail->send()===false){
                $user_notify->usn_status = 3;
            }else{
                $user_notify->usn_status = 1;
            }
            $user_notify->save();
        } catch (Exception $e) {
            $user_notify->usn_status = 3;
            $user_notify->save();
            @file_put_contents(storage_path('logs/mail_log.log'),date('d-m-Y H:i:s').':'.'PHPMAILER(notification to user): ' . $mail->ErrorInfo. "\n",FILE_APPEND);
        }
    }
}
