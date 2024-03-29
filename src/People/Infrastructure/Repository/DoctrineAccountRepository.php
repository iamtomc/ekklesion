<?php

/*
 * This file is part of the Ekklesion project.
 * (c) Matías Navarro Carter <mnavarrocarter@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekklesion\People\Infrastructure\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Ekklesion\People\Domain\Model\Account;
use Ekklesion\People\Domain\Repository\AccountRepository;
use MNC\PhpDdd\Domain\Model\Collection;
use MNC\PhpDdd\Infrastructure\Domain\Model\DoctrineCollection;
use Ramsey\Uuid\UuidInterface;

/**
 * Class DoctrineAccountRepository.

 *
 * @author Matías Navarro Carter <mnavarro@option.cl>
 *
 * @method Account|null find(UuidInterface $id)
 */
class DoctrineAccountRepository extends EntityRepository implements AccountRepository
{
    public function all(): Collection
    {
        $qb = $this->createQueryBuilder('a');

        return new DoctrineCollection(new Paginator($qb->getQuery()));
    }

    public function ofId(UuidInterface $id): ?Account
    {
        return $this->find($id);
    }

    /**
     * @param string $username
     *
     * @return Account|null
     */
    public function ofUsername(string $username): ?Account
    {
        $qb = $this->createQueryBuilder('a');
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->eq('a.username.normal', ':username'),
            $qb->expr()->eq('a.username.canonical', ':username')
        ))
        ->setParameter('username', $username);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * @param Account $account
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function add(Account $account): void
    {
        $this->getEntityManager()->persist($account);
    }

    /**
     * @param Account $account
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function remove(Account $account): void
    {
        $this->getEntityManager()->remove($account);
    }
}
