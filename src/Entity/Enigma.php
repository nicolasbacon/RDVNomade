<?php

namespace App\Entity;

use App\Repository\EnigmaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EnigmaRepository::class)
 */
class Enigma
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $answer;

    /**
     * @ORM\Column(type="integer")
     */
    private $solved;

    /**
     * @ORM\Column(type="integer")
     */
    private $try;

    /**
     * @ORM\Column(type="boolean")
     */
    private $star;

    /**
     * @ORM\ManyToMany(targetEntity=Skill::class)
     */
    private $listSkill;

    public function __construct()
    {
        $this->listSkill = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): self
    {
        $this->answer = $answer;

        return $this;
    }

    public function getSolved(): ?int
    {
        return $this->solved;
    }

    public function setSolved(int $solved): self
    {
        $this->solved = $solved;

        return $this;
    }

    public function getTry(): ?int
    {
        return $this->try;
    }

    public function setTry(int $try): self
    {
        $this->try = $try;

        return $this;
    }

    public function getStar(): ?bool
    {
        return $this->star;
    }

    public function setStar(bool $star): self
    {
        $this->star = $star;

        return $this;
    }

    /**
     * @return Collection|Skill[]
     */
    public function getListSkill(): Collection
    {
        return $this->listSkill;
    }

    public function addListSkill(Skill $listSkill): self
    {
        if (!$this->listSkill->contains($listSkill)) {
            $this->listSkill[] = $listSkill;
        }

        return $this;
    }

    public function removeListSkill(Skill $listSkill): self
    {
        if ($this->listSkill->contains($listSkill)) {
            $this->listSkill->removeElement($listSkill);
        }

        return $this;
    }
}
