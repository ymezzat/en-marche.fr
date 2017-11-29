<?php

namespace AppBundle\Form;

use AppBundle\CitizenProject\CitizenProjectCommand;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectCategory;
use AppBundle\Entity\CitizenProjectCommitteeSupport;
use AppBundle\Entity\Committee;
use AppBundle\Repository\CommitteeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CitizenProjectCommandType extends AbstractType
{
    private $committeeRepository;

    public function __construct(CommitteeRepository $committeeRepository)
    {
        $this->committeeRepository = $committeeRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'filter_emojis' => true,
            ])
            ->add('subtitle', TextType::class, [
                'filter_emojis' => true,
            ])
            ->add('category', EventCategoryType::class, [
                'class' => CitizenProjectCategory::class,
            ])
            ->add('problem_description', TextareaType::class, [
                'property_path' => 'problemDescription',
                'filter_emojis' => true,
            ])
            ->add('proposed_solution', TextareaType::class, [
                'property_path' => 'proposedSolution',
                'filter_emojis' => true,
                'purify_html' => true,
            ])
            ->add('required_means', TextareaType::class, [
                'property_path' => 'requiredMeans',
                'filter_emojis' => true,
                'purify_html' => true,
            ])
            ->add('address', NullableAddressType::class)
            ->add('assistance_needed', CheckboxType::class, [
                'property_path' => 'assistanceNeeded',
                'required' => false,
            ])
            ->add('assistance_content', TextareaType::class, [
                'required' => false,
                'property_path' => 'assistanceContent',
                'purify_html' => true,
                'filter_emojis' => true,
            ])
            ->add('committeeSupports', CollectionType::class, [
                'required' => false,
                'entry_type' => TextType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('committees_search', TextType::class, [
                'mapped' => false,
                'required' => false,
                'filter_emojis' => true,
                'attr' => [
                    'placeholder' => 'Vous avez déjà le soutient d\'un comité local ? Indiquez son nom : (Optionnel)',
                ],
            ])
        ;

        $committeeRepository = $this->committeeRepository;

        $builder->get('committeeSupports')->addModelTransformer(new CallbackTransformer(
            function($collection) {
                $uuids = [];
                /** @var CitizenProjectCommitteeSupport $committeeSupports */
                foreach ($collection as $committeeSupports) {
                    $uuids[] = $committeeSupports->getCommittee()->getUuid()->toString();
                }

                return $uuids;
        }, function($uuids) use ($committeeRepository) {
                $collection = new ArrayCollection();

                foreach ($uuids as $uuid) {
                    if ($committee = $committeeRepository->findOneByUuid($uuid)) {
                        $collection->add($committee);
                    }
                }
                return $collection;
        }));
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $formEvent) {
            $formData = $formEvent->getData();
            $committeeSupports = $formData->getCommitteeSupports()->toArray();
            foreach ($committeeSupports as &$object) {
                if ($object instanceof Committee) {
                    $object = new CitizenProjectCommitteeSupport($formData, $object);
                }
            }

            $formData->setCommitteeSupports(new ArrayCollection($committeeSupports));
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CitizenProjectCommand::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'citizen_project';
    }
}
