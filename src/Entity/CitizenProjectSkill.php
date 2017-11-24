<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CitizenProjectSkillRepository")
 * @ORM\Table(
 *   name="citizen_project_skills",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="citizen_project_skill_slug_unique", columns="slug")
 *   }
 * )
 *
 * @UniqueEntity("name")
 */
class CitizenProjectSkill extends BaseSkill
{
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="CitizenProjectCategorySkill", mappedBy="skill", indexBy="id", cascade={"all"}, orphanRemoval=true)
     */
    private $categorySkills;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\CitizenProject", mappedBy="skills")
     *
     * @var CitizenProject[]
     */
    private $citizenProjects;

    public function __construct(? string $name = null)
    {
        parent::__construct($name);

        $this->categorySkills = new ArrayCollection();
        $this->citizenProjects = new ArrayCollection();
    }

    public function addCitizenProject(CitizenProject $citizenProjects): void
    {
        if (!$this->citizenProjects->contains($citizenProjects)) {
            $this->citizenProjects->add($citizenProjects);
        }
    }

    public function addCategory(CitizenProjectCategory $category, bool $promotion = false): void
    {
        foreach ($this->categorySkills as $categorySkill) {
            if ($categorySkill->getCategory() === $category && $categorySkill->getPromotion() === $promotion) {
                return;
            }
        }

        $categorySkill = new CitizenProjectCategorySkill($category, $this, $promotion);
        $this->categorySkills->add($categorySkill);
    }

    public function getCategorySkills(): ArrayCollection
    {
        return $this->categorySkills;
    }

    public function setCategorySkills(ArrayCollection $categorySkills): void
    {
        $this->categorySkills = $categorySkills;
    }
}
