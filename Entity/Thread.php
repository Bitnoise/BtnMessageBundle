<?php

namespace Btn\MessageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\UserInterface;

/**
 * Thread abstract class
 */
abstract class Thread extends Base
{
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Message", mappedBy="thread")
     * @ORM\OrderBy({"updatedAt" = "desc"})
     */
    protected $messages;

    /**
     * @var \Btn\MessageBundle\Entity\Message
     *
     * @ORM\OneToOne(targetEntity="Message")
     * @ORM\JoinColumn(name="last_message_id", referencedColumnName="id")
     */
    protected $lastMessage;

    /**
     * @abstract set ManyToMany mapping to user entity
     *
     * @ORM\JoinTable(name="thread_user",
     *      joinColumns={@ORM\JoinColumn(name="thread_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     * )
     */
    protected $participants;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_archive", type="boolean")
     */
    protected $isArchive = false;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->participants = new ArrayCollection();
        $this->messages     = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Get last message
     *
     * @return Message
     */
    public function getLastMessage()
    {
        return $this->lastMessage;
    }

    /**
     * Set last message
     *
     * @param Message $message
     */
    public function setLastMessage(Message $message)
    {
        $this->lastMessage = $message;
    }

    /**
     * @return ArrayCollection of UserInterface
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * Get other participant of message
     * @param  UserInterface $user
     * @return UserInterface
     */
    public function getOtherParticipant(UserInterface $user)
    {
        if (count($this->participants) > 0) {
            foreach ($this->participants as $participant) {
                if ($participant != $user) {
                    return $participant;
                }
            }
        }

        return null;
    }

    /**
     * Check if user is participant in thread
     *
     * @param  UserInterface $user
     * @return bool
     */
    public function isParticipant(UserInterface $user)
    {
        return $this->participants->contains($user);
    }

    /**
     * Add participent to thread
     *
     * @param UserInterface $participant
     */
    public function addParticipant(UserInterface $participant)
    {
        if (!$this->isParticipant($participant)) {
            $this->participants->add($participant);
        }
    }

    /**
     * Remove participent from thread
     *
     * @param UserInterface $user
     */
    public function removeParticipant(UserInterface $user)
    {
        if ($this->isParticipant($user)) {
            $this->participants->removeElement($user);
        }
    }

    /**
     * Set isArchive
     *
     * @param  boolean $isArchive
     * @return Thread
     */
    public function setIsArchive($isArchive)
    {
        $this->isArchive = $isArchive;

        return $this;
    }

    /**
     * Get isArchive
     *
     * @return boolean
     */
    public function getIsArchive()
    {
        return $this->isArchive;
    }

    /**
     * Get is new from last message
     *
     * @return bool
     */
    public function isNew()
    {
        $lm = $this->getLastMessage();
        if ($lm && $lm->isNew()) {
            return true;
        }
    }
}
