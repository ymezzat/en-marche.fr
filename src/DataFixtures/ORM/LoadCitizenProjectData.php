<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\CitizenProject\CitizenProjectFactory;
use AppBundle\Entity\NullablePostAddress;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadCitizenProjectData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface, DependentFixtureInterface
{
    const CITIZEN_PROJECT_1_UUID = 'aa364092-3999-4102-930c-f711ef971195';
    const CITIZEN_PROJECT_2_UUID = '552934ed-2ac6-4a3a-a490-ddc8bf959444';
    const CITIZEN_PROJECT_3_UUID = '942201fe-bffa-4fed-a551-71c3e49cea43';
    const CITIZEN_PROJECT_4_UUID = '31fe9de2-5ba2-4305-be82-8e9a329e2579';
    const CITIZEN_PROJECT_5_UUID = '0ac45a9f-8495-4b32-bd2d-e43a27f5e4b6';
    const CITIZEN_PROJECT_6_UUID = 'cff414ca-3ee7-43db-8201-0852b0c05334';
    const CITIZEN_PROJECT_7_UUID = 'fc83efde-17e5-4e87-b9e9-71b165aecd10';
    const CITIZEN_PROJECT_8_UUID = '55bc9c81-612b-4108-b5ae-d065a69456d1';
    const CITIZEN_PROJECT_9_UUID = 'eacefe0b-ace6-4ed5-a747-61f874f165f6';

    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        // Create some default citizen projects and make people join them
        $citizenProjectFactory = $this->getCitizenProjectFactory();

        $citizenProject1 = $citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_1_UUID,
            'name' => 'Le projet citoyen à Paris 8',
            'subtitle' => 'Le projet citoyen des habitants du 8ème arrondissement de Paris.',
            'category' => $this->getReference('cpc001'),
            'problem_description' => 'Problème 1',
            'proposed_solution' => 'Solution 1',
            'required_means' => 'Les moyens 1',
            'assistance_needed' => false,
            'created_by' => LoadAdherentData::ADHERENT_3_UUID,
            'created_at' => '2017-10-12 12:25:54',
            'address' => NullablePostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', 48.8705073, 2.3032432),
        ]);

        $citizenProject1->addSkill($this->getReference('cps001'));
        $citizenProject1->addSkill($this->getReference('cps003'));

        $citizenProject1->approved('2017-10-12 15:54:18');
        $this->addReference('citizen-project-1', $citizenProject1);

        $citizenProject2 = $citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_2_UUID,
            'name' => 'Le projet citoyen à Marseille',
            'subtitle' => 'Le projet citoyen à Marseille !',
            'category' => $this->getReference('cpc002'),
            'problem_description' => 'Problème 2',
            'proposed_solution' => 'Solution 2',
            'required_means' => 'Les moyens 2',
            'assistance_needed' => false,
            'created_by' => LoadAdherentData::ADHERENT_6_UUID,
            'created_at' => '2017-10-12 18:34:12',
            'address' => NullablePostAddress::createFrenchAddress('30 Boulevard Louis Guichoux', '13003-13203', 43.3256095, 5.374416),
        ]);
        $this->addReference('citizen-project-2', $citizenProject2);

        $citizenProject3 = $citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_3_UUID,
            'name' => 'Le projet citoyen à Dammarie-les-Lys',
            'subtitle' => 'Le projet citoyen sans adresse et téléphone',
            'category' => $this->getReference('cpc003'),
            'problem_description' => 'Problème 3',
            'proposed_solution' => 'Solution 3',
            'required_means' => 'Les moyens 3',
            'assistance_needed' => false,
            'created_by' => LoadAdherentData::ADHERENT_7_UUID,
            'created_at' => '2017-10-26 16:08:24',
            'address' => NullablePostAddress::createFrenchAddress('30 Boulevard Louis Guichoux', '13003-13203', 43.3256095, 5.374416),
        ]);
        $citizenProject3->approved('2017-10-27 10:18:33');
        $this->addReference('citizen-project-3', $citizenProject3);

        $citizenProject4 = $citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_4_UUID,
            'subtitle' => 'Encore un projet citoyen',
            'category' => $this->getReference('cpc004'),
            'problem_description' => 'Problème 4',
            'proposed_solution' => 'Solution 4',
            'required_means' => 'Les moyens 4',
            'assistance_needed' => false,
            'created_by' => LoadAdherentData::ADHERENT_7_UUID,
            'created_at' => '2017-09-19 07:36:55',
            'name' => 'Massive Open Online Course',
            'address' => NullablePostAddress::createFrenchAddress('30 Boulevard Louis Guichoux', '13003-13203', 43.3256095, 5.374416),
        ]);
        $citizenProject4->approved();
        $this->addReference('citizen-project-4', $citizenProject4);

        $citizenProject5 = $citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_5_UUID,
            'name' => 'Formation en ligne ouverte à tous à Évry',
            'subtitle' => 'Équipe de la formation en ligne ouverte à tous à Évry',
            'category' => $this->getReference('cpc005'),
            'problem_description' => 'Problème 5',
            'proposed_solution' => 'Solution 5',
            'required_means' => 'Les moyens 5',
            'assistance_needed' => false,
            'created_by' => LoadAdherentData::ADHERENT_7_UUID,
            'created_at' => '2017-10-19 11:54:28',
            'address' => NullablePostAddress::createFrenchAddress("Place des Droits de l'Homme et du Citoyen", '91000-91228', 48.6241569, 2.4265995),
        ]);
        $citizenProject5->approved();
        $this->addReference('citizen-project-5', $citizenProject5);

        $citizenProject6 = $citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_6_UUID,
            'name' => 'Formation en ligne ouverte à tous',
            'subtitle' => 'Équipe de la formation en ligne ouverte à tous',
            'category' => $this->getReference('cpc006'),
            'problem_description' => 'Problème 6',
            'proposed_solution' => 'Solution 6',
            'required_means' => 'Les moyens 6',
            'assistance_needed' => false,
            'created_by' => LoadAdherentData::ADHERENT_9_UUID,
            'created_at' => '2017-09-18 20:12:33',
            'address' => NullablePostAddress::createFrenchAddress('30 Boulevard Louis Guichoux', '13003-13203', 43.3256095, 5.374416),
        ]);
        $citizenProject6->approved('2017-10-19 09:17:24');
        $this->addReference('citizen-project-6', $citizenProject6);

        $citizenProject7 = $citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_7_UUID,
            'name' => 'Projet citoyen à Berlin',
            'subtitle' => 'Projet citoyen de nos partenaires Allemands.',
            'category' => $this->getReference('cpc001'),
            'problem_description' => 'Problème 7',
            'proposed_solution' => 'Solution 7',
            'required_means' => 'Les moyens 7',
            'assistance_needed' => false,
            'created_by' => LoadAdherentData::ADHERENT_10_UUID,
            'created_at' => '2017-09-18 09:14:45',
            'address' => NullablePostAddress::createFrenchAddress('30 Boulevard Louis Guichoux', '13003-13203', 43.3256095, 5.374416),
        ]);
        $citizenProject7->approved('2017-03-19 13:43:26');
        $this->addReference('citizen-project-7', $citizenProject7);

        $citizenProject8 = $citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_8_UUID,
            'name' => 'En Marche - Projet citoyen',
            'subtitle' => 'Projet citoyen.',
            'category' => $this->getReference('cpc002'),
            'problem_description' => 'Problème 8',
            'proposed_solution' => 'Solution 8',
            'required_means' => 'Les moyens 8',
            'assistance_needed' => false,
            'created_by' => LoadAdherentData::ADHERENT_11_UUID,
            'created_at' => '2017-10-10 17:34:18',
            'address' => NullablePostAddress::createFrenchAddress('30 Boulevard Louis Guichoux', '13003-13203', 43.3256095, 5.374416),
        ]);
        $citizenProject8->approved('2017-10-10 18:23:18');
        $this->addReference('citizen-project-8', $citizenProject8);

        $citizenProject9 = $citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_9_UUID,
            'name' => 'Projet citoyen à New York City',
            'subtitle' => 'Projet citoyen à New York City.',
            'category' => $this->getReference('cpc003'),
            'problem_description' => 'Problème 3',
            'proposed_solution' => 'Solution 3',
            'required_means' => 'Les moyens 3',
            'assistance_needed' => false,
            'created_by' => LoadAdherentData::ADHERENT_12_UUID,
            'created_at' => '2017-10-09 12:16:22',
            'address' => NullablePostAddress::createForeignAddress('US', '10019', 'New York', '226 W 52nd St', 40.7625289, -73.9859927),
        ]);
        $citizenProject9->approved('2017-10-09 13:27:42');
        $this->addReference('citizen-project-9', $citizenProject9);

        $manager->persist($citizenProject1);
        $manager->persist($citizenProject2);
        $manager->persist($citizenProject3);
        $manager->persist($citizenProject4);
        $manager->persist($citizenProject5);
        $manager->persist($citizenProject6);
        $manager->persist($citizenProject7);
        $manager->persist($citizenProject8);
        $manager->persist($citizenProject9);

        // Make adherents join citizen projects
        $manager->persist($this->getReference('adherent-3')->administrateCitizenProject($citizenProject1, '2017-10-12 17:25:54'));
        $manager->persist($this->getReference('adherent-7')->administrateCitizenProject($citizenProject3, '2017-10-26 17:08:24'));
        $manager->persist($this->getReference('adherent-7')->administrateCitizenProject($citizenProject4));
        $manager->persist($this->getReference('adherent-7')->administrateCitizenProject($citizenProject5));
        $manager->persist($this->getReference('adherent-2')->followCitizenProject($citizenProject1));
        $manager->persist($this->getReference('adherent-4')->followCitizenProject($citizenProject1));
        $manager->persist($this->getReference('adherent-5')->administrateCitizenProject($citizenProject1));
        $manager->persist($this->getReference('adherent-6')->administrateCitizenProject($citizenProject2));
        $manager->persist($this->getReference('adherent-3')->followCitizenProject($citizenProject4));
        $manager->persist($this->getReference('adherent-3')->followCitizenProject($citizenProject5));
        $manager->persist($this->getReference('adherent-9')->administrateCitizenProject($citizenProject6));
        $manager->persist($this->getReference('adherent-3')->followCitizenProject($citizenProject6));
        $manager->persist($this->getReference('adherent-10')->administrateCitizenProject($citizenProject7));
        $manager->persist($this->getReference('adherent-3')->followCitizenProject($citizenProject7));
        $manager->persist($this->getReference('adherent-3')->administrateCitizenProject($citizenProject3));
        $manager->persist($this->getReference('adherent-9')->followCitizenProject($citizenProject5));
        $manager->persist($this->getReference('adherent-11')->administrateCitizenProject($citizenProject8));
        $manager->persist($this->getReference('adherent-3')->followCitizenProject($citizenProject8));
        $manager->persist($this->getReference('adherent-12')->administrateCitizenProject($citizenProject9));
        $manager->persist($this->getReference('adherent-3')->followCitizenProject($citizenProject9));
        $manager->persist($this->getReference('adherent-11')->followCitizenProject($citizenProject9));

        $manager->flush();
    }

    private function getCitizenProjectFactory(): CitizenProjectFactory
    {
        return $this->container->get('app.citizen_project.factory');
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadCitizenProjectCategoryData::class,
            LoadCitizenProjectSkillData::class,
        ];
    }
}
