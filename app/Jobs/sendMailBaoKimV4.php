<?php

namespace App\Jobs;

use App\Models\Money;
use App\Models\User;
use App\Models\UserSpendHistory;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class sendMailBaoKimV4 extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $subject = '';
    protected $body = '';
    protected $address_send = ['email' => '', 'name' => ''];
    protected $arr_transaction_status = [
        0 => 'Đang thực hiện giao dịch',
        1 => 'Giao dịch chưa xác minh OTP',
        2 => 'Giao dịch đã xác minh OTP',
        4 => 'Giao dịch hoàn thành',
        5 => 'Giao dịch bị hủy',
        6 => 'Giao dịch bị từ chối nhận tiền',
        7 => 'Giao dịch hết hạn',
        8 => 'Giao dịch thất bại',
        12 => 'Giao dịch bị đóng băng',
        13 => 'Giao dịch bị tạm giữ (thanh toán an toàn)',
        15 => 'Giao dịch bị hủy khi chưa xác minh OTP',
        16 => 'Giao dịch chưa được xác minh.(giao dịch giả mạo)'
    ];
    protected $ush_id = null;
    protected $address_cc = null;

    public function __construct($request,$ush_id)
    {
        $this->request = $request;
        $this->ush_id = $ush_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ush = UserSpendHistory::find($this->ush_id);
        if ($ush) {
            $user = User::find($ush->ush_user_id);
            if($user){
                if($user->use_email!=$this->request['order']['customer_email']){
                    $this->address_cc = $this->request['order']['customer_email'];
                }
            }

            $transaction_status = $this->request['txn']['stat'];
            if (key_exists($transaction_status, $this->arr_transaction_status)) {
                if ($transaction_status == 4||$transaction_status==13) {
                    $this->subject = 'Nạp XU vào tài khoản ' . $user->use_email . ' thành công - Thông báo từ trang bất động sản: https://sosanhnha.com';
                } else {
                    $this->subject =$this->arr_transaction_status[$transaction_status] . ' - Thông báo từ trang bất động sản: https://sosanhnha.com';
                }
            }else{
                $this->subject ='Giao dịch không thành công - Thông báo từ trang bất động sản: https://sosanhnha.com';
            }
            $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
            try {
                //Server settings
                $mail->CharSet = 'UTF-8';
                $mail->SMTPDebug = 2;                                 // Enable verbose debug output
                $mail->isSMTP();                                      // Set mailer to use SMTP
                $mail->Host = config('phpmailer.host');  // Specify main and backup SMTP servers
                $mail->SMTPAuth = config('phpmailer.smtp_auth');                               // Enable SMTP authentication
                $mail->Username = config('phpmailer.user_name');                 // SMTP username
                $mail->Password = config('phpmailer.password');                           // SMTP password
                $mail->SMTPSecure = config('phpmailer.smtp_secure');                            // Enable TLS encryption, `ssl` also accepted
                $mail->Port = config('phpmailer.port');                                    // TCP port to connect to

                //Recipients
                $mail->setFrom(config('phpmailer.user_name'), config('app.app_name'));
                $user_email = filter_var(trim($user->use_email), FILTER_VALIDATE_EMAIL)!==false&&strpos($user->use_email,"@facebook")===false?$user->use_email:$user->use_email_payment;
                $mail->addAddress(trim($user_email), $user->use_fullname);     // Add a recipient
                $mail->addReplyTo(config('phpmailer.user_name'), config('app.app_name'));
                if(strpos($user->use_email,"@facebook")===false&&$user->use_email_payment!=null&&$user->use_email!=$user->use_email_payment){
                    $mail->addCC(trim($user->use_email_payment));
                }
//            $mail->addBCC('bcc@example.com');

                //Attachments
//            $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
//            $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

                //Content
                $mail->isHTML(true);                                  // Set email format to HTML
                $mail->Subject = $this->subject;
                $mail->Body = $this->renderBody($user, $ush->ush_order_id,$ush);
                $mail->AltBody = 'Email send from website: https://sosanhnha.com';
                $mail->send();
            } catch (\Exception $e) {
                sendNotifySlack(['ush_id'=>$this->ush_id,'type'=>'bk_send_mail','status'=>'fail','message'=>$e->getMessage(),'mail_exception'=>$mail->ErrorInfo],'https://hooks.slack.com/services/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');
                @file_put_contents(storage_path('logs/mail_log.log'),date('d-m-Y H:i:s').':'.'PHPMAILER(notification baokim):' . $mail->ErrorInfo. "\n",FILE_APPEND);
            }
        }
    }

    protected function renderBody($user,$order_id,$ush)
    {
        $money = Money::select('mon_count')->where('mon_user_id',$user->use_id)->first();

        $body='
        
        <div style="margin: 0">
    <div style="display:table;font-size:12px;line-height:18px;font-family:arial">

        <div style="display:table;text-align:center;width:650px;margin-top:5px">
            <b style="color:#21b353;text-align:center;font-size:17px"> WEBSITE BẤT ĐỘNG SẢN SOSANHNHA.COM </b>
        </div>
        <div style="display:table;color:#333333;padding-left:8px;font-family:arial">
            <p style="font-size:13px">
                Kính gửi: <span>  '.$user->use_loginname.'</span> <br>
                Quý khách thực hiện giao dịch nạp XU vào tài khoản với mã giao dịch '.$order_id.' trên website <a href="https://sosanhnha.com" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://sosanhnha.com&amp;source=gmail&amp;ust=1544782641526000&amp;usg=AFQjCNFqbPaPMh3gQApKbbYcXTDrm1ZCUQ">https://sosanhnha.com</a> .<br>
                Thông tin giao dịch:<br>
            </p>
        </div>
        <div style="display:table;font-size:13px;padding-left:8px;padding-bottom:2px">
            <table style="border-spacing:2px;text-indent:0;border-collapse:collapse">
                <tbody>
                <tr style="display:table-row;vertical-align:inherit">
                    <td style="border:1px solid #aaaaaa;padding:8px 15px"> <b> Mã giao dịch</b> </td>
                    <td style="border:1px solid #aaaaaa;padding:8px 75px" colspan="3">  '.$order_id.' </td>
                </tr>

                <tr style="display:table-row;vertical-align:inherit">
                    <td style="border:1px solid #aaaaaa;padding:8px 15px"> <b>Số dư tài khoản </b> </td>
                    <td style="border:1px solid #aaaaaa;padding:8px 75px" colspan="3">'.number_format($money->mon_count,0,",",".").' XU </td>
                </tr>
                <tr style="display:table-row;vertical-align:inherit">
                    <td style="border:1px solid #aaaaaa;padding:8px 15px"> <b>Số XU nạp vào</b> </td>
                    <td style="border:1px solid #aaaaaa;padding:8px 75px;text-transform:uppercase" colspan="3">'.number_format($ush->ush_count,0,",",".").' XU </td>
                </tr>
                <tr style="display:table-row;vertical-align:inherit">
                    <td style="border:1px solid #aaaaaa;padding:8px 15px"> <b>Nội dung giao dịch</b> </td>
                    <td colspan="3" style="border:1px solid #aaaaaa;padding:8px 75px"> Nạp XU sosanhnha.com </td>
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
                <p style="font-weight:bold;font-size:13px;margin-right:8px;color:#4e4e4e"> Email: '.config('phpmailer.user_name').'<br>
                    <a style="color:#006c9a;text-decoration:none" href="https://www.sosanhnha.com/" target="_blank" >www.sosanhnha.com</a>
                </p>
            </div>
        </div>
    </div>
</div>
<!--<style>-->
        
        ';
//        $body = '
//        <div>
//    <p>Chào '.$user->use_loginname.'</p>
//    <p>Hệ thống website <a href="https://sosanhnha.com">https://sosanhnha.com</a> thông báo trạng thái giao dịch có mã <b>'.$order_id.'</b>: <span style="color:red">'.$status.'</span>. Nếu có bất kì thắc mắc nào ban có thể trả lời mail này hoặc liên hệ với quản trị viên. Chúng tôi sẽ hỗ trợ bạn sớm nhất có thể.</p><br>
//    <p>
//        <b>Cảm ơn bạn đã sử dụng dịch vụ của trong tôi.</b>
//    </p>
//</div>
//        ';
        return $body;
    }
}
