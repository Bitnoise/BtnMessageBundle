<?php

namespace Btn\MessageBundle\Model;

use Doctrine\ORM\EntityManager;
use Btn\MessageBundle\Entity\Message;
use Btn\MessageBundle\Entity\Thread;
use Btn\MessageBundle\Entity\Metadata;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class MessageManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $repo;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var \Btn\MessageBundle\Model\ThreadManager
     */
    protected $tm;

    /**
     * @var array
     */
    protected $messageType;

    /**
     * @param EntityManager $em
     * @param string        $entityName
     * @param ThreadManager $tm
     * @param array         $messageType
     */
    public function __construct(EntityManager $em, $entityName, ThreadManager $tm, array $messageType = null)
    {
        $this->em         = $em;
        $this->repo       = $em->getRepository($entityName);
        $this->entityName = $em->getClassMetadata($entityName)->name;
        $this->tm         = $tm;
        $this->messageType = $messageType;
    }

    /**
     * Create new message
     *
     * @return Message
     */
    public function createMessage()
    {
        $entityName = $this->getEntityName();

        return new $entityName;
    }

    /**
     * Save message
     *
     * @param Message $message
     * @param Boolean $andFlush
     */
    public function saveMessage(Message $message, $andFlush = true)
    {
        $this->em->persist($message);
        if ($andFlush) {
            $this->em->flush();
        }
    }

    /**
     * Get Message entity name
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * Get Thread Manager
     *
     * @return ThreadManager
     */
    public function getThreadManager()
    {
        return $this->tm;
    }

    /**
     * Get Message type
     *
     * @return array
     */
    public function getMessageType()
    {
        return $this->messageType;
    }

    /**
     * Send message (basic function)
     * @param  Message $message
     * @return Message
     */
    public function send(Message $message)
    {
        $thread = $message->getThread();

        // update thread with data from new message
        $thread->setSubject($message->getSubject());
        $thread->setBody($message->getBody());
        $thread->setType($message->getType());
        $thread->updated();

        // save thread and message
        $this->tm->saveThread($thread, false);
        $this->saveMessage($message);

        return $message;
    }

    /**
     * Send message
     *
     * @param  UserInterface $sender
     * @param  UserInterface $recipient
     * @param  string        $subject
     * @param  string        $body
     * @param  integer       $type
     * @param  Metadata      $metadata
     * @return Message
     */
    public function sendMessage(
        UserInterface $sender,
        UserInterface $recipient,
        Thread $thread,
        $subject,
        $body,
        $type = null,
        Message $replyTo = null,
        Metadata $metadata = null
    ) {
        $type = $this->getTypeId($type);
        $message = $this->createMessage();
        $message->setThread($thread);
        $message->setSender($sender);
        $message->setRecipient($recipient);
        $message->setSubject($subject);
        $message->setBody($body);
        $message->setType($type);
        if (null !== $replyTo) {
            $message->setReplyTo($replyTo);
        }
        if (null !== $metadata) {
            $message->setMetadata($metadata);
        }

        $thread->setLastMessage($message);

        return $this->send($message);
    }

    /**
     * Send new message in existing tread
     *
     * @param  UserInterface $sender
     * @param  UserInterface $recipient
     * @param  string        $subject
     * @param  string        $body
     * @param  integer       $type
     * @param  Metadata      $metadata
     * @return Message
     */
    public function sendMessageInThread(
        UserInterface $sender,
        UserInterface $recipient,
        Thread $thread,
        $subject,
        $body,
        $type = null,
        Metadata $metadata = null
    ) {
        return $this->sendMessage($sender, $recipient, $thread, $subject, $body, $type, null, $metadata);
    }

    /**
     * Send new message within new tread
     *
     * @param  UserInterface $sender
     * @param  UserInterface $recipient
     * @param  string        $subject
     * @param  string        $body
     * @param  integer       $type
     * @param  Metadata      $metadata
     * @return Message
     */
    public function sendNewMessage(
        UserInterface $sender,
        UserInterface $recipient,
        $subject,
        $body,
        $type = null,
        Metadata $metadata = null
    ) {
        return $this->sendMessageInThread(
            $sender,
            $recipient,
            $this->tm->createThread(),
            $subject,
            $body,
            $type,
            $metadata
        );
    }

    /**
     * Send replay to existing message
     *
     * @param  Message  $message
     * @param  string   $subject
     * @param  string   $body
     * @param  integer  $type
     * @param  Metadata $metadata
     * @return Message
     */
    public function sendReplay(Message $message, $subject, $body, $type = null, Metadata $metadata = null)
    {
        return $this->sendMessage(
            $message->getRecipient(),
            $message->getSender(),
            $message->getThread(),
            $subject,
            $body,
            $type,
            $message,
            $metadata
        );
    }

    /**
     * Mark message as read
     *
     * @param Message $message
     * @param bool    $andFlush
     */
    public function markMessageAsRead(Message $message, $andFlush = true)
    {
        $message->setIsNew(false);
        $this->saveMessage($message, $andFlush);
    }

    /**
     * Mark all user messages as read (where user is recipient)
     * @param UserInterface $user
     * @param Thread        $thread
     */
    public function markAllUserMessagesAsRead(UserInterface $user, Thread $thread = null)
    {
        $query = $this->em->createQuery(
            "SELECT m FROM $this->entityName m WHERE m.recipient = :user ".($thread ? " AND m.thread = :thread" : '')
        );
        $query->setParameter(':user', $user);
        if ($thread) {
            $query->setParameter(':thread', $thread);
        }

        $messages = $query->getResult();

        if ($messages) {
            foreach ($messages as $message) {
                $this->markMessageAsRead($message, false);
            }
            $this->em->flush();
        }
    }

    /**
     * Get type id for message type
     *
     * @param  integer      $input
     * @return integer|null
     */
    public function getTypeId($input)
    {
        if (!is_null($input) && $this->messageType) {
            foreach ($this->messageType as $key => $type) {
                if ($key === $input || (is_numeric($input) && $input == $type['id'])) {
                    return $type['id'];
                }
            }
            throw new \Exception("Message type for $input haven't been found");
        }

        return null;
    }

    /**
     * Get type key for message type
     *
     * @param  integer      $input
     * @return integer|null
     */
    public function getTypeKey($input)
    {
        if (!is_null($input) && $this->messageType) {
            foreach ($this->messageType as $key => $type) {
                if ($key === $input || (is_numeric($input) && $input == $type['id'])) {
                    return $key;
                }
            }
            throw new \Exception("Message type for $input haven't been found");
        }

        return null;
    }

    /**
     * Process form
     * @param Form    $form
     * @param Request $request
     *
     */
    public function processForm(Form $form, Request $request)
    {
        if ($request->isMethod('POST') && $request->get($form->getName())) {
            $form->handleRequest($request);
            if ($form->get('send')->isClicked()) {
                if ($form->isValid()) {
                    return true;
                }
            } else {
                //TODO: form is not clicked, clear errors or generete new form to prevent error messages
            }
        }
    }

    /**
     * Get messages from thread
     * @param  Thread             $thread
     * @return Doctrine\ORM\Query
     */
    public function getMessagesFromThreadQuery(Thread $thread)
    {
        $query = $this->em->createQuery(
            "SELECT m FROM $this->entityName m "
            . " WHERE m.thread = :thread "
            . " ORDER BY m.updatedAt DESC "
        );
        $query->setParameters(array(':thread' =>$thread));

        return $query;
    }
}
