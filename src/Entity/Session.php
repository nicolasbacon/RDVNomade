<?php

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SessionRepository::class)
 */
class Session
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
     * @ORM\Column(type="boolean")
     */
    private $enable;

    /**
     * @ORM\Column(type="boolean")
     */
    private $synchrone;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateEndSession;

    /**
     * @ORM\Column(type="time")
     */
    private $timeAlert;

    /**
     * @ORM\OneToMany(targetEntity=Team::class, mappedBy="session", orphanRemoval=true)
     */
    private $listTeam;

    /**
     * @ORM\ManyToMany(targetEntity=Enigma::class)
     */
    private $listEnigma;

    public function __construct()
    {
        $this->listTeam = new ArrayCollection();
        $this->listEnigma = new ArrayCollection();
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

    public function isEnable(): ?bool
    {
        return $this->enable;
    }

    public function setEnable(bool $enable): self
    {
        $this->enable = $enable;

        return $this;
    }

    public function getSynchrone(): ?bool
    {
        return $this->synchrone;
    }

    public function setSynchrone(bool $synchrone): self
    {
        $this->synchrone = $synchrone;

        return $this;
    }

    public function getDateEndSession()
    {
        return $this->dateEndSession;
    }

    public function setDateEndSession($dateEndSession): self
    {
        $this->dateEndSession = $dateEndSession;

        return $this;
    }

    public function getTimeAlert()
    {
        return $this->timeAlert;
    }

    public function setTimeAlert($timeAlert): self
    {
        $this->timeAlert = $timeAlert;

        return $this;
    }

    /**
     * @return Collection|Team[]
     */
    public function getListTeam(): Collection
    {
        return $this->listTeam;
    }

    public function addListTeam(Team $listTeam): self
    {
        if (!$this->listTeam->contains($listTeam)) {
            $this->listTeam[] = $listTeam;
            $listTeam->setSession($this);
        }

        return $this;
    }

    public function removeListTeam(Team $listTeam): self
    {
        if ($this->listTeam->contains($listTeam)) {
            $this->listTeam->removeElement($listTeam);
            // set the owning side to null (unless already changed)
            if ($listTeam->getSession() === $this) {
                $listTeam->setSession(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Enigma[]
     */
    public function getListEnigma(): Collection
    {
        return $this->listEnigma;
    }

    public function addListEnigma(Enigma $listEnigma): self
    {
        if (!$this->listEnigma->contains($listEnigma)) {
            $this->listEnigma[] = $listEnigma;
        }

        return $this;
    }

    public function removeListEnigma(Enigma $listEnigma): self
    {
        if ($this->listEnigma->contains($listEnigma)) {
            $this->listEnigma->removeElement($listEnigma);
        }

        return $this;
    }
}
