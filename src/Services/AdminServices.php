<?php


namespace App\Services;

use App\Entity\Player;
use App\Entity\PlayerAsset;
use App\Entity\Session;
use App\Entity\Team;
use App\Repository\AssetRepository;
use App\Repository\PlayerEnigmaRepository;
use App\Repository\SessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;


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

            //Conversion des temps en timestamp pour pouvoir les additionner, afin de créer l'heure de fin de jeu.
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
            //Check si l'enigme est résolue avec le numéro 3
            if($enigme->getSolved() == 3){
                $tempSucces = $tempSucces+1;
                //Check si l'enigme était dure
                //Incrémentation du compteur
                if($enigme->getEnigma()->getStar())
                {
                    $tempStarSucces = $tempStarSucces+1;
                }
            }
            //Comptabilise toutes les enigmes de la session qui avaient une étoile
            if ($enigme->getEnigma()->getStar()){
                $tempStarMax = $tempStarMax+1;
            }
            //On compte celles qui ont étés ouvertes, mais aussi voir si un essai a été fait,
            // car si ouvert mais pas d'essai, le client ne veut pas que ce soit compté
            if ($enigme->getSolved() != 0 && $enigme->getTry() > 0){
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
        // Calcul du taux de précision, avec le nombre de succes sur le nombre d'essais
        if($statistiques->get("try") != 0) {
            $precision = ($statistiques->get("succes") / $statistiques->get("try")) * 100;
        }
        // Pour ne pas avoir un taux null en affichage, on initialise a 0
        else
        {
            $precision = 0;
        }
        //On calcule le nombre de succes sur le nombre d'essais, tout en sachant que dans les "ouverts" ne sont comptés
        //que les enigmes ouvertes+1 essai minimum (Demande du client)
        if($statistiques->get("openned") != 0)
        {
            $efficacite = ($statistiques->get("succes") / $statistiques->get("openned")) * 100;
        }
        else{
            $efficacite = 0;
        }
        //On attribue les valeurs reçues dans notre Array Collection
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

        // on parcourt la liste des enigmes du joueur afin de créer une liste de compétences temporaire
        // (Non additionnées, les doublons restent)
        foreach ($liste as $playerEnigma) {
            foreach ($playerEnigma->getEnigma()->getListSkill() as $skill) {
                $listSkillsTmp[] = $skill;
            }
        }
        //On parcourt la liste des compétences temporaire du dessus
        foreach ($listSkillsTmp as $skillTmp) {
            // Si la liste de Compétences Max que l'on peut avoir n'a pas été initialisée, on le fait ici
            if (empty($listSkillsMax)) {
                $listSkillsMax[] = $skillTmp;
            }
            //On verifie que dans la liste de compétence max, la compétence n'existe pas deja, si elle existe on incrémente la valeur
            //Si la ligne n'existe pas, on créer la ligne.
            else {
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
        //On récupère la liste des compétences avec les valeurs additionnées, et pas de doublons
        $listeSkill = $playerService->createTableSkill($player, $playerEnigmaRepository);
        //On récupère la liste des compétences max de la session pour pouvoir comparer par la suite
        $listeSkillMax = $this->createListSkillMax($player);

        return [$listeSkill,$listeSkillMax];
    }

    public function validerAtout(Player $player, ObjectManager $entityManager)
    {
        $listeAssets = $player->getPlayerAssets();
        foreach ($listeAssets as $atoutPlayer)
        {
            $txt = "atout".strval($atoutPlayer->getId());
            $valueReceived = $_POST[$txt];
            $atoutPlayer->setValue($valueReceived);
            $entityManager->persist($atoutPlayer);
        }
        $entityManager->flush();
    }

    public function ajouterAtout(Player $player, AssetRepository $repo, ObjectManager $entityManager)
    {
        $listeAtoutsRepo = $repo->findAll();
        if($player->getPlayerAssets()->isEmpty())
        {
            foreach($listeAtoutsRepo as $atout)
            {
                $playerAtout = new PlayerAsset();
                $playerAtout->setValue(0);
                $playerAtout->setPlayer($player);
                $playerAtout->setAsset($atout);
                $entityManager->persist($playerAtout);
                $player->addPlayerAsset($playerAtout);
            }
            $entityManager->flush();
        }
        return $player;
    }
}