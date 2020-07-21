<?php


namespace App\Services;

use App\Entity\Enigma;
use App\Entity\Player;
use App\Entity\PlayerEnigma;
use App\Entity\Session;
use App\Entity\Skill;
use App\Entity\Team;
use App\Repository\AdminRepository;
use App\Repository\PlayerEnigmaRepository;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use ErrorException;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PlayerServices
{

    public function __construct()
    {
    }

    public function recupererLaListeDesEnigmes(Player $player, PlayerEnigmaRepository $playerEnigmaRepository, EntityManagerInterface $entityManager)
    {
        $listPlayerEnigma = $playerEnigmaRepository->findPlayerEnigmaAndEnigmaByPlayer($player);

        if ($player->getTeam()->getDeadLine() == null && $player->getDeadLine() == null) {
            $now = new \DateTime();
            //Décalage Horaire de +2h par rapport au 00:00
            $now->add(new \DateInterval('PT2H'));

            $timestmpTempsJeu = $player->getTeam()->getTimeTeam()->getTimestamp();
            $timestmpNow = $now->getTimestamp();

            $timestpdeadline = $timestmpNow + $timestmpTempsJeu;
            $deadLine = new \DateTime();
            $deadLine->setTimestamp($timestpdeadline);
            $player->setDeadLine($deadLine);


            $entityManager->persist($player);
            $entityManager->flush();
        }

        return $listPlayerEnigma;
    }

    public function checkBeforShowEnigma(Player $player, PlayerEnigmaRepository $playerEnigmaRepository, Enigma $enigma, EntityManagerInterface $em)
    {
        // On recupere le tableau de playerEnigma qui lui est lier
        $listPlayerEnigma = $playerEnigmaRepository->findBy(['player' => $player]);
        // On initialise a boolean a false partant du principe qu'il n'as pas cette enigme
        $bool = false;
        // On parcour la liste des playerEnigma
        foreach ($listPlayerEnigma as $playerEnigma) {
            // Si a un moment une des enigmes equivaut l'enigme a la quelle il veut acceder
            if ($playerEnigma->getEnigma() === $enigma) {
                // On passe le boolean a true
                $bool = true;
                // On passe l'enigme en ouverte si elle ne l'etait pas
                // Si elle est deja resolue on genere une exception
                switch ($playerEnigma->getSolved()) {

                    case 0 :
                        $playerEnigma->setSolved(1);
                        $em->persist($playerEnigma);
                        $em->flush();
                        break;

                    case 3 :
                        return new AccessDeniedException("Vous avez deja resolu cette enigme !");
                        break;
                }
                // On stop la boucle
                break;
            }
        }
        // Si le boolean est toujours false, on genere une exeception
        if (!$bool) return new AccessDeniedException("Cette enigme ne fait pas partie de votre session");
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
        }

        // On initialse le nombre de bon charactere a 0
        $goodChara = 0;
        dump($answer);
        dump($enigma->getAnswer());

        try {
            // On parcour chaque charactere de la reponse données par l'utilisateur
            for ($i = 0; $i < strlen($answer); $i++) {
                // Si le charactere de la bonne reponse corespond au charactere données un incremente goodChara de 1
                if ($enigma->getAnswer()[$i] == $answer[$i]) $goodChara += 1;
            }
            dump($goodChara);
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
        ##TODO: Ajouter les admin dans la liste
        // On recupere son groupe pour la demande d'aide
        $team = $player->getTeam();
        // Sur le groupe on recupere la liste des autres joueur qui ont reussi l'enigme
        $listOtherPlayer = $playerRepository->findSuccessfulPlayers($enigma, $team);
        $listAdmin = $adminRepository->findAll();
        foreach ($listAdmin as $admin) {
            $listOtherPlayer[] = $admin;
        }
        return $listOtherPlayer;
    }

    public function isEndOfGame($object): bool
    {
        if ($object->getDeadLine() < new \DateTime()) {
            return true;
        } else return false;
    }

    public function isSessionSynchrone($object) {
        if ($object instanceof Player) {
            return $object->getTeam()->getSession()->getSynchrone();
        } elseif ($object instanceof Team) {
            return $object->getSession()->getSynchrone();
        } elseif ($object instanceof  Session) {
            return $object->getSynchrone();
        } else {
            return null;
        }
    }

    public function setLastChanceTrueAndTeamEnableFalse(Player $player, Team $team, EntityManagerInterface $em) {
        // Si la derniere chance du joueur est a false
        if (!$player->getLastChance()) {
            // On le passe a true
            $player->setLastChance(1);
            // On verifie si le groupe est deja desactiver
            if ($team->isEnable()) {
                $team->setEnable(false);
            }
            $em->persist($player);
            $em->persist($team);
            $em->flush();
            return true;
        } else {
            return false;
        }
    }

    public function EndOfGame(Player $player, FlashBagInterface $flashBag, EntityManagerInterface $em)
    {
        // On recupere son groupe
        $team = $player->getTeam();

        switch ($this->isSessionSynchrone($team)) {

            // Si la session est une session synchrone
            case true :
                // Si le temps du jeux du groupe est bien depasser
                if ($this->isEndOfGame($team)) {
                    return $this->setLastChanceTrueAndTeamEnableFalse($player, $team, $em);
                } else {
                    $flashBag->add('error', "Votre temps de jeu n'est pas encore terminé");
                    return null;
                }
                break;

            // Si la session n'est pas synchrone
            case false :
                // Si le temps du jeux du joueur est bien depasser
                if ($this->isEndOfGame($player)) {
                    return $this->setLastChanceTrueAndTeamEnableFalse($player, $team, $em);
                } else {
                    // C'est que le temps de jeux n'est pas depasser et donc on redirige vers
                    // la liste des enigmes
                    $flashBag->add('error', "Votre temps de jeu n'est pas encore terminé");
                    return null;
                }
                break;

            case null :
                $flashBag->add('error', "Il y a une erreur avec votre session !");
                return null;
                break;
        }
    }
}