<?php

namespace App\Jobs;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class sendMailNotificationToAdmin extends Job
{


    protected $request = null;
    protected $ush = null;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request,$ush)
    {
        $this->request = $request;
        $this->ush = $ush;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $subject = 'Thông báo từ hệ thống quản lí tiền - Bất động sản https://www.sosanhnha.com';
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
            $mail->addAddress(config('app.mail_notification_admin'));     // Add a recipient
            $mail->addAddress(config('app.mail_notification_admin'));               // Name is optional
            $mail->addReplyTo(config('phpmailer.user_name'), config('app.app_name'));
            $mail->addCC('minhngoc2512@yahoo.com');
//            $mail->addBCC('quangdn@vatgia.com');
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
//            $this->sendNotificationToSlack();
        } catch (Exception $e) {
            @file_put_contents(storage_path('logs/mail_log.log'),date('d-m-Y H:i:s').':'.'PHPMAILER(notification to user): ' . $mail->ErrorInfo. "\n",FILE_APPEND);
        }
    }

//    protected function sendNotificationToSlack(){
//        $message = "Nạp tiền vào hệ thống sosanhnha.com \n";
//        $customer_address = isset($this->request['customer_address'])?$this->request['customer_address']:'';
//        $customer_name =  isset($this->request['customer_name'])?$this->request['customer_name']:'';
//        $customer_phone = isset($this->request['customer_phone'])?$this->request['customer_phone']:'';
//        $total_amount = isset($this->request['total_amount'])?$this->request['total_amount']:'';
//
//        if(!empty($customer_name)) $message .= "Họ tên: $customer_name \n";
//        if(!empty($customer_address)) $message .= "Địa chỉ: $customer_address \n";
//        if(!empty($customer_phone)) $message .= "Điện thoại: $customer_phone \n";
//        if(!empty($total_amount)) $message .= "Số tiền: $total_amount \n";
//
//
//        $client = new Client('https://hooks.slack.com/services/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');
//        $client->to('#sosanhnha')->send($message);
//    }

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
        $status_compare = $this->ush?'Thành công':'Khớp không thành công';
        $body = '
            <div>
                <p>Hệ thống website bất động sản <a href="https://sosanhnha.com">https://sosanhnha.com</a> thông báo nhận được phản hồi từ hệ thống thanh toán Bảo Kim:</p>
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
                <b style="color: blue">Trạng thái khớp với lịch sử giao dịch trên hệ thống: <span style="color: red">'.$status_compare.'</span></b>
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
