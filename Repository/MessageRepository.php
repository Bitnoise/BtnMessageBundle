<?php

namespace Btn\MessageBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Btn\MessageBundle\Entity\ThreadInterface;

class MessageRepository extends EntityRepository
{
    public function getMessagesForThreadQueryBuilder(ThreadInterface $thread)
    {
        $qb = $this->createQueryBuilder('m');

        $qb->andWhere('m.thread = :thread')->setParameter(':thread', $thread);
        $qb->orderBy('m.updatedAt', 'DESC');

        return $qb;
    }
}
