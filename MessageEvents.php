<?php

namespace Btn\MessageBundle;

final class MessageEvents
{
    /**
     * The message_send event is thrown each time an message is send
     *
     * The event listener receives an
     * Acme\MessageBundle\Event\MessageEvent instance.
     *
     * @var string
     */
    const MESSAGE_SEND = 'btn_message.message_send';
}
