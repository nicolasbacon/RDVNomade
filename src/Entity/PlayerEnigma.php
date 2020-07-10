<?php

namespace App\Entity;

use App\Repository\PlayerEnigmaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PlayerEnigmaRepository::class)
 */
class PlayerEnigma
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Player::class, inversedBy="playerEnigmas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $player;

    /**
     * @ORM\ManyToOne(targetEntity=Enigma::class, inversedBy="playerEnigmas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $enigma;

    /**
     * @ORM\Column(type="integer")
     */
    private $try;

    /**
     * @ORM\Column(type="integer")
     */
    private $solved;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getEnigma(): ?Enigma
    {
        return $this->enigma;
    }

    public function setEnigma(?Enigma $enigma): self
    {
        $this->enigma = $enigma;

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

    public function getSolved(): ?int
    {
        return $this->solved;
    }

    public function setSolved(int $solved): self
    {
        $this->solved = $solved;

        return $this;
    }
}
