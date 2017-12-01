<?php

namespace AppBundle\Coordinator\Filter;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\BaseGroup;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractCoordinatorAreaFilter
{
    public const PER_PAGE = 20;

    private const PARAMETER_OFFSET = 'o';
    private const PARAMETER_STATUS = 's';

    private $status;
    protected $offset = 0;
    private $count = 0;
    /** @var Adherent */
    protected $coordinator;

    final private function __construct()
    {
    }

    abstract protected function getAvailableStatus(): array;

    abstract protected function getCoordinatorAreaCodes(): array;

    final public function apply(QueryBuilder $qb, string $alias): void
    {
        if ($this->coordinator->getCoordinatorManagedAreas()->count()) {
            $this->applyGeoFilter($qb, $alias);
        }

        $qb
            ->andWhere(sprintf('%s.status = :status', $alias))
            ->setParameter('status', $this->getStatus())

            ->orderBy(sprintf('%s.createdAt', $alias), 'DESC')
            ->addOrderBy(sprintf('%s.name', $alias), 'ASC')

            ->setFirstResult($this->offset)
            ->setMaxResults(static::PER_PAGE);

        $this->updateCount($qb, $alias);
    }

    public function __toString()
    {
        return $this->getQueryStringForOffset($this->offset);
    }

    public static function fromQueryString(Request $request)
    {
        $filters = new static();

        $filters->setStatus($request->query->get(self::PARAMETER_STATUS, BaseGroup::PENDING));
        $filters->setOffset($request->query->getInt(self::PARAMETER_OFFSET));

        return $filters;
    }

    public function setStatus(string $status): void
    {
        $status = trim($status);

        if ($status && !in_array($status, $this->getAvailableStatus(), true)) {
            throw new \UnexpectedValueException(sprintf('Unexpected committee request status "%s".', $status));
        }

        if (empty($status)) {
            $this->status = null;

            return;
        }

        $this->status = $status;
    }

    public function setCount(int $count): void
    {
        $this->count = $count;
    }

    public function getCount(): string
    {
        return $this->count;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    final public function getLimit(): int
    {
        return self::PER_PAGE;
    }

    public function getCoordinator(): Adherent
    {
        return $this->coordinator;
    }

    public function setCoordinator(Adherent $coordinator): void
    {
        $this->coordinator = $coordinator;
    }

    private function updateCount(QueryBuilder $qb, string $alias): void
    {
        $qbCount = clone $qb;

        $count = $qbCount
            ->select(sprintf('count(%s)', $alias))
            ->setMaxResults(null)
            ->setFirstResult(null)
            ->getQuery()
            ->getSingleScalarResult();

        $this->setCount($count);
    }

    public function getQueryStringForOffset(?int $offset): string
    {
        $parameters = $this->getQueryStringParameters();
        $parameters[self::PARAMETER_OFFSET] = $offset ?? $this->offset;

        return '?'.http_build_query($parameters);
    }

    protected function getQueryStringParameters(): array
    {
        if ($this->status) {
            $parameters[self::PARAMETER_STATUS] = $this->status;
        }

        return $parameters ?? [];
    }

    public function getPreviousPageQueryString(): string
    {
        $previousOffset = $this->offset - self::PER_PAGE;

        return $this->getQueryStringForOffset($previousOffset >= 0 ? $previousOffset : 0);
    }

    public function getNextPageQueryString(): string
    {
        return $this->getQueryStringForOffset($this->offset + self::PER_PAGE);
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    private function applyGeoFilter(QueryBuilder $qb, string $alias): void
    {
        $codesFilter = $qb->expr()->orX();

        foreach ($this->getCoordinatorAreaCodes() as $key => $code) {
            if (is_numeric($code)) {
                // Postal code prefix
                $codesFilter->add(
                    $qb->expr()->andX(
                        $alias.'.postAddress.country = \'FR\'',
                        $qb->expr()->like($alias.'.postAddress.postalCode', ':code'.$key)
                    )
                );

                $qb->setParameter('code'.$key, $code.'%');
            } else {
                // Country
                $codesFilter->add($qb->expr()->eq($alias.'.postAddress.country', ':code'.$key));
                $qb->setParameter('code'.$key, $code);
            }
        }

        $qb->andWhere($codesFilter);
    }
}