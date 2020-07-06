<?php

namespace App\Entity;

use App\Repository\PlayerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PlayerRepository::class)
 */
class Player extends User
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
    private $photo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $descByAdmin;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $timePlayer;

    /**
     * @ORM\Column(type="boolean")
     */
    private $lastChance;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $rSuccess;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $rPrecision;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $rHelp;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbrAskHelp;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbrAskReceivedHelp;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbrAcceptHelp;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="listPlayer")
     * @ORM\JoinColumn(nullable=false)
     */
    private $team;

    /**
     * @ORM\ManyToMany(targetEntity=Asset::class)
     */
    private $listAsset;

    /**
     * @ORM\ManyToMany(targetEntity=Enigma::class)
     */
    private $listEnigma;

    public function __construct()
    {
        $this->listAsset = new ArrayCollection();
        $this->listEnigma = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getDescByAdmin(): ?string
    {
        return $this->descByAdmin;
    }

    public function setDescByAdmin(?string $descByAdmin): self
    {
        $this->descByAdmin = $descByAdmin;

        return $this;
    }

    public function getTimePlayer()
    {
        return $this->timePlayer;
    }

    public function setTimePlayer($timePlayer): self
    {
        $this->timePlayer = $timePlayer;

        return $this;
    }

    public function getLastChance(): ?bool
    {
        return $this->lastChance;
    }

    public function setLastChance(bool $lastChance): self
    {
        $this->lastChance = $lastChance;

        return $this;
    }

    public function getRSuccess(): ?int
    {
        return $this->rSuccess;
    }

    public function setRSuccess(?int $rSuccess): self
    {
        $this->rSuccess = $rSuccess;

        return $this;
    }

    public function getRPrecision(): ?int
    {
        return $this->rPrecision;
    }

    public function setRPrecision(?int $rPrecision): self
    {
        $this->rPrecision = $rPrecision;

        return $this;
    }

    public function getRHelp(): ?int
    {
        return $this->rHelp;
    }

    public function setRHelp(?int $rHelp): self
    {
        $this->rHelp = $rHelp;

        return $this;
    }

    public function getNbrAskHelp(): ?int
    {
        return $this->nbrAskHelp;
    }

    public function setNbrAskHelp(int $nbrAskHelp): self
    {
        $this->nbrAskHelp = $nbrAskHelp;

        return $this;
    }

    public function getNbrAskReceivedHelp(): ?int
    {
        return $this->nbrAskReceivedHelp;
    }

    public function setNbrAskReceivedHelp(int $nbrAskReceivedHelp): self
    {
        $this->nbrAskReceivedHelp = $nbrAskReceivedHelp;

        return $this;
    }

    public function getNbrAcceptHelp(): ?int
    {
        return $this->nbrAcceptHelp;
    }

    public function setNbrAcceptHelp(int $nbrAcceptHelp): self
    {
        $this->nbrAcceptHelp = $nbrAcceptHelp;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @return Collection|Asset[]
     */
    public function getListAsset(): Collection
    {
        return $this->listAsset;
    }

    public function addListAsset(Asset $listAsset): self
    {
        if (!$this->listAsset->contains($listAsset)) {
            $this->listAsset[] = $listAsset;
        }

        return $this;
    }

    public function removeListAsset(Asset $listAsset): self
    {
        if ($this->listAsset->contains($listAsset)) {
            $this->listAsset->removeElement($listAsset);
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
