<?php

namespace AppBundle\Form\EventListener;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CoordinatorManagedArea;
use Doctrine\Common\Collections\ArrayCollection;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CoordinatorManagedAreaListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
            FormEvents::POST_SET_DATA => 'onPostSetData',
            FormEvents::POST_SUBMIT => 'onPostSubmit',
        ];
    }

    /**
     * If the adherent has more than 1 coordinator managed area, then need to
     * allow the delete action on CollectionType form.
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event): void
    {
        $form = $event->getForm();
        /** @var Adherent $adherent */
        $adherent = $event->getData();

        if (!$adherent instanceof Adherent) {
            return;
        }

        if (1 < $adherent->getCoordinatorManagedAreas()->count()) {
            $fieldOptions = $form->get('coordinatorManagedAreas')->getConfig()->getOptions();

            $fieldOptions = array_merge(
                $fieldOptions,
                ['allow_delete' => true]
            );

            $form->add('coordinatorManagedAreas', CollectionType::class, $fieldOptions);
        }
    }

    /**
     * If the adherent has any coordinator managed area, then
     * need to add an empty coordinator managed area for avoiding empty form.
     *
     * @param FormEvent $event
     */
    public function onPostSetData(FormEvent $event): void
    {
        $form = $event->getForm();
        /** @var Adherent $adherent */
        $adherent = $event->getData();

        if (!$adherent instanceof Adherent) {
            return;
        }

        if (0 === $adherent->getCoordinatorManagedAreas()->count()) {
            $form->get('coordinatorManagedAreas')->setData(
                new ArrayCollection([new CoordinatorManagedArea()])
            );
        }
    }

    /**
     * If an empty coordinator managed area has been submit, then
     * need to remove them from adherent object for avoiding its persistence in base.
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event): void
    {
        $adherent = $event->getData();

        if (!$adherent instanceof Adherent) {
            return;
        }

        $adherent->setCoordinatorManagedAreas(
            $adherent->getCoordinatorManagedAreas()->filter(function (CoordinatorManagedArea $entity) {
                return !empty($entity->getCodes()) && !empty($entity->getSector());
            })
        );
    }
}
