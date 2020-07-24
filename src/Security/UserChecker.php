<?php

namespace App\Security;

use App\Entity\Player;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof Player) {
            return 1;
        }
        if($user instanceof Player)
        {
            if($user->getTeam()->getBeginGame() == false)
            {
                return 1;
            }
        }
        return 0;
    }

    public function checkPostAuth(UserInterface $user)
    {
        $this->checkPreAuth($user);
    }
}