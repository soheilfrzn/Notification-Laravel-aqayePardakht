<?php

namespace Kavenegar\Laravel\Channel;

use Kavenegar\KavenegarApi;
use Kavenegar\Laravel\Message\KavenegarMessage;
use \Kavenegar\Laravel\Facade as Kavenegar;

class KavenegarChannel
{
    /**
     * The Kavenegar client instance.
     *
     * @var Kavenegar\KavenegarApi
     */
    protected $kavenegar;

    /**
     * The phone number notifications should be sent from.
     *
     * @var string
     */
    protected $from;

    /**
     * Create a new Kavenegar channel instance.
     *
     * @param Kavenegar\KavenegarApi $kavenegar
     * @param string $from
     * @return void
     */
    public function __construct(KavenegarApi $kavenegar, $from = null)
    {
        $this->from = $from;
        $this->kavenegar = $kavenegar;
        dd($this->kavenegar);
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @return \Kavenegar\Laravel\Message\KavenegarMessage
     */
    public function send($notifiable, $notification)
    {
        $message = $notification->toKavenegar($notifiable);

        $message->to($message->to ?: $notifiable->routeNotificationFor('kavenegar', $notification));
        if (!$message->to || !($message->from || $message->method)) {
            return;
        }

        return $message->method ?
            $this->verifyLookup($message) :
            Kavenegar::Send($message->from, $message->to, $message->content);
    }

    public function verifyLookup(KavenegarMessage $message)
    {
        $token2 = isset($message->tokens[1]) ? $message->tokens[1] : null;
        $token3 = isset($message->tokens[2]) ? $message->tokens[2] : null;
        return Kavenegar::VerifyLookup($message->to, $message->tokens[0], $token2, $token3, $message->method);
    }
}
