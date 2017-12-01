<?php

namespace AppBundle\Admin;

use AppBundle\Entity\BaseEventCategory;
use AppBundle\Entity\CitizenProjectCategory;
use AppBundle\Entity\CitizenProjectCategorySkill;
use AppBundle\Form\CategorySkillType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CitizenProjectCategoryAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'ASC',
        '_sort_by' => 'name',
    ];

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Visibilité',
                'choices' => [
                    'Visible' => BaseEventCategory::ENABLED,
                    'Masqué' => BaseEventCategory::DISABLED,
                ],
            ])
            ->add('categorySkills', CollectionType::class, [
                'label' => 'Compétences',
                'entry_type' => CategorySkillType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'error_bubbling' => false,
            ])
        ;

        $formMapper->getFormBuilder()
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                /** @var CitizenProjectCategory $citizenProjectCategory */
                $citizenProjectCategory = $form->getData();

                /** @var CitizenProjectCategorySkill $categorySkill */
                foreach ($citizenProjectCategory->getCategorySkills() as $categorySkill) {
                    if (null === $categorySkill->getCategory()) {
                        $categorySkill->setCategory($citizenProjectCategory);
                    }
                }
            })
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('status', null, [
                'label' => 'Visibilité',
                'template' => 'admin/citizen_project_category/list_status.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }
}
