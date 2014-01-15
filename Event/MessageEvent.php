<?php

namespace Btn\MessageBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Btn\MessageBundle\Entity\Message;

class MessageEvent extends Event
{
    /**
     * @var \Btn\MessageBundle\Entity\Message
     */
    protected $message;

    /**
     * @param \Btn\MessageBundle\Entity\Message $message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Get message
     *
     * @return \Btn\MessageBundle\Entity\Message
     */
    public function getMessage()
    {
        return $this->message;
    }
}
