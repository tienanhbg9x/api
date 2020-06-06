<?php

namespace App\Jobs;

use App\Models\User;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class sendMailNotification extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $classified = null;
    protected $money = null;
    protected $ush = null;
    protected $subject = null;
    protected $user = null;

    public function __construct($ush, $money, $classified)
    {
        $this->classified = $classified;
        $this->ush = $ush;
        $this->money = $money;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::find($this->classified->cla_use_id);
        $this->user = $user;
        $this->subject = 'Làm mới tin có ID:'.$this->classified->cla_id.' thành công - Bất động sản https://www.sosanhnha.com';
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
//            if ($this->address_cc != null) {
//                $mail->addCC($this->address_cc);
//            }
//            $mail->addBCC('bcc@example.com');

            //Attachments
//            $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
//            $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $this->subject;
            $mail->Body = $this->renderBody();
            $mail->AltBody = 'Email send from website: https://sosanhnha.com';
            $mail->send();
        } catch (Exception $e) {
            @file_put_contents(storage_path('logs/mail_log.log'),date('d-m-Y H:i:s').':'.'PHPMAILER(notification to user): ' . $mail->ErrorInfo. "\n",FILE_APPEND);
        }
    }


    protected function renderBody()
    {


        $body = '
                <div style="margin: 0">
                        <div style="display:table;font-size:12px;line-height:18px;font-family:arial">

                            <div style="display:table;text-align:center;width:650px;margin-top:5px">
                                <b style="color:#21b353;text-align:center;font-size:17px"> WEBSITE BẤT ĐỘNG SẢN SOSANHNHA.COM </b>
                            </div>
                            <div style="display:table;color:#333333;padding-left:8px;font-family:arial">
                                <p style="font-size:13px">
                                    Kính gửi: <span>  ' . $this->user->use_fullname . '</span> <br>
                                    Bạn vừa làm mới tin <a target="_blank" href="' .env('DOMAIN_WEBSITE').'/'. $this->classified->cla_rewrite . '">' . $this->classified->cla_title . '</a> bởi tài khoản <b>' . $this->user->use_email . '</b>.
                                </p>
                                <p>
                                    Thông tin chi tiết giao dịch :
                                </p>
                            </div>
                            <div style="display:table;font-size:13px;padding-left:8px;padding-bottom:2px">
                                <table style="border-spacing:2px;text-indent:0;border-collapse:collapse">
                                    <tbody>
                                    <tr style="display:table-row;vertical-align:inherit">
                                        <td style="border:1px solid #aaaaaa;padding:8px 15px"> <b>Mã giao dịch </b> </td>
                                        <td style="border:1px solid #aaaaaa;padding:8px 75px" colspan="3"> ' . $this->ush->ush_order_id . '  </td>

                                    </tr>
                                          <tr style="display:table-row;vertical-align:inherit">
                                        <td style="border:1px solid #aaaaaa;padding:8px 15px"> <b>Tài khoản giao dịch </b> </td>
                                        <td style="border:1px solid #aaaaaa;padding:8px 75px" colspan="3"> ' . $this->user->use_email . '  </td>

                                    </tr>
                                            <tr style="display:table-row;vertical-align:inherit">
                                        <td style="border:1px solid #aaaaaa;padding:8px 15px"> <b>Nội dung giao dịch </b> </td>
                                        <td style="border:1px solid #aaaaaa;padding:8px 75px" colspan="3"> ' . $this->ush->ush_message . '  </td>
                                    </tr>
                                             <tr style="display:table-row;vertical-align:inherit">
                                        <td style="border:1px solid #aaaaaa;padding:8px 15px"> <b>Số tiền giao dịch </b> </td>
                                        <td style="border:1px solid #aaaaaa;padding:8px 75px" colspan="3"> ' .($this->ush->ush_count<0? number_format(($this->ush->ush_count/-1), 0, '', ','):$this->ush->ush_count) . ' VND </td>
                                    </tr>
                                           <tr style="display:table-row;vertical-align:inherit">
                                        <td style="border:1px solid #aaaaaa;padding:8px 15px"> <b>Số dư tài khoản thay đổi </b> </td>
                                        <td style="border:1px solid #aaaaaa;padding:8px 75px" colspan="3"> ' . number_format((int)$this->ush->ush_count, 0, '', ',') . ' VND </td>
                                    </tr>
                                                 <tr style="display:table-row;vertical-align:inherit">
                                        <td style="border:1px solid #aaaaaa;padding:8px 15px"> <b>Số dư tài khoản </b> </td>
                                        <td style="border:1px solid #aaaaaa;padding:8px 75px" colspan="3"> ' . number_format((int)$this->money->mon_count, 0, '', ',') . ' VND  </td>
                                    </tr>
                                              <tr style="display:table-row;vertical-align:inherit">
                                        <td style="border:1px solid #aaaaaa;padding:8px 15px"> <b>Thời gian giao dịch </b> </td>
                                        <td style="border:1px solid #aaaaaa;padding:8px 75px" colspan="3"> ' . $this->ush->ush_created_at . '  </td>
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
                                    <p style="font-weight:bold;font-size:13px;margin-right:8px;color:#4e4e4e"> Email: ' . config('phpmailer.user_name') . ' - hanhdt@vatgia.com<br>
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
