<?php

namespace AppBundle\Admin;

use AppBundle\CitizenProject\CitizenProjectManager;
use Sonata\AdminBundle\Datagrid\DatagridInterface;

class CitizenProjectDatagrid implements DatagridInterface
{
    use DatagridDecoratorTrait;

    private $manager;
    private $cachedResults;

    public function __construct(DatagridInterface $decorated, CitizenProjectManager $manager)
    {
        $this->setDecorated($decorated);

        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        if (!$this->cachedResults) {
            $results = $this->decorated->getResults();

            foreach ($results as $result) {
                $result->administrators = $this->manager->getCitizenProjectAdministrators($result);
            }

            $this->cachedResults = $results;
        }

        return $this->cachedResults;
    }
}
