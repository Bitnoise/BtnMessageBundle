<?php

namespace Btn\MessageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\UserInterface;

/**
 * Message abstract class
 *
 * @ORM\MappedSuperclass()
 */
abstract class Message extends Base
{
    /**
     * @var \Btn\MessageBundle\Entity\Thread
     *
     * @ORM\ManyToOne(targetEntity="Thread", inversedBy="messages")
     * @ORM\JoinColumn(name="thread_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $thread;

    /**
     * @var Message
     *
     * @ORM\ManyToOne(targetEntity="Message", inversedBy="replies")
     * @ORM\JoinColumn(name="reply_to_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $replyTo;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Message", mappedBy="replyTo")
     */
    protected $replies;

    /**
     * @abstract UserInterface
     *
     * @ORM\JoinColumn(name="sender_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $sender;

    /**
     * @abstract UserInterface
     *
     * @ORM\JoinColumn(name="recipient_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $recipient;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_new", type="boolean")
     */
    protected $isNew = true;

    /**
     * @var boolean
     */
    protected $wasNew;

    /**
     * @var Metadata
     *
     * @ORM\Column(name="metadata", type="object", nullable=true)
     */
    protected $metadata;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->replies = new ArrayCollection();
    }

    /**
     * Set thread
     *
     * @param  integer $thread
     * @return Message
     */
    public function setThread($thread)
    {
        $this->thread = $thread;

        return $this;
    }

    /**
     * Get thread
     *
     * @return Thread
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * Get thread id
     *
     * @return integer
     */
    public function getThreadId()
    {
        return $this->getThread()->getId();
    }

    /**
     * Is last message in thread
     */
    public function isLastMessage()
    {
        return $this->getThread()->getLastMessage() === $this;
    }

    /**
     * Set reply to
     *
     * @param Message $message
     */
    public function setReplyTo(Message $message)
    {
        $this->replyTo = $message;
    }

    /**
     * Get reply to message
     *
     * @return Message
     */
    public function getReplyTo()
    {
        return $this->replyTo;
    }

    /**
     * @return ArrayCollection
     */
    public function getReplies()
    {
        return $this->replies;
    }

    /**
     * Set sender
     *
     * @param  UserInterface $sender
     * @return Message
     */
    public function setSender(UserInterface $sender)
    {
        if (null === $this->thread) {
            throw new \Exception('Set thread before adding sender');
        }

        $this->sender = $sender;
        $this->thread->addParticipant($sender);

        return $this;
    }

    /**
     * Get sender
     *
     * @return UserInterface
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Check if user is sender of message
     *
     * @param  UserInterface $user
     * @return bool
     */
    public function isSender(UserInterface $user)
    {
        return $this->getSender() === $user;
    }

    /**
     * Set recipient
     *
     * @param  UserInterface $recipient
     * @return Message
     */
    public function setRecipient(UserInterface $recipient)
    {
        if (null === $this->thread) {
            throw new \Exception('Set thread before adding recipient');
        }

        $this->recipient = $recipient;
        $this->thread->addParticipant($recipient);

        return $this;
    }

    /**
     * Get recipient
     *
     * @return UserInterface
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * Check if user is reciment of message
     *
     * @param  UserInterface $user
     * @return bool
     */
    public function isRecipient(UserInterface $user)
    {
        return $this->getRecipient() === $user;
    }

    /**
     * Check if user is participant of message (sender or recipient)
     *
     * @param  UserInterface $user
     * @return bool
     */
    public function isParticipant(UserInterface $user)
    {
        return $this->isSender($user) || $this->isRecipient($user);
    }

    /**
     * Get other participant of message
     * @param  UserInteface $user
     * @return UserInteface
     */
    public function getOtherParticipant(UserInterface $user)
    {
        if ($this->isParticipant($user)) {
            return $this->isRecipient($user) ? $this->getSender() : $this->getRecipient();
        }
    }

    /**
     * Set isNew
     *
     * @param  boolean $isNew
     * @return Message
     */
    public function setIsNew($isNew)
    {
        $this->getWasNew(); //update info before change state

        $this->isNew = $isNew;

        return $this;
    }

    /**
     * Get isNew
     *
     * @return boolean
     */
    public function getIsNew()
    {
        return $this->isNew;
    }

    /**
     * Alias for getIsNew method
     */
    public function isNew()
    {
        return $this->getIsNew();
    }

    /**
     * Check if message is new for user
     */
    public function isNewFor(UserInterface $user)
    {
        return $this->isNew() && $this->isRecipient($user);
    }

    /**
     * get was message new, keeps information
     */
    public function getWasNew()
    {
        if (is_null($this->wasNew)) {
            $this->wasNew = $this->getIsNew();
        }

        return $this->wasNew;
    }

    /**
     * Alias for getWasNew method
     */
    public function wasNew()
    {
        return $this->getWasNew();
    }

    /**
     * Check if message was new for user
     */
    public function wasNewFor(UserInterface $user)
    {
        return $this->wasNew() && $this->isRecipient($user);
    }

    /**
     * Set metadata
     *
     * @param  Metadata $metadata
     * @return Message
     */
    public function setMetadata(Metadata $adress)
    {
        $this->metadata = $adress;

        return $this;
    }

    /**
     * Get metadata
     *
     * @return Metadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
}
