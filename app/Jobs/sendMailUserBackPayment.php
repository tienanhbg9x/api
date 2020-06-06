<?php

namespace App\Jobs;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class sendMailUserBackPayment extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected  $request = null;
    protected  $user_id = null;

    public function __construct($request,$user_id)
    {
        $this->request = $request;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $subject = 'Khách hàng '.$this->user_id.' cần hỗ trợ - Hệ thống  sosanhnha.com';
        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
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
            $mail->addAddress('hanhdt@vatgia.com');     // Add a recipient
            $mail->addAddress('hanhdt@vatgia.com');               // Name is optional
            $mail->addReplyTo(config('phpmailer.user_name'), config('app.app_name'));
            $mail->addCC('minhngoc2512@yahoo.com');
            $mail->addBCC('quangdn@vatgia.com');
//            if ($this->address_cc != null) {
//                $mail->addCC($this->address_cc);
//            }
//            $mail->addBCC('bcc@example.com');

            //Attachments
//            $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
//            $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $this->renderBody();
            $mail->AltBody = 'Email send from website: https://sosanhnha.com';
            $mail->send();
        } catch (Exception $e) {
            @file_put_contents(storage_path('logs/mail_log.log'),date('d-m-Y H:i:s').':'.'PHPMAILER(notification to user): ' . $mail->ErrorInfo. "\n",FILE_APPEND);
        }
    }
    protected function renderBody()
    {
        $content_body = '';
        foreach ($this->request as $key=>$value){
            $content_body .='
             <tr>
                <td>'.$key.'</td>
                <td>'.$value.'</td>
            </tr>
            ';
        }
        $body = '
            <div>
                <p>Hệ thống website bất động sản <a href="https://sosanhnha.com">https://sosanhnha.com</a> thông báo khách hàng thoát khỏi hệ thống nạp tiền:</p>
                <p>
                        <table>
                    <tr>
                        <td>Thông số</td>
                        <td>Giá trị</td>
                    </tr>
                  '.$content_body.'
                </table>
                </p>
                <br>
               
            </div>
            <style>
                table {
                    font-family: arial, sans-serif;
                    border-collapse: collapse;
                    width: 100%;
                }
            
                td, th {
                    border: 1px solid #dddddd;
                    text-align: left;
                    padding: 8px;
                }
            
                tr:nth-child(even) {
                    background-color: #dddddd;
                }
            </style>
        ';
        return $body;
    }
}
