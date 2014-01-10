<?php

namespace Btn\MessageBundle\Tests\Model;

use Btn\MessageBundle\Model\ThreadManager;

class ThreadManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $entityName = 'Btn\MessageBundle\Entity\Thread';

    /**
     * @var string
     */
    protected $mockEntityName = '\Btn\MessageBundle\Tests\Model\MockThread';

    /**
     *
     */
    public function setUp()
    {
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
                'createQuery',
                'setParameter',
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

    /**
     *
     */
    public function testCreateThread()
    {
        $emMock = $this->getEmMock();
        $tm = new ThreadManager($emMock, $this->entityName);
        $thread = $tm->createThread();
        $this->assertInstanceOf($this->mockEntityName, $thread);
    }

    /**
     *
     */
    public function testSaveThread()
    {
        $emMock = $this->getEmMock();
        $tm = new ThreadManager($emMock, $this->entityName);
        $thread = $tm->createThread();

        $emMock
            ->expects($this->exactly(2))
            ->method('persist')
            ->with($this->equalTo($thread))
        ;
        $emMock
            ->expects($this->exactly(1))
            ->method('flush')
        ;

        $tm->saveThread($thread);
        $tm->saveThread($thread, false);
    }

    /**
     *
     */
    public function testgetUserThreadsQuery()
    {
        $userMock = $this->getMock('FOS\UserBundle\Model\UserInterface');
        $queryMock = $this->getMock('Doctrine\ORM\QueryMock', array('setParameters'));
        $emMock = $this->getEmMock();
        $emMock
            ->expects($this->once())
            ->method('createQuery')
            ->will($this->returnValue($queryMock))
        ;
        $queryMock
            ->expects($this->once())
            ->method('setParameters')
            ->with($this->equalTo(array(':user' => $userMock, ':isArchive' => false)))
            ->will($this->returnValue($queryMock));
        ;
        $tm = new ThreadManager($emMock, $this->entityName);
        $result = $tm->getUserThreadsQuery($userMock);
        $this->assertEquals($queryMock, $result);
    }
}
