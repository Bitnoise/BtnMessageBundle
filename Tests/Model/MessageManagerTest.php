<?php

namespace Btn\MessageBundle\Tests\Model;

use Btn\MessageBundle\Model\MessageManager;

class MessageManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $entityName = 'Btn\MessageBundle\Entity\Message';

    /**
     * @var string
     */
    protected $mockEntityName = '\Btn\MessageBundle\Tests\Model\MockMessage';

    /**
     * @var string
     */
    protected $testSubject = 'Subject';

    /**
     * @var string
     */
    protected $testBody = 'Body';

    /**
     * @var array
     */
    protected $messageType;

    /**
     * @var \Btn\MessageBundle\Entity\Metadata
     */
    protected $mockMetadata;

    /**
     * @var \FOS\UserBundle\Model\UserInterface
     */
    protected $mockSender;

    /**
     * @var \FOS\UserBundle\Model\UserInterface
     */
    protected $mockRecipient;

    /**
     * @var MockThread
     */
    protected $mockThread;

    /**
     * @var \Btn\MessageBundle\Model\ThreadManager
     */
    protected $mockThreadManager;

    /**
     *
     */
    public function setUp()
    {
        $this->mockMetadata = $this->getMock('Btn\MessageBundle\Entity\Metadata');
        $this->mockSender = $this->getMockUser('Sender');
        $this->mockRecipient = $this->getMockUser('Recipient');
        $this->assertNotEquals($this->mockSender, $this->mockRecipient);
        $this->mockThread = new MockThread();
        $this->mockThreadManager = $this->getMockThreadManager();
        $this->messageType = array(
            'type1' => array('id' => 1),
            'type2' => array('id' => 2),
            'type3' => array('id' => 3),
        );
    }

    /**
     *
     */
    public function testCreateMessage()
    {
        $emMock = $this->getEmMock();
        $mm = new MessageManager($emMock, $this->entityName, $this->getMockThreadManager());
        $message = $mm->createMessage();
        $this->assertInstanceOf($this->mockEntityName, $message);
    }

    /**
     *
     */
    public function testSaveMessage()
    {
        $emMock = $this->getEmMock();
        $mm = new MessageManager($emMock, $this->entityName, $this->getMockThreadManager());
        $message = $mm->createMessage();

        $emMock
            ->expects($this->exactly(2))
            ->method('persist')
            ->with($this->equalTo($message))
        ;
        $emMock
            ->expects($this->exactly(1))
            ->method('flush')
        ;

        $mm->saveMessage($message);
        $mm->saveMessage($message, false);
    }

    /**
     *
     */
    public function testGetEntityName()
    {
        $emMock = $this->getEmMock();
        $mm = new MessageManager($emMock, $this->entityName, $this->getMockThreadManager());
        $entityName = $mm->getEntityName();
        $this->assertEquals($this->mockEntityName, $entityName);
    }

    /**
     *
     */
    public function testGetThreadManager()
    {
        $emMock = $this->getEmMock();
        $tmMock = $this->getMockThreadManager();
        $mm = new MessageManager($emMock, $this->entityName, $tmMock);
        $tm = $mm->getThreadManager();
        $this->assertEquals($tmMock, $tm);
    }

    /**
     *
     */
    public function testGetMessageType()
    {
        $emMock = $this->getEmMock();
        $mm = new MessageManager($emMock, $this->entityName, $this->getMockThreadManager(), $this->messageType);
        $messageType = $mm->getMessageType();
        $this->assertEquals($this->messageType, $messageType);
    }

    /**
     *
     */
    public function testGetTypeId()
    {
        $emMock = $this->getEmMock();
        $mm = new MessageManager($emMock, $this->entityName, $this->getMockThreadManager(), $this->messageType);
        $this->assertEquals(1, $mm->getTypeId('type1'));
        $this->assertEquals(2, $mm->getTypeId('type2'));
        $this->assertEquals(3, $mm->getTypeId('type3'));
        $this->assertNull($mm->getTypeId(null));

        $this->setExpectedException('Exception');
        $mm->getTypeId('abc');
    }

    /**
     *
     */
    public function testSendMessageInThread()
    {
        $this->mockThreadManager
            ->expects($this->exactly(1))
            ->method('saveThread')
            ->with($this->equalTo($this->mockThread), $this->equalTo(false))
        ;

        $emMock = $this->getEmMock();
        $emMock->expects($this->exactly(1))->method('persist');

        $mm = new MessageManager($emMock, $this->entityName, $this->mockThreadManager, $this->messageType);

        $this->mockMetadata = $this->getMock('Btn\MessageBundle\Entity\Metadata');

        $message = $mm->sendMessageInThread(
            $this->mockSender,
            $this->mockRecipient,
            $this->mockThread,
            $this->testSubject,
            $this->testBody,
            'type1',
            $this->mockMetadata
        );

        $this->verifyMessage($message, 1);
    }

    /**
     *
     */
    public function testSendReplay()
    {
        $mockMessage = new MockMessage();
        $mockMessage->setThread($this->mockThread);
        $mockMessage->setSender($this->mockSender);
        $mockMessage->setRecipient($this->mockRecipient);

        $emMock = $this->getEmMock();
        $emMock->expects($this->exactly(1))->method('persist');

        $mm = new MessageManager($emMock, $this->entityName, $this->mockThreadManager, $this->messageType);

        $message = $mm->sendReplay(
            $mockMessage,
            $this->testSubject,
            $this->testBody,
            'type2',
            $this->mockMetadata
        );

        $this->verifyMessage($message, 2, true);
    }

    /**
     *
     */
    public function testSendNewMessage()
    {
        $this->mockThreadManager
            ->expects($this->once())
            ->method('createThread')
            ->will($this->returnValue($this->mockThread))
        ;

        $emMock = $this->getEmMock();
        $mm = new MessageManager($emMock, $this->entityName, $this->mockThreadManager, $this->messageType);

        $message = $mm->sendNewMessage(
            $this->mockSender,
            $this->mockRecipient,
            $this->testSubject,
            $this->testBody,
            3,
            $this->mockMetadata
        );

        $this->verifyMessage($message, 3);
    }

    /**
     *
     */
    protected function verifyMessage($message, $type, $reply = false)
    {
        if ($reply) {
            $this->assertEquals($this->mockSender, $message->getRecipient());
            $this->assertEquals($this->mockRecipient, $message->getSender());
        } else {
            $this->assertEquals($this->mockSender, $message->getSender());
            $this->assertEquals($this->mockRecipient, $message->getRecipient());
        }
        $this->assertEquals($this->testSubject, $message->getSubject());
        $this->assertEquals($this->testBody, $message->getBody());
        $this->assertEquals($type, $message->getType());
        $this->assertEquals($this->mockThread, $message->getThread());
        $this->assertEquals($this->testSubject, $message->getThread()->getSubject());
        $this->assertEquals($this->testBody, $message->getThread()->getBody());
        $this->assertEquals($type, $message->getThread()->getType());
        $this->assertTrue($message->getIsNew());
        $this->assertInstanceOf('\DateTime', $message->getCreatedAt());
        $this->assertNull($message->getUpdatedAt());
        $this->assertEquals($message->getCreatedAt(), $message->getThread()->getCreatedAt());
        $this->assertEquals($message->getUpdatedAt(), $message->getThread()->getUpdatedAt());
        $this->assertEquals($this->mockMetadata, $message->getMetadata());
    }

    /**
     *
     */
    public function testMarkMessageAsRead()
    {
        $mockMessage = new MockMessage();
        $this->assertTrue($mockMessage->getIsNew());
        $emMock = $this->getEmMock();
        $mm = new MessageManager($emMock, $this->entityName, $this->mockThreadManager);
        $mm->markMessageAsRead($mockMessage);
        $this->assertFalse($mockMessage->getIsNew());
    }

    /**
     *
     */
    public function testProcessFormWithSubmit()
    {
        $formName = 'FormName';
        $emMock = $this->getEmMock();
        $mm = new MessageManager($emMock, $this->entityName, $this->mockThreadManager);
        $mockSendForm = $this->getMock(
            'Symfony\Component\Form\Form',
            array('isClicked'),
            array(),
            '',
            false
        );
        $mockForm = $this->getMock(
            'Symfony\Component\Form\Form',
            array('handleRequest', 'getName', 'get', 'isValid'),
            array(),
            '',
            false
        );
        $mockForm
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($formName))
        ;

        $mockRequest = $this->getMock(
            'Symfony\Component\HttpFoundation\Request',
            array('isMethod', 'get'),
            array(),
            '',
            false
        );
        $mockRequest
            ->expects($this->atLeastOnce())
            ->method('isMethod')
            ->with($this->equalTo('POST'))
            ->will($this->returnValue(true))
        ;
        $mockRequest
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with($this->equalTo($formName))
            ->will($this->returnValue(true))
        ;
        $mockSendForm
            ->expects($this->once())
            ->method('isClicked')
            ->will($this->returnValue(true))
        ;
        $mockForm
            ->expects($this->atLeastOnce())
            ->method('handleRequest')
            ->with($this->equalTo($mockRequest))
        ;
        $mockForm
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with($this->equalTo('send'))
            ->will($this->returnValue($mockSendForm))
        ;
        $mockForm
            ->expects($this->atLeastOnce())
            ->method('isValid')
            ->will($this->returnValue(true))
        ;
        $result = $mm->processForm($mockForm, $mockRequest);
        $this->assertTrue($result);
    }

    /**
     *
     */
    public function testProcessFormWithoutSubmit()
    {
        $emMock = $this->getEmMock();
        $mm = new MessageManager($emMock, $this->entityName, $this->mockThreadManager);
        $mockForm = $this->getMock(
            'Symfony\Component\Form\Form',
            array('submit', 'getName'),
            array(),
            '',
            false
        );

        $mockRequest = $this->getMock(
            'Symfony\Component\HttpFoundation\Request',
            array('isMethod', 'get'),
            array(),
            '',
            false
        );
        $mockRequest
            ->expects($this->atLeastOnce())
            ->method('isMethod')
            ->will($this->returnValue(false))
        ;
        $result = $mm->processForm($mockForm, $mockRequest);
        $this->assertNull($result);
    }

    /**
     *
     */
    protected function getMockUser($mockClassName)
    {
        return $this->getMock(
            'FOS\UserBundle\Model\UserInterface',
            array(),
            array(),
            $mockClassName,
            true
        );
    }

    /**
     *
     */
    protected function getMockThreadManager()
    {
        return $this->getMock(
            '\Btn\MessageBundle\Model\ThreadManager',
            array('saveThread', 'createThread'),
            array(),
            '',
            false
        );
    }

    /**
     *
     */
    protected function getEmMock()
    {
        $emMock = $this->getMock(
            '\Doctrine\ORM\EntityManager',
            array(
                'getRepository',
                'getClassMetadata',
                'persist',
                'flush',
            ),
            array(),
            '',
            false
        );
        $emMock
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo($this->entityName))
        ;
        $emMock->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->equalTo($this->entityName))
            ->will($this->returnValue((object) array('name' => $this->mockEntityName)));
        ;

        return $emMock;
    }
}
