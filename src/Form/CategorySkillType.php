<?php

namespace AppBundle\Form;

use AppBundle\Entity\CitizenProjectCategorySkill;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategorySkillType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('skill')
            ->add('promotion', CheckboxType::class, [
                'required' => false,
                'label' => 'Promotion',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', CitizenProjectCategorySkill::class);
    }
}
