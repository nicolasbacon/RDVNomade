<?php

namespace App\Entity;

use App\Repository\PlayerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=PlayerRepository::class)
 */
class Player extends User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(name="id_player", type="integer")
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
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deadLine;

    /**
     * @ORM\Column(type="boolean")
     */
    private $challenger;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="listPlayer")
     * @ORM\JoinColumn(nullable=false)
     */
    private $team;

    /**
     * @ORM\OneToMany(targetEntity=PlayerEnigma::class, mappedBy="player", orphanRemoval=true)
     */
    private $playerEnigmas;

    /**
     * @ORM\OneToMany(targetEntity=PlayerAsset::class, mappedBy="player", orphanRemoval=true)
     */
    private $listPlayerAsset;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbrRelevanceHelp;

    public function __construct()
    {
        $this->playerEnigmas = new ArrayCollection();
        $this->listPlayerAsset = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->getIdUser();
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

    public function getDeadLine(): ?\DateTimeInterface
    {
        return $this->deadLine;
    }

    public function setDeadLine(?\DateTimeInterface $deadLine): self
    {
        $this->deadLine = $deadLine;

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
            $playerEnigma->setPlayer($this);
        }

        return $this;
    }

    public function removePlayerEnigma(PlayerEnigma $playerEnigma): self
    {
        if ($this->playerEnigmas->contains($playerEnigma)) {
            $this->playerEnigmas->removeElement($playerEnigma);
            // set the owning side to null (unless already changed)
            if ($playerEnigma->getPlayer() === $this) {
                $playerEnigma->setPlayer(null);
            }
        }

        return $this;
    }

    public function getRoles()
    {
        return ["ROLE_PLAYER"];
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return $this->getPseudo();
    }

    public function eraseCredentials(){}

    /**
     * @return Collection|PlayerAsset[]
     */
    public function getPlayerAssets(): Collection
    {
        return $this->listPlayerAsset;
    }

    public function addPlayerAsset(PlayerAsset $listPlayerAsset): self
    {
        if (!$this->listPlayerAsset->contains($listPlayerAsset)) {
            $this->listPlayerAsset[] = $listPlayerAsset;
            $listPlayerAsset->setPlayer($this);
        }

        return $this;
    }

    public function removePlayerAssets(PlayerAsset $listPlayerAsset): self
    {
        if ($this->listPlayerAsset->contains($listPlayerAsset)) {
            $this->listPlayerAsset->removeElement($listPlayerAsset);
            // set the owning side to null (unless already changed)
            if ($listPlayerAsset->getPlayer() === $this) {
                $listPlayerAsset->setPlayer(null);
            }
        }

        return $this;
    }

    public function isChallenger(): ?bool
    {
        return $this->challenger;
    }

    public function setChallenger(bool $challenger): self
    {
        $this->challenger = $challenger;

        return $this;
    }

    public function getNbrRelevanceHelp(): ?int
    {
        return $this->nbrRelevanceHelp;
    }

    public function setNbrRelevanceHelp(int $nbrRelevanceHelp): self
    {
        $this->nbrRelevanceHelp = $nbrRelevanceHelp;

        return $this;
    }

}
