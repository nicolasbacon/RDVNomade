<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TeamRepository::class)
 * @ORM\Table(name="`Team`")
 */
class Team
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $number;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enable;

    /**
     * @ORM\Column(type="time", nullable=true)
     * @var DateTime
     */
    private $timeTeam;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deadLine;

    /**
     * @ORM\Column(type="boolean")
     */
    private $beginGame;

    /**
     * @ORM\OneToMany(targetEntity=Player::class, mappedBy="team")
     */
    private $listPlayer;

    /**
     * @ORM\ManyToOne(targetEntity=Session::class, inversedBy="listTeam")
     * @ORM\JoinColumn(nullable=false)
     */
    private $session;

    public function __construct()
    {
        $this->listPlayer = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

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

    public function getTimeTeam()
    {
        return $this->timeTeam;
    }

    public function setTimeTeam($timeTeam): self
    {
        $this->timeTeam = $timeTeam;

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

    public function getBeginGame(): ?bool
    {
        return $this->beginGame;
    }

    public function setBeginGame(bool $beginGame): self
    {
        $this->beginGame = $beginGame;

        return $this;
    }

    /**
     * @return Collection|Player[]
     */
    public function getListPlayer(): Collection
    {
        return $this->listPlayer;
    }

    public function addListPlayer(Player $listPlayer): self
    {
        if (!$this->listPlayer->contains($listPlayer)) {
            $this->listPlayer[] = $listPlayer;
            $listPlayer->setTeam($this);
        }

        return $this;
    }

    public function removeListPlayer(Player $listPlayer): self
    {
        if ($this->listPlayer->contains($listPlayer)) {
            $this->listPlayer->removeElement($listPlayer);
            // set the owning side to null (unless already changed)
            if ($listPlayer->getTeam() === $this) {
                $listPlayer->setTeam(null);
            }
        }

        return $this;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): self
    {
        $this->session = $session;

        return $this;
    }
}
