<?php

namespace App\Jobs;

use App\Models\User;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use App\Models\UserTheme;

class sendMailToUserWhenCustomerRegister extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $uth_id = null;
    protected $user_id = null;
    protected $customer = null;

    public function __construct($user_id,$uth_id, $customer)
    {
        $this->user_id = $user_id;
        $this->uth_id = $uth_id;
        $this->customer = $customer;
        //
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::find($this->user_id);
        $user_theme = UserTheme::find($this->uth_id);
        $this->user = $user;
        $subject = 'Khách hàng đăng kí nhận tin về dự án "'.$user_theme->uth_name.' " - Bất động sản https://www.sosanhnha.com';
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
            $mail->addAddress(trim($user->use_email), $user->use_name);     // Add a recipient
            $mail->addReplyTo(config('phpmailer.user_name'), config('app.app_name'));
            if($user->use_email_payment){
                $mail->addCC(trim($user->use_email_payment));
            }
            //CC mail to admin
//            $mail->addCC(config('app.mail_notification_admin'));

            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $this->renderBody($user,$user_theme);
            $mail->AltBody = 'Email send from website: https://sosanhnha.com';
            $mail->send();
        } catch (Exception $e) {
            @file_put_contents(storage_path('logs/mail_log.log'), date('d-m-Y H:i:s') . ':' . 'PHPMAILER(notification to user): ' . $mail->ErrorInfo . "\n", FILE_APPEND);
        }
    }

    protected function renderBody($user,$user_theme)
    {
        $info_custom = json_decode($this->customer->ucm_info);
        if($info_custom!=null&&isset($info_custom[0])){
            $info_custom = $info_custom[0]->description;
        }
        $body='
                <div style="margin: 0">
                        <div style="display:table;font-size:12px;line-height:18px;font-family:arial">
                
                            <div style="display:table;text-align:center;width:650px;margin-top:5px">
                                <b style="color:#21b353;text-align:center;font-size:17px"> WEBSITE BẤT ĐỘNG SẢN SOSANHNHA.COM </b>
                            </div>
                            <div style="display:table;color:#333333;padding-left:8px;font-family:arial">
                                <p style="font-size:13px">
                                    Kính gửi: <span>  '.$user->use_fullname.'</span> <br>
                                    Người dùng có tên <b style=" text-transform: uppercase;"> '.$this->customer->ucm_name.'</b> quan tâm tới dự án <a target="_blank" style="text-transform: capitalize" href="'.env('SLUG_USER_THEME').$user_theme->uth_rewrite.'"> '.$user_theme->uth_name.'</a> của bạn.
                                </p>
                                <p>
                                    Thông tin khách hàng chi tiết như sau:
                                </p>
                            </div>
                            <div style="display:table;font-size:13px;padding-left:8px;padding-bottom:2px">
                                <table style="border-spacing:2px;text-indent:0;border-collapse:collapse">
                                    <tbody>
                                    <tr style="display:table-row;vertical-align:inherit">
                                        <td style="border:1px solid #aaaaaa;padding:8px 15px"> <b>Họ tên </b> </td>
                                        <td style="border:1px solid #aaaaaa;padding:8px 75px" colspan="3"> '.$this->customer->ucm_name.'  </td>
                                    </tr>
                                    <tr style="display:table-row;vertical-align:inherit">
                                        <td style="border:1px solid #aaaaaa;padding:8px 15px"> <b> Số điện thoại </b> </td>
                                        <td style="border:1px solid #aaaaaa;padding:8px 75px" colspan="3">  '.$this->customer->ucm_phone.'</td>
                                    </tr>
                                    <tr style="display:table-row;vertical-align:inherit">
                                        <td style="border:1px solid #aaaaaa;padding:8px 15px"> <b>Email:</b> </td>
                                        <td colspan="3" style="border:1px solid #aaaaaa;padding:8px 75px">  '.$this->customer->ucm_email.' </td>
                                    </tr>
                                    <tr style="display:table-row;vertical-align:inherit">
                                        <td style="border:1px solid #aaaaaa;padding:8px 15px"> <b>Địa chỉ:</b> </td>
                                        <td colspan="3" style="border:1px solid #aaaaaa;padding:8px 75px"> '.$this->customer->ucm_address.' </td>
                                    </tr>
                                    <tr style="display:table-row;vertical-align:inherit">
                                        <td style="border:1px solid #aaaaaa;padding:8px 15px"> <b>Thông tin bổ xung :</b> </td>
                                        <td colspan="3" style="border:1px solid #aaaaaa;padding:8px 75px"> '.$info_custom.' </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div style="display:table;text-align:center;width:650px;padding-top:5px">
                                <p style="font-size:14px;margin-right:8px;color:#4e4e4e">
                                    Cám ơn quý khách đã sử dụng dịch vụ của Sosanhnha.com!
                                </p>
                            </div>
                            <div style="display:table;color:#333333;text-align:left;width:650px;padding-top:5px;padding-left:20px;font-size:13px;margin-bottom:7px">
                                       <i>Mọi thắc mắc trong quá trình giao dịch bạn vui lòng trả lời mail '.config('phpmailer.user_name').' - ngocnm@vatgia.com hoặc liên hệ với quản trị trang soanhnha.com: <a href="tel: +84915356965">0915356965</a>.
                                     </i>
                                <p>
                                    <b>Trân trọng thông tin đến bạn.</b>
                                </p>
                
                            </div>
                
                            <div style="display:table;width:650px;height:auto;margin:0px auto;border-top:1px solid #aaaaaa;color:#aaaaaa;line-height:16px;font-size:12px">
                
                                <div style="display:table;text-align:center">
                                    <p style="font-weight:bold;font-size:13px;margin-right:8px;color:#4e4e4e"> Email: '.config('phpmailer.user_name').' - ngocnm@vatgia.com . Phone: 0915356965<br>
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
