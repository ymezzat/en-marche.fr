<?php

namespace AppBundle\Repository;

use AppBundle\Collection\CommitteeCollection;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Geocoder\Coordinates;
use AppBundle\Committee\Filter\CommitteeFilters;
use AppBundle\Search\SearchParametersFilter;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Ramsey\Uuid\UuidInterface;

class CommitteeRepository extends EntityRepository
{
    use NearbyTrait;
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    const ONLY_APPROVED = 1;
    const INCLUDE_UNAPPROVED = 2;

    public function count(): int
    {
        return (int) $this
            ->createQueryBuilder('c')
            ->select('COUNT(c)')
            ->where('c.status = :approved')
            ->setParameter('approved', Committee::APPROVED)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Finds a Committee instance by its unique canonical name.
     *
     * @param string $name
     *
     * @return Committee|null
     */
    public function findOneByName(string $name): ?Committee
    {
        $canonicalName = Committee::canonicalize($name);

        return $this->findOneBy(['canonicalName' => $canonicalName]);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByUuid(string $uuid): ?Committee
    {
        return $this->findOneByValidUuid($uuid);
    }

    /**
     * Finds approved Committee instances.
     *
     * @return Committee[]
     */
    public function findApprovedCommittees()
    {
        return $this->findBy(['status' => Committee::APPROVED]);
    }

    /**
     * Returns the most recent created Committee.
     *
     * @return Committee|null
     */
    public function findMostRecentCommittee(): ?Committee
    {
        $query = $this
            ->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }

    /**
     * @param int         $count
     * @param Coordinates $coordinates
     *
     * @return Committee[]
     */
    public function findNearbyCommittees(int $count, Coordinates $coordinates)
    {
        $qb = $this
            ->createNearbyQueryBuilder($coordinates)
            ->andWhere('n.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->setMaxResults($count)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns the total number of approved committees.
     *
     * @return int
     */
    public function countApprovedCommittees(): int
    {
        $query = $this
            ->createQueryBuilder('c')
            ->select('COUNT(c.uuid)')
            ->where('c.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->getQuery()
        ;

        return $query->getSingleScalarResult();
    }

    public function findCommittees(array $uuids, int $statusFilter = self::ONLY_APPROVED, int $limit = 0): CommitteeCollection
    {
        if (!$uuids) {
            return new CommitteeCollection();
        }

        $statuses[] = Committee::APPROVED;
        if (self::INCLUDE_UNAPPROVED === $statusFilter) {
            $statuses[] = Committee::PENDING;
        }

        $qb = $this->createQueryBuilder('c');

        $qb
            ->where($qb->expr()->in('c.uuid', $uuids))
            ->andWhere($qb->expr()->in('c.status', $statuses))
            ->orderBy('c.membersCounts', 'DESC')
        ;

        if ($limit >= 1) {
            $qb->setMaxResults($limit);
        }

        return new CommitteeCollection($qb->getQuery()->getResult());
    }

    public function findManagedBy(Adherent $referent)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->where('c.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->orderBy('c.name', 'ASC')
            ->orderBy('c.createdAt', 'DESC');

        $codesFilter = $qb->expr()->orX();

        foreach ($referent->getManagedArea()->getCodes() as $key => $code) {
            if (is_numeric($code)) {
                // Postal code prefix
                $codesFilter->add(
                    $qb->expr()->andX(
                        'c.postAddress.country = \'FR\'',
                        $qb->expr()->like('c.postAddress.postalCode', ':code'.$key)
                    )
                );

                $qb->setParameter('code'.$key, $code.'%');
            } else {
                // Country
                $codesFilter->add($qb->expr()->eq('c.postAddress.country', ':code'.$key));
                $qb->setParameter('code'.$key, $code);
            }
        }

        $qb->andWhere($codesFilter);

        return $qb->getQuery()->getResult();
    }

    public function findManagedByCoordinator(Adherent $coordinator, CommitteeFilters &$filters): array
    {
        if (!$coordinator->isCoordinator()) {
            return [];
        }

        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->orderBy('c.name', 'ASC')
            ->orderBy('c.createdAt', 'DESC');

        $codesFilter = $qb->expr()->orX();

        foreach ($coordinator->getCoordinatorManagedArea()->getCodes() as $key => $code) {
            if (is_numeric($code)) {
                // Postal code prefix
                $codesFilter->add(
                    $qb->expr()->andX(
                        'c.postAddress.country = \'FR\'',
                        $qb->expr()->like('c.postAddress.postalCode', ':code'.$key)
                    )
                );

                $qb->setParameter('code'.$key, $code.'%');
            } else {
                // Country
                $codesFilter->add($qb->expr()->eq('c.postAddress.country', ':code'.$key));
                $qb->setParameter('code'.$key, $code);
            }
        }

        $qb->andWhere($codesFilter);

        if ($filters) {
            $filters->apply($qb, 'c');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return int
     */
    public function countSitemapCommittees(): int
    {
        return (int) $this->createSitemapQb()
            ->select('COUNT(c) AS nb')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param int $page
     * @param int $perPage
     *
     * @return array
     */
    public function findSitemapCommittees(int $page, int $perPage): array
    {
        return $this->createSitemapQb()
            ->select('c.uuid', 'c.slug')
            ->orderBy('c.id')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return QueryBuilder
     */
    private function createSitemapQb(): QueryBuilder
    {
        return $this
            ->createQueryBuilder('c')
            ->where('c.status = :status')
            ->setParameter('status', Committee::APPROVED);
    }

    /**
     * @return Committee[]
     */
    public function searchCommittees(SearchParametersFilter $search): array
    {
        if ($coordinates = $search->getCityCoordinates()) {
            $qb = $this
                ->createNearbyQueryBuilder($coordinates)
                ->andWhere($this->getNearbyExpression().' < :distance_max')
                ->setParameter('distance_max', $search->getRadius());
        } else {
            $qb = $this->createQueryBuilder('n');
        }

        if (!empty($query = $search->getQuery())) {
            $qb->andWhere('n.name like :query');
            $qb->setParameter('query', '%'.$query.'%');
        }

        return $qb
            ->andWhere('n.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->setFirstResult($search->getOffset())
            ->setMaxResults($search->getMaxResults())
            ->getQuery()
            ->getResult()
        ;
    }

    public function hasCommitteeInStatus(Adherent $adherent, array $status): bool
    {
        $nb = $this->createQueryBuilder('c')
            ->select('COUNT(c) AS nb')
            ->where('c.createdBy = :creator')
            ->andWhere('c.status IN (:status)')
            ->setParameter('creator', $adherent->getUuid()->toString())
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();

        return $nb > 0;
    }

    public function findCommitteesUuidByCreatorUuids(array $creatorsUuid): array
    {
        $qb = $this->createQueryBuilder('c');

        $query = $qb
            ->select('c.uuid')
            ->where('c.createdBy IN (:creatorsUuid)')
            ->setParameter('creatorsUuid', $creatorsUuid)
            ->getQuery()
        ;

        return array_map(function (UuidInterface $uuid) {
            return $uuid->toString();
        }, array_column($query->getArrayResult(), 'uuid'));
    }

    public function findByPartialName(string $search, int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.canonicalName LIKE :search')
            ->setParameter('search', '%'.strtolower($search).'%')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
