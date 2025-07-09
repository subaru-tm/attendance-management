<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmail extends VerifyEmailBase
{
    /**
     * メールメッセージを構築する。
     * 
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('メールアドレス確認のお願い')
            ->line('ご登録ありがとうございます。メールアドレス認証をお願いいたします') // 本文
            ->action('認証はこちらから', $verificationUrl);  // アクションボタン
    }

    /**
     * メール認証用URLを生成する。
     * 
     * @param mixed $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        return parent::verificationUrl($notifiable);
    }

}