<?php


namespace App\Services;

use App\Controller\AdminController;
use App\Entity\Admin;
use App\Entity\Session;
use App\Entity\Team;
use App\Form\AdminType;
use App\Form\SessionType;
use App\Form\TeamType;
use App\Repository\AdminRepository;
use App\Repository\SessionRepository;
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

}