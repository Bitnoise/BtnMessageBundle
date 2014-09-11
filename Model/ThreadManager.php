<?php

namespace Btn\MessageBundle\Model;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;
use Btn\MessageBundle\Entity\Thread;
use FOS\UserBundle\Model\UserInterface;

class ThreadManager
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
     * @param EntityManager $em
     * @param string        $entityName
     */
    public function __construct(EntityManager $em, $entityName)
    {
        $this->em         = $em;
        $this->repo       = $em->getRepository($entityName);
        $this->entityName = $em->getClassMetadata($entityName)->name;
    }

    /**
     * Create thread
     *
     * @return Thread
     */
    public function createThread()
    {
        $entityName = $this->getEntityName();

        return new $entityName;
    }

    /**
     * Save thread
     *
     * @param Thread  $thread
     * @param Boolean $andFlush
     */
    public function saveThread(Thread $thread, $andFlush = true)
    {
        $this->em->persist($thread);
        if ($andFlush) {
            $this->em->flush();
        }
    }

    /**
     * Get Message entity name
     *
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @param UserInterface $user
     * @param bool $isArchive
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getUserThreadsQueryQueryBuilder(UserInterface $user, $isArchive = false)
    {
        $qb = $this->em->createQueryBuilder();

        $qb
            ->from($this->entityName, 't')
            ->select('t')
            ->join('t.participants', 'p', 'WITH', 'p.id = :user')
            ->where('t.isArchive = :isArchive')
            ->setParameter('user', $user)
            ->setParameter('isArchive', $isArchive)
            ->orderBy('t.updatedAt', 'DESC')
        ;

        return $qb;
    }

    /**
     * Get user threads
     *
     * @param UserInterface $user
     * @param bool          $isArchive
     *
     * @return Doctrine\ORM\Query
     */
    public function getUserThreadsQuery(UserInterface $user, $isArchive = false)
    {
        $qb    = $this->getUserThreadsQueryQueryBuilder($user, $isArchive);
        $query = $qb->getQuery();

        return $query;
    }

    /**
     * Get users thread with limit
     *
     * @param  UserInterface $user
     * @param  integer       $limit
     * @param  boolean       $isArchive
     * @return array         of Thread
     */
    public function getUserThreadsLimit(UserInterface $user, $limit, $isArchive = false)
    {
        $query = $this->getUserThreadsQuery($user, $isArchive);
        $query->setMaxResults($limit);

        return $query->getResult();
    }

    /**
     * Get users threads
     *
     * @param UserInterface[] $users
     *
     * @return Doctrine\ORM\Query
     */
    public function getUsersThreadsQuery(array $users)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('t');
        $qb->from($this->entityName, 't');

        foreach ($users as $key => $user) {
            if ($user instanceof UserInterface) {
                $i = (int) $key;
                $qb->join('t.participants', 'p'.$i, Expr\Join::WITH, 'p'.$i.'.id = :user'.$i);
                $qb->setParameter(':user'.$i, $user, \Doctrine\DBAL\Types\Type::INTEGER);
            }
        }

        $qb->addOrderBy('t.updatedAt', 'DESC');

        $query = $qb->getQuery();

        return $query;
    }
}
