<?php

namespace App\Security;

use App\Entity\Player;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof Player) {
            return;
        }
        if($user instanceof Player)
        {

            if($user->getTeam()->getBeginGame() == false)
            {
                throw new AuthenticationException("Le groupe n'est pas ouvert");
            }
        }
        return;
    }

    public function checkPostAuth(UserInterface $user)
    {
        $this->checkPreAuth($user);
    }
}