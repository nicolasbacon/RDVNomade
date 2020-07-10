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
     * @ORM\Column(type="boolean")
     */
    private $star;

    /**
     * @ORM\ManyToMany(targetEntity=Skill::class)
     */
    private $listSkill;

    /**
     * @ORM\OneToMany(targetEntity=PlayerEnigma::class, mappedBy="enigma")
     */
    private $playerEnigmas;

    public function __construct()
    {
        $this->listSkill = new ArrayCollection();
        $this->playerEnigmas = new ArrayCollection();
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

    /**
     * @return Collection|PlayerEnigma[]
     */
    public function getPlayerEnigmas(): Collection
    {
        return $this->playerEnigmas;
    }

    public function addPlayerEnigma(PlayerEnigma $playerEnigma): self
    {
        if (!$this->playerEnigmas->contains($playerEnigma)) {
            $this->playerEnigmas[] = $playerEnigma;
            $playerEnigma->setEnigma($this);
        }

        return $this;
    }

    public function removePlayerEnigma(PlayerEnigma $playerEnigma): self
    {
        if ($this->playerEnigmas->contains($playerEnigma)) {
            $this->playerEnigmas->removeElement($playerEnigma);
            // set the owning side to null (unless already changed)
            if ($playerEnigma->getEnigma() === $this) {
                $playerEnigma->setEnigma(null);
            }
        }

        return $this;
    }
}
