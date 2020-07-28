<?php


namespace App\Services;

use App\Entity\Enigma;
use App\Entity\Player;
use App\Entity\PlayerEnigma;
use App\Entity\Skill;
use App\Repository\AdminRepository;
use App\Repository\EnigmaRepository;
use App\Repository\PlayerEnigmaRepository;
use App\Repository\PlayerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use ErrorException;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PlayerServices
{

    public function __construct()
    {
    }

    public function calculDeadLine(Player $player, EntityManagerInterface $entityManager): \DateTime
    {
        // On recupere la session et le groupe
        $team =  $player->getTeam();
        $session = $team->getSession();

        // Le timestamp de now avec decalage horaire
        $timestmpNow = (new \DateTime())->add(new \DateInterval('PT2H'))->getTimestamp();
        // Le timestamp de la durée du jeux
        $timestpdeadline = $timestmpNow + $session->getGameTime()->getTimestamp();

        if (!$session->getSynchrone()) {
            // On fait un objet DateTime avec le timestamp de fin de jeux
            $deadLine = (new \DateTime())->setTimestamp($timestpdeadline);
            $player->setDeadLine($deadLine);
            $entityManager->persist($player);
        }

        $entityManager->flush();
        return $deadLine;
    }

    public function isPlayerEnigma(Player $player, PlayerEnigmaRepository $playerEnigmaRepository, Enigma $enigma)
    {
        // On recupere le tableau de playerEnigma qui lui est lier
        $listPlayerEnigma = $playerEnigmaRepository->findBy(['player' => $player]);

        // On parcour la liste des playerEnigma
        foreach ($listPlayerEnigma as $playerEnigma) {
            // Si a un moment une des enigmes equivaut l'enigme a la quelle il veut acceder
            if ($playerEnigma->getEnigma() === $enigma) {
                // On passe le boolean a true
                return $playerEnigma;
                break;
            }
        }
        return null;
    }

    public function checkBeforShowEnigma(Player $player, PlayerEnigmaRepository $playerEnigmaRepository, Enigma $enigma, EntityManagerInterface $em)
    {
        if ($this->isEndOfGame($player) && !$player->getLastChance()) return new AccessDeniedException("Vous ne pouvez plus repondre à cette enigme !");

        // Soit on recupere la ligne de PlayerEnigma soit on recupere null si c'est pas son enigme
        $playerEnigma = $this->isPlayerEnigma($player, $playerEnigmaRepository, $enigma);
        // Si on as recuperer null c'est que l'enigme ne lui appartient pas
        if ($playerEnigma == null) return new AccessDeniedException("Cette enigme ne fait pas partie de votre session");

        switch ($playerEnigma->getSolved()) {

            // On passe l'enigme en ouverte si elle ne l'etait pas
            case 0 :
                $playerEnigma->setSolved(1);
                $em->persist($playerEnigma);
                $em->flush();
                break;

            // Si elle est deja resolue on genere une exception
            case 3 :
                return new AccessDeniedException("Vous avez deja resolu cette enigme !");
                break;
        }
        return null;
    }

    public function checkAnswer(Player $player, Enigma $enigma, string $answer, EntityManagerInterface $em, PlayerEnigmaRepository $playerEnigmaRepository)
    {
        // On recupere le user et son enigme
        $playerEnigma = $playerEnigmaRepository->findOneBy(['player' => $player, 'enigma' => $enigma]);

        // Si le joueur est bien une instance de Player
        if ($playerEnigma instanceof PlayerEnigma) {
            // On incremente le nombre de tentative
            $playerEnigma->setTry($playerEnigma->getTry() + 1);
            // Si c'etait sa derniere chance on passe sa derniere chance a false
            if ($this->isEndOfGame($player) && $player->getLastChance()) {
                $player->setLastChance(false);
                $em->persist($player);
                $em->flush();
            }
        }

        // On initialse le nombre de bon charactere a 0
        $goodChara = 0;

        try {
            // On parcour chaque charactere de la reponse données par l'utilisateur
            for ($i = 0; $i < strlen($answer); $i++) {
                // Si le charactere de la bonne reponse corespond au charactere données un incremente goodChara de 1
                if ($enigma->getAnswer()[$i] == $answer[$i]) $goodChara += 1;
            }
        } catch (ErrorException $e) {
            return 1;
        }

        $average = ($goodChara / strlen($enigma->getAnswer())) * 100;

        switch (true) {

            case ($average == 100) :
                $playerEnigma->setSolved(3);
                $em->persist($playerEnigma);
                $em->flush();
                return 3;
                break;

            case ($average >= 50) :
                $playerEnigma->setSolved(2);
                $em->persist($playerEnigma);
                $em->flush();
                return 2;
                break;

            default :
                return 1;
                break;
        }
    }

    public function createTableSkill(Player $player, PlayerEnigmaRepository $playerEnigmaRepository)
    {
        // On instancie deux tableau, un temporaire et un definitif
        $listSkillsTmp = array();
        $listSkillsDef = array();

        // On recherche toutes les enigmes que le player a reussi
        $listPlayerEnigma = $playerEnigmaRepository->findBy(['player' => $player, 'solved' => 3]);

        // On recupere toutes les competences des enigmes qu'il as reussi et on les stock dans le tableau temporaire
        foreach ($listPlayerEnigma as $playerEnigma) {
            foreach ($playerEnigma->getEnigma()->getListSkill() as $skill) {
                //Il faut ABSOLUMENT Cloner la liste et pas la copier car sinon les objets seront les memes,
                //Et ce ne sera pas bon
                $listSkillsTmp[] = clone $skill;
            }
        }
        // On parcourt toutes les competences du tableau temporaire
        foreach ($listSkillsTmp as $skillTmp) {

            // Si le tableau definitif est vide on met la premiere competence dedans
            if (empty($listSkillsDef)) {
                $listSkillsDef[] = $skillTmp;
            } else {
                // Sinon on stock la taille du tableau definitif
                $length = count($listSkillsDef);
                // On le parcourt
                for ($i = 0; $i < $length; $i++) {
                    // Si l'id de la competence sur la quelle on est dans le tableau temporaire
                    // est la meme que celle du tableau definitif
                    if ($listSkillsDef[$i]->getName() === $skillTmp->getName()) {
                        $listSkillsDef[$i]->setValue($listSkillsDef[$i]->getValue() + $skillTmp->getValue());
                        // Et on quitte la boucle pour qu'il arrete de rechercher
                        break;
                        // Sinon on ajoute la competence dans le tableau definitif
                    } elseif ($i == $length - 1) {
                        $listSkillsDef[] = $skillTmp;
                    }
                }
            }
        }
        return $listSkillsDef;
    }

    public function createTableAsset(Player $player, PlayerEnigmaRepository $playerEnigmaRepository)
    {
        // On instancie deux tableau, un temporaire et un definitif
        $listSkillsTmp = array();
        $listSkillsDef = array();

        // On recherche toutes les enigmes que le player a reussi
        $listPlayerEnigma = $playerEnigmaRepository->findBy(['player' => $player, 'solved' => 3]);

        // On recupere toutes les competences des enigmes qu'il as reussi et on les stocke dans le tableau temporaire
        foreach ($listPlayerEnigma as $playerEnigma) {
            foreach ($playerEnigma->getEnigma()->getListSkill() as $skill) {
                $listSkillsTmp[] = $skill;
            }
        }

        // On parcour toutes les competences du tableau temporaire
        foreach ($listSkillsTmp as $skillTmp) {

            // Si le tableau definitif est vide on met la premiere competences dedans
            if (empty($listSkillsDef)) {
                $listSkillsDef[] = $skillTmp;
            } else {
                // Sinon on stocke la taille du tableau definitif
                $length = count($listSkillsDef);
                // On le parcour
                for ($i = 0; $i < $length; $i++) {
                    // Si l'id de la competence sur la quelle on est dans le tableau temporaire
                    // est la meme que celle du tableau definitif
                    if ($listSkillsDef[$i]->getId() == $skillTmp->getId()) {
                        // On additionne les deux valeur
                        $listSkillsDef[$i]->setValue($listSkillsDef[$i]->getValue() + $skillTmp->getValue());
                        // Et on quitte la boucle pour qu'il arrete de rechercher
                        break;
                        // Sinon on ajoute la competence dans le tableau definitif
                    } elseif ($i == $length - 1) $listSkillsDef[] = $skillTmp;
                }
            }
        }

        return $listSkillsDef;
    }

    public function findHigherSkill(Player $player): Skill
    {
        $adminService = new AdminServices();

        $skillMax = null;

        $listSkillMax = $adminService->createListSkillMax($player);

        foreach ($listSkillMax as $skill) {
            if ($skillMax == null) $skillMax = $skill;
            elseif ($skill->getValue() > $skillMax->getValue()) $skillMax = $skill;
        }
        return $skillMax;
    }

    public function createListOtherPlayerForHelp(Player $player, Enigma $enigma, PlayerRepository $playerRepository, AdminRepository $adminRepository)
    {
        // On recupere son groupe pour la demande d'aide
        $team = $player->getTeam();
        // Sur le groupe on recupere la liste des autres joueur qui ont reussi l'enigme
        $listOtherPlayer = $playerRepository->findSuccessfullPlayers($enigma, $team);
        // Si la session est asynchrone on retire ceux qui ont leurs deadline de depasser
        if (!$team->getSession()->getSynchrone()) {
            $dateNow = (new \DateTime())->add(new \DateInterval("PT2H"));
            foreach ($listOtherPlayer as $player) {
                if ($player->getDeadLine() < $dateNow) {
                    // array_search trouve la clef de la valeur corespondante
                    // unset retire l'element du tableau a l'index donné
                    unset($listOtherPlayer[array_search($player, $listOtherPlayer)]);
                }
            }
        }
        $listAdmin = $adminRepository->findAll();
        foreach ($listAdmin as $admin) {
            $listOtherPlayer[] = $admin;
        }

        return $listOtherPlayer;
    }

    public function isEndOfGame(Player $object)
    {
        $date = new \DateTime();
        $date->add(new \DateInterval("PT2H"));

        if ($object->getDeadLine() != null) {
            try {
                if ($object->getDeadLine() < $date) {
                    return true;
                } else return false;
            } catch (\Exception $e) {
            }
        } else if ($object->getTeam()->getDeadLine() != null) {
            if ($object->getTeam()->getDeadLine() < $date) {
                return true;
            } else return false;
        }

        return null;
    }

    public function EndOfGame(Player $player, FlashBagInterface $flashBag, EntityManagerInterface $em, EnigmaRepository $enigmaRepository)
    {
        switch ($this->isEndOfGame($player)) {

            // Si le temps du jeux est bien depasser
            case true :
                // Si le deadLine du joueur est null c'est que c'est une session synchrone
                if ($player->getDeadLine() == null) {
                    // On met donc le groupe en enable false
                    $team = $player->getTeam();
                    if ($team->isEnable()) {
                        $team->setEnable(false);
                        $em->persist($team);
                        $em->flush();
                    }
                }
                // On recupere toute les enigmes que le joueur n'a pas reussi
                // pour savoir si on le redirige vers la page dernière chance
                $listEnigmaNotSolved = new ArrayCollection($enigmaRepository->findEnigmasNotSolved($player));
                // Si le lastChance du joueur est a true
                // et que la liste des enigmes non reussi n'est pas vide
                // alors on renvoie true pour qu'il soit rediriger vers la page derniere chance
                if ($player->getLastChance() && !$listEnigmaNotSolved->isEmpty()) return true;
                else return false;
                break;

            // Si le temps du jeux du joueur n'est pas depasser
            case false :
                // On redirige vers la liste des enigmes
                $flashBag->add('danger', "Votre temps de jeu n'est pas encore terminé");
                return null;
                break;

            // Si la fonction isEndOfGame renvoie null c'est qu'il y a une erreur
            case null :
                $flashBag->add('danger', "Il y a une erreur avec votre session !");
                return null;
                break;
        }
        return null;
    }

    public function calculHelp(Player $connectedPlayer, ?Player $playerAskRecevedHelp, int $acceptHelp, int $relevanceHelp, EntityManagerInterface $entityManager)
    {
        $connectedPlayer->setNbrAskHelp($connectedPlayer->getNbrAskHelp() + 1);
        $entityManager->persist($connectedPlayer);
        // Si la personne a qui il a demander de l'aide est bien un joueur
        if ($playerAskRecevedHelp instanceof Player) {
            $playerAskRecevedHelp->setNbrAskReceivedHelp($playerAskRecevedHelp->getNbrAskReceivedHelp() + 1);
            $entityManager->persist($playerAskRecevedHelp);
            // Si il a accepter de donné de l'aide
            if ($acceptHelp > 0) {
                $playerAskRecevedHelp->setNbrAcceptHelp($playerAskRecevedHelp->getNbrAcceptHelp() + 1);
                $entityManager->persist($playerAskRecevedHelp);
                // Si sa reponse etait pertinante
                if ($relevanceHelp > 0) {
                    $playerAskRecevedHelp->setNbrRelevanceHelp($playerAskRecevedHelp->getNbrRelevanceHelp() + 1);
                    $entityManager->persist($playerAskRecevedHelp);
                }
            }
        }
        $entityManager->flush();
    }

    public function challenge(Player $connectedPlayer, EntityManagerInterface $em, EnigmaRepository $enigmaRepository): Enigma {

        // On ajoute l'attribut challenger au joueur
        $connectedPlayer->setChallenger(true);
        $em->persist($connectedPlayer);
        $em->flush();

        // On recupere le groupe et la session
        $team = $connectedPlayer->getTeam();
        $session = $team->getSession();

        // On instancie la liste qui contiendras les enigmes
        $listAllEnigma = new ArrayCollection();

        // Si c'est une session synchrone il faut regarder les enigmes non resolue par le groupe
        if ($session->getSynchrone()) {
            // On recupere les joueurs du groupe
            $listPlayer = $team->getListPlayer();
            // On recupere ensuite la liste des enigmes que le joueur a reussi
            $listEnigmaSolved = new ArrayCollection($enigmaRepository->findEnigmasSolved($connectedPlayer));
            // On parcour tous les joueurs
            foreach ($listPlayer as $player) {
                // Sur chaque joueur on recupere un tableau des enigmes non reussi
                $listEnigmaNotSolved = $enigmaRepository->findEnigmasNotSolved($player);
                // On parcour chaqu'une des enigmes
                foreach ($listEnigmaNotSolved as $enigma) {
                    // Si le tableau des enigmes reussi par le joueur
                    // et que le tableau final ne contient pas deja cette enigme
                    // alors on l'ajoute dedans
                    if (!$listEnigmaSolved->contains($enigma) && !$listAllEnigma->contains($enigma)) $listAllEnigma->add($enigma);
                }
            }
            if ($listAllEnigma->isEmpty()) {
                $listAllEnigma = $this->getEgnimaSolved($enigmaRepository,$connectedPlayer,$listAllEnigma);
            }
            // Sinon si la sesion est asynchrone
        } else {
            $listAllEnigma = $this->getEgnimaSolved($enigmaRepository,$connectedPlayer,$listAllEnigma);
        }
        return $listAllEnigma[0];
    }

    public function getEgnimaSolved(EnigmaRepository $enigmaRepository, Player $player, ArrayCollection $listAllEnigma): ArrayCollection {
        // On recupere toutes les enigmes que le joueur n'as pas reussi
        $listEnigma = $enigmaRepository->findEnigmasNotSolved($player);
        // On parcour chaqu'une des enigmes
        foreach ($listEnigma as $enigma) {
            // Si elle ne sont pas presente dans le tableau final
            // alors on les ajoutent
            if (!$listAllEnigma->contains($enigma)) $listAllEnigma->add($enigma);
        }
        return $listAllEnigma;
    }

    public function recupererDeadLine(Player $player): \DateTimeInterface
    {
        $time = null;
        if ($player->getDeadLine() != null) {
            // Soit dans le joueur
            $time = $player->getDeadLine();
        } else if ($player->getTeam()->getDeadLine() != null) {
            // Soit dans le groupe
            $time = $player->getTeam()->getDeadLine();
        }
        return $time;
    }

    public function recupererDateChallenge(Player $player): \DateTime
    {
        $timeChallenge = $player->getTeam()->getSession()->getTimeAlert();
        $deadLine = $this->recupererDeadLine($player);
        return (new \DateTime())->setTimestamp($deadLine->getTimestamp() - $timeChallenge->getTimestamp());
    }
}