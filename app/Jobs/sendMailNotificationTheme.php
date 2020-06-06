<?php

namespace App\Jobs;
use App\Models\User;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class sendMailNotificationTheme extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $user = null;
    protected $theme = null;
    protected $ush = null;
    protected $money= null;
    protected $subject ='';

    public function __construct($theme, $ush,$money)
    {
        $this->theme = $theme;
        $this->ush = $ush;
        $this->money = $money;
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::find($this->theme->uth_user_id);
        $this->user = $user;
        $status_body = '';
        $this->subject = 'Mua giao diện với mã ' .     $this->theme->uth_theme_id . ' thành công - Bất động sản https://www.sosanhnha.com';
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
            $user_email = trim(strpos($user->use_email,"@facebook")===false?$user->use_email:$user->use_email_payment);
            $mail->addAddress($user_email, $user->use_name);     // Add a recipient
            $mail->addReplyTo(config('phpmailer.user_name'), config('app.app_name'));
            //CC mail to admin
            if($user->use_email_payment){
                $mail->addCC(trim($user->use_email_payment));
            }
            $mail->addBCC(config('app.mail_notification_admin'));

            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $this->subject;
            $mail->Body = $this->renderBody();
            $mail->AltBody = 'Email send from website: https://sosanhnha.com';
            $mail->send();
        } catch (Exception $e) {
            @file_put_contents(storage_path('logs/mail_log.log'), date('d-m-Y H:i:s') . ':' . 'PHPMAILER(notification to user): ' . $mail->ErrorInfo . "\n", FILE_APPEND);
        }
    }

    protected function renderBody()
    {
        $money = $this->money!=null?$this->money->mon_count:0;

        $body='
        
        <div style="margin: 0">
    <div style="display:table;font-size:12px;line-height:18px;font-family:arial">

        <div style="display:table;text-align:center;width:650px;margin-top:5px">
            <b style="color:#21b353;text-align:center;font-size:17px"> WEBSITE BẤT ĐỘNG SẢN SOSANHNHA.COM </b>
        </div>
        <div style="display:table;color:#333333;padding-left:8px;font-family:arial">
            <p style="font-size:13px">
                Kính gửi: <span>  '.$this->user->use_loginname.'</span> <br>
                Quý khách đã thực hiện giao dịch mua giao diện <a target="_blank" href="'.env("SLUG_USER_THEME").$this->theme->uth_rewrite.' ">'.$this->theme->uth_name.'</a> với mã giao dịch <span style="color: red">'.$this->ush->ush_order_id.' </span> trên website <a href="https://sosanhnha.com" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://sosanhnha.com&amp;source=gmail&amp;ust=1544782641526000&amp;usg=AFQjCNFqbPaPMh3gQApKbbYcXTDrm1ZCUQ">https://sosanhnha.com</a> .<br>
                Thông tin giao dịch:<br>
            </p>
        </div>
        <div style="display:table;font-size:13px;padding-left:8px;padding-bottom:2px">
            <table style="border-spacing:2px;text-indent:0;border-collapse:collapse">
                <tbody>
                <tr style="display:table-row;vertical-align:inherit">
                    <td style="border:1px solid #aaaaaa;padding:8px 15px"> <b> Mã giao dịch</b> </td>
                    <td style="border:1px solid #aaaaaa;padding:8px 75px" colspan="3">  '.$this->ush->ush_order_id.' </td>
                </tr>
                 <tr style="display:table-row;vertical-align:inherit">
                    <td style="border:1px solid #aaaaaa;padding:8px 15px"> <b>Tài khoản thay đổi </b> </td>
                    <td style="border:1px solid #aaaaaa;padding:8px 75px" colspan="3">'.number_format($this->ush->ush_count,0,",",".").' VND</td>
                </tr>
                <tr style="display:table-row;vertical-align:inherit">
                    <td style="border:1px solid #aaaaaa;padding:8px 15px"> <b>Số dư tài khoản </b> </td>
                    <td style="border:1px solid #aaaaaa;padding:8px 75px" colspan="3">'.number_format($money,0,",",".").' VND</td>
                </tr>
                <tr style="display:table-row;vertical-align:inherit">
                    <td style="border:1px solid #aaaaaa;padding:8px 15px"> <b>Nội dung giao dịch</b> </td>
                    <td colspan="3" style="border:1px solid #aaaaaa;padding:8px 75px"> Mua template sosanhnha.com . </td>
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
                <b>Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi.</b>
            </p>

        </div>

        <div style="display:table;width:650px;height:auto;margin:0px auto;border-top:1px solid #aaaaaa;color:#aaaaaa;line-height:16px;font-size:12px">

            <div style="display:table;text-align:center">
                <p style="font-weight:bold;font-size:13px;margin-right:8px;color:#4e4e4e"> Email: '.config('phpmailer.user_name').'  - ngocnm@vatgia.com . Phone: 0915356965<br>
                    <a style="color:#006c9a;text-decoration:none" href="https://www.sosanhnha.com/" target="_blank" >www.sosanhnha.com</a>
                </p>
            </div>
        </div>
    </div>
</div>
<!--<style>-->
        
        ';
        return $body;
    }
}
