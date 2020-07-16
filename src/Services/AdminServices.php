<?php


namespace App\Services;

use App\Controller\AdminController;
use App\Entity\Admin;
use App\Entity\Enigma;
use App\Entity\Player;
use App\Entity\Session;
use App\Entity\Team;
use App\Form\AdminType;
use App\Form\SessionType;
use App\Form\TeamType;
use App\Repository\AdminRepository;
use App\Repository\PlayerEnigmaRepository;
use App\Repository\SessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class AdminServices
{

    public function __construct()
    {
    }

    public function commencerJeu(Team $team, ObjectManager $entityManager)
    {
        //Débuter le Jeu d'un groupe, pour qu'ils puissent répondre aux énigmes
        $team->setBeginGame(true);
        //Prendre la datetime actuelle + le temps de jeu pour fixer la deadline
        //Si et seulement si la session est SYNCHRONE car sinon le temps de jeu sera dans le joueur à sa connexion
        if ($team->getSession()->getSynchrone() == true){
            $now = new \DateTime();
            //Décalage Horaire de +2h par rapport au 00:00
            $now->add(new \DateInterval('PT2H'));

            $tempsdejeu = $team->getTimeTeam();
            $timestmpTempsJeu = $tempsdejeu->getTimestamp();
            $timestmpNow = $now->getTimestamp();

            $timestpdeadline = $timestmpNow + $timestmpTempsJeu;
            $deadLine = new \DateTime();
            $deadLine->setTimestamp($timestpdeadline);
            $team->setDeadLine($deadLine);
        }
        $entityManager->persist($team);
        $entityManager->flush();
        return $team;
    }

    public function ouvrirGroupe(Team $team, ObjectManager $entityManager)
    {
        //Activer un groupe qui ne l'est pas
        $team->setEnable(true);
        $entityManager->persist($team);
        $entityManager->flush();
        return $team;
    }

    public function activerSession(Session $session, ObjectManager $entityManager)
    {
        //Activer une session qui ne l'est pas
        $session->setEnable(true);
        $entityManager->persist($session);
        $entityManager->flush();
        return $session;
    }

    public function creationSession(Session $session, ObjectManager $entityManager, int $nbrTeam, Team $team)
    {
        $entityManager->persist($session);
        $entityManager->flush();

        $scale = 1;
        if($session->getSynchrone() == false)
        {
            $nbrTeam = 1;
        }

        // Ici on boucle par rapport au nombre de groupe pour en ajouter en fonction du nombre demandé par l'admin
        while ($scale <= $nbrTeam) {
            $groupe = new Team();
            //On met de base la session inactive
            $groupe->setEnable(false);
            //On créer un entier qui servira pour la création de groupe selon le nombre
            $groupe->setNumber($scale);
            //On attribue la session au groupe
            $groupe->setSession($session);
            //On met a false le debut du jeu
            $groupe->setBeginGame(false);

            //Si la session est synchrone on met le temps dans le groupe
            if ($session->getSynchrone() == true) {
                $groupe->setTimeTeam($team->getTimeTeam());
            }
            $entityManager->persist($groupe);
            $scale = $scale + 1;
        }
        $entityManager->flush();
    }

    public function creerStatistiques(Player $player)
    {
        $listeEnigmes = $player->getPlayerEnigmas();

        $statistiques = new ArrayCollection();
        $tempSucces = 0;
        $tempStarSucces = 0;
        $tempStarMax = 0;
        $tempTry = 0;
        $tempOpenned =  0;

        foreach ($listeEnigmes as $enigme)
        {
            //Check si l'enigme est résolue
            if($enigme->getSolved() == 3){
                $tempSucces = $tempSucces+1;
                //Check si l'enigme était dure
                //Incrémentation du compteur
                if($enigme->getEnigma()->getStar())
                {
                    $tempStarSucces = $tempStarSucces+1;
                }
            }
            if ($enigme->getEnigma()->getStar()){
                $tempStarMax = $tempStarMax+1;
            }
            if ($enigme->getSolved() != 0){
                $tempOpenned = $tempOpenned+1;
                $tempTry = $tempTry+$enigme->getTry();
            }
        }
        $statistiques->set("succes", $tempSucces);
        $statistiques->set("starSucces", $tempStarSucces);
        $statistiques->set("openned", $tempOpenned);
        $statistiques->set("try", $tempTry);
        $statistiques->set("starMax", $tempStarMax);
        $statistiques->set("maxEnigmes", $listeEnigmes->count());

        return $statistiques;

    }

    public function creerTaux(ArrayCollection $statistiques)
    {
        $taux = new ArrayCollection();
        if($statistiques->get("try") != 0) {
            $precision = ($statistiques->get("succes") / $statistiques->get("try")) * 100;
        }
        else
        {
            $precision = 0;
        }
        if($statistiques->get("openned") != 0)
        {
            $efficacite = ($statistiques->get("succes") / $statistiques->get("openned")) * 100;
        }
        else{
            $efficacite = 0;
        }

        $reussite = ($statistiques->get("succes")/$statistiques->get("maxEnigmes"))*100;
        $taux->set("rPrecision", $precision);
        $taux->set("rReussite", $reussite);
        $taux->set("rEfficacite",$efficacite);

        return $taux;
    }
    public function createListSkillMax(Player $player)
    {
        $listSkillsTmp = array();
        $listSkillsMax = array();

        $liste = $player->getPlayerEnigmas();


        foreach ($liste as $playerEnigma) {
            foreach ($playerEnigma->getEnigma()->getListSkill() as $skill) {
                $listSkillsTmp[] = $skill;
            }
        }
        foreach ($listSkillsTmp as $skillTmp) {

            if (empty($listSkillsMax)) {
                $listSkillsMax[] = $skillTmp;
            } else {
                $length = count($listSkillsMax);
                for ($i = 0; $i < $length; $i++) {
                    if ($listSkillsMax[$i]->getId() == $skillTmp->getId()) {
                        $listSkillsMax[$i]->setValue($listSkillsMax[$i]->getValue() + $skillTmp->getValue());
                        break;
                    } elseif ($i == $length - 1) $listSkillsMax[] = $skillTmp;
                }
            }
        }

        return $listSkillsMax;
    }

    public function creerListeCompetence(Player $player, PlayerEnigmaRepository $playerEnigmaRepository)
    {
        $playerService = new PlayerServices();
        $listeSkill = $playerService->createTableSkill($player, $playerEnigmaRepository);
        $listeSkillMax = $this->createListSkillMax($player);

        return [$listeSkill,$listeSkillMax];


    }
}