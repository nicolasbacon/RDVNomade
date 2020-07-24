<?php

namespace App\Entity;

use App\Repository\AdminRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=AdminRepository::class)
 * @UniqueEntity(fields={"pseudo"},
 *     entityClass="App\Entity\User",
 *      message="Ce pseudo est deja utilisé", repositoryMethod="findByUniquePseudo")
 * @UniqueEntity(fields={"mail"},
 *     entityClass="App\Entity\User",
 *      message="Cet email est deja utilisé", repositoryMethod="findByUniqueMail")
 */
class Admin extends User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;


    public function getId(): ?int
    {
        return $this->getIdUser();
    }


    public function getRoles()
    {
        return ["ROLE_ADMIN"];
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return $this->getPseudo();
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }
}
