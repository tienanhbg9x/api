<?php

namespace App\Jobs;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class sendMailUserContact extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $data=null;
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
        try {
            $subject = 'Khách hàng quan tâm đến tin đăng('.$this->data['cla_title'].') của bạn - Website bất động sản https://www.sosanhnha.com';
            $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
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
            $mail->addAddress(trim($this->data['user_email']), $this->data['user_name']);     // Add a recipient
            $mail->addReplyTo(config('phpmailer.user_name'), config('app.app_name'));
            //CC mail
            if(isset($this->data['user_mail_cc'])){
                $mail->addCC(trim($this->data['user_mail_cc']));
            }

//            $mail->addBCC(config('app.mail_notification_admin'));
//            $mail->addBCC('minhngoc2512@yahoo.com');

            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $this->renderBody();
            $mail->AltBody = 'Email send from website: https://www.sosanhnha.com';
            $mail->send();
        } catch (Exception $e) {
            @file_put_contents(storage_path('logs/mail_log.log'), date('d-m-Y H:i:s') . ':' . 'PHPMAILER(notification money update vip): ' . $mail->ErrorInfo . "\n", FILE_APPEND);
        }

    }

    public function renderBody()
    {
        $body_info = '';
        foreach ($this->data['user_contact'] as $key=>$value){
            $body_info .='
                   <tr style="display:table-row;vertical-align:inherit">
                        <td style="border:1px solid #aaaaaa;padding:8px 15px"> <b>'.$key.' </b> </td>
                        <td style="border:1px solid #aaaaaa;padding:8px 75px" colspan="3"> ' . $value . '  </td>

                    </tr>
            ';
        }
        $body = '
                <div style="margin: 0">
                        <div style="display:table;font-size:12px;line-height:18px;font-family:arial">

                            <div style="display:table;text-align:center;width:650px;margin-top:5px">
                                <b style="color:#21b353;text-align:center;font-size:17px"> WEBSITE BẤT ĐỘNG SẢN SOSANHNHA.COM </b>
                            </div>
                            <div style="display:table;color:#333333;padding-left:8px;font-family:arial">
                                <p style="font-size:13px">
                                    Kính gửi: <span>  ' . $this->data['user_name'] . '</span> <br>
                                    Hệ thống website: <a href="'.env('DOMAIN_WEBSITE').'" target="_blank">www.sosanhnha.com</a> thông báo:<br>
                                    Có khách hàng quan tâm đến tin đăng của bạn:  <a target="_blank" href="' .env('DOMAIN_WEBSITE').'/'. $this->data['cla_rewrite'] . '?utm_source=email&utm_medium=user_contact&utm_campaign=user_contact_web&utm_term=contact">' . $this->data['cla_title'] . '</a>.<br>
                                    <strong>Link tin đăng:</strong> <a href="' .env('DOMAIN_WEBSITE').'/'. $this->data['cla_rewrite'] . '?utm_source=email&utm_medium=user_contact&utm_campaign=user_contact_web&utm_term=contact">' .env('DOMAIN_WEBSITE').'/'. $this->data['cla_rewrite'] . '</a>.
                                </p>
                                <p>
                                    Thông tin liên hệ khách hàng:
                                </p>
                            </div>
                            <div style="display:table;font-size:13px;padding-left:8px;padding-bottom:2px">
                                <table style="border-spacing:2px;text-indent:0;border-collapse:collapse">
                                    <tbody>'.$body_info.'</tbody>
                                </table>
                            </div>
                            <div style="display:table;text-align:center;width:650px;padding-top:5px">
                                <p style="font-size:14px;margin-right:8px;color:#4e4e4e">
                                    Thông tin được gửi từ hệ thống website bất động sản <a href="'.env('DOMAIN_WEBSITE').'" target="_blank">www.sosanhnha.com</a>.
                                </p>
                            </div>
                            <div style="display:table;color:#333333;text-align:left;width:650px;padding-top:5px;padding-left:20px;font-size:13px;margin-bottom:7px">
                                   <i>Mọi thắc mắc vui lòng trả lời mail '.config('phpmailer.user_name').' - ngocnm@vatgia.com.
                                     </i>
                                <p>
                                    <b>Trân trọng thông tin đến bạn.</b>
                                </p>

                            </div>

                            <div style="display:table;width:650px;height:auto;margin:0px auto;border-top:1px solid #aaaaaa;color:#aaaaaa;line-height:16px;font-size:12px">

                                <div style="display:table;text-align:center">
                                    <p style="font-weight:bold;font-size:13px;margin-right:8px;color:#4e4e4e"> Email: ' . config('phpmailer.user_name') . '<br>
                                        <a style="color:#006c9a;text-decoration:none" href="https://www.sosanhnha.com/" target="_blank" >www.sosanhnha.com</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
<!--<style>-->

        ';
        return $body;
    }
}
