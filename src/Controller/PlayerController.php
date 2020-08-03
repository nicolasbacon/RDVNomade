<?php

namespace App\Controller;

use App\Entity\Enigma;
use App\Entity\Player;
use App\Entity\PlayerEnigma;
use App\Entity\Skill;
use App\Form\AnswerType;
use App\Form\PlayerType;
use App\Repository\AdminRepository;
use App\Repository\EnigmaRepository;
use App\Repository\PlayerEnigmaRepository;
use App\Repository\PlayerRepository;
use App\Repository\SessionRepository;
use App\Services\PlayerServices;
use Doctrine\ORM\EntityManagerInterface;
use Swift_Attachment;
use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route("/player")
 */
class PlayerController extends AbstractController
{
    /**
     * @Route("/login", name="login_player", methods={"GET","POST"})
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        $message = null;

        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error != null) {
            $message = $error->getMessage();
            if ($message === "Bad credentials.") {
                $message = "Pseudo Incorrect";
            }
        }
        return $this->render('player/login.html.twig', [
            'error' => $message,
        ]);
    }

    /**
     * @Route("/logout", name="logout_player")
     */
    public function logout()
    {
    }

    /**
     * @Route("/", name="player_index", methods={"GET"})
     * @param PlayerRepository $playerRepository
     * @return Response
     */
    public function index(PlayerRepository $playerRepository): Response
    {
        return $this->render('player/index.html.twig', [
            'players' => $playerRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="player_new", methods={"GET","POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param SessionRepository $sessionRepository
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function new(Request $request, EntityManagerInterface $entityManager, SessionRepository $sessionRepository, UserPasswordEncoderInterface $encoder): Response
    {
        $player = new Player();

        $form = $this->createForm(PlayerType::class, $player);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            if (!filter_var($player->getMail(), FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('danger', 'Adresse mail Incorrecte');
                return $this->redirectToRoute('player_new');
            }
            // Recherche une session active
            $session = $sessionRepository->findOneBy(['enable' => true]);

            if ($session == null) {
                $this->addFlash('danger', 'Aucune session active');
                return $this->redirectToRoute('player_new');
            }
            $teams = $session->getListTeam();

            // Parcoure toutes les teams pour trouver celle qui est active
            foreach ($teams as $team) {
                if ($team->isEnable() == true) {

                    // Si trouve une team active alors attribut cette team au player
                    $player->setTeam($team);

                    // Encryption du mot de passse
                    $hashed = $encoder->encodePassword($player, '123');
                    $player->setPassword($hashed);
                    // Initialise sa derniere chance a faux
                    $player->setLastChance(true);
                    // Initialise le gout du challenge a faux
                    $player->setChallenger(false);
                    // Initialise son nombre de demande d'aide a 0
                    $player->setNbrAskHelp(0);
                    // Initialise son nombre d'aide acceptter a 0
                    $player->setNbrAcceptHelp(0);
                    // Initialise son nombre de demande d'aide recu a 0
                    $player->setNbrAskReceivedHelp(0);
                    // Initialise son nombre de reponse pertinante a 0
                    $player->setNbrRelevanceHelp(0);

                    //gestion de l'image
                    $brochureFile = $form->get('photo')->getData();
                    if ($brochureFile) {
                        $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();
                        try {
                            $brochureFile->move(
                                $this->getParameter('image_directory'),
                                $newFilename
                            );
                        } catch (FileException $e) {

                        }
                        $player->setPhoto($newFilename);
                    }

                    foreach ($session->getListEnigma() as $enigma) {
                        $playerEnigma = new PlayerEnigma();
                        $playerEnigma->setEnigma($enigma);
                        $playerEnigma->setPlayer($player);
                        $playerEnigma->setSolved(0);
                        $playerEnigma->setTry(0);
                        $entityManager->persist($playerEnigma);
                    }
                }
            }
            if ($player->getTeam() == null) {
                $this->addFlash('danger', 'Aucun groupe actif');
                return $this->redirectToRoute('player_new');
            }

            $entityManager->persist($player);
            $entityManager->flush();

            return $this->redirectToRoute('login_player');
        }


        return $this->render('player/new.html.twig', [
            'player' => $player,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/show", name="player_show", methods={"GET"})
     */
    public function show()
    {

        $player = $this->getUser();
        return $this->render('player/show.html.twig', [
            'player' => $player,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="player_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Player $player
     * @return Response
     */
    public function edit(Request $request, Player $player): Response
    {
        $form = $this->createForm(PlayerType::class, $player);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('player_index');
        }

        return $this->render('player/edit.html.twig', [
            'player' => $player,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="player_delete", methods={"DELETE"})
     * @param Request $request
     * @param Player $player
     * @return Response
     */
    public function delete(Request $request, Player $player): Response
    {
        if ($this->isCsrfTokenValid('delete' . $player->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($player);
            $entityManager->flush();
        }

        return $this->redirectToRoute('player_index');
    }

    /**
     * @Route("/list", name="player_list_enigmas", methods={"GET"})
     * @param PlayerEnigmaRepository $playerEnigmaRepository
     * @param EntityManagerInterface $entityManager
     * @param PlayerServices $playerServices
     * @return Response
     */
    public function listEnigmas(PlayerEnigmaRepository $playerEnigmaRepository, EntityManagerInterface $entityManager, PlayerServices $playerServices): Response
    {
        $lastPage = null;
        $player = $this->getUser();

        if ($player instanceof Player) {
            // Moment du challenge
            $challenge = $player->getTeam()->getSession()->getTimeAlert();
            // Si c'est la premiere fois qu'il se connecte
            // on lui met donc sont deadline
            if ($player->getDeadLine() == null && $player->getTeam()->getDeadLine() == null) {
                $playerServices = new PlayerServices();
                $time = $playerServices->calculDeadLine($player, $entityManager);
            } // On recupere le deadline soit dans le joueur
            else {
                $time = $playerServices->recupererDeadLine($player);
            }
            // On recupere la liste des enigmes du player
            $listPlayerEnigma = $playerEnigmaRepository->findPlayerEnigmaAndEnigmaByPlayer($player);
        } else {
            throw $this->createAccessDeniedException("Vous devez etre un joueur pour acceder a cette page !");
        }

        if (isset($_SERVER['HTTP_REFERER'])) $lastPage = $_SERVER['HTTP_REFERER'];

        return $this->render('player/listEnigmas.html.twig', [
            'listPlayerEnigma' => $listPlayerEnigma,
            'time' => $time,
            'team' => $player->getTeam(),
            'challenge' => $challenge,
            'lastPage' => $lastPage,
        ]);
    }

    /**
     * @Route("/enigma/{id}", name="player_show_enigma", methods={"GET","POST"})
     * @param Request $request
     * @param Enigma $enigma
     * @param PlayerEnigmaRepository $playerEnigmaRepository
     * @param PlayerRepository $playerRepository
     * @param AdminRepository $adminRepository
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function showEnigma(Request $request, Enigma $enigma, PlayerEnigmaRepository $playerEnigmaRepository, PlayerRepository $playerRepository, AdminRepository $adminRepository, EntityManagerInterface $em): Response
    {
        $playerServices = new PlayerServices();

        // On recupere le user connecter
        $player = $this->getUser();

        // On instancie la liste des autres joueur pour l'aide
        $listOtherPlayer = null;

        // Si le user connecté est une instance de player
        if ($player instanceof Player) {
            // On verifie si le joueur accede bien a une enigme qui lui est lié
            // On la passe en ouverte si c'est le cas
            // On verifie egalement que le temps de jeux n'est pas depasser ou qu'il utilise sa derniere chance
            $exception = $playerServices->checkBeforShowEnigma($player, $playerEnigmaRepository, $enigma, $em);
            if ($exception != null) throw $exception;

            // On rempli la liste des joueurs qui ont reussi l'enigme + admin
            $listOtherPlayer = $playerServices->createListOtherPlayerForHelp($player, $enigma, $playerRepository, $adminRepository);

        } else throw $this->createAccessDeniedException("Vous devez etre un joueur !");


        $form = $this->createForm(AnswerType::class);
        $form->handleRequest($request);

        // Traitement du formulaire de reponses
        if ($form->isSubmitted() && $form->isValid()) {
            // On recupere la reponse donnée
            $answer = $form->get('answer')->getData();
            // On verifie si c'est la bonne reponse

            switch ($playerServices->checkAnswer($player, $enigma, $answer, $em, $playerEnigmaRepository)) {

                case 1 :
                    return $this->render('enigma/wrongAnswer.html.twig', [
                        'enigma' => $enigma,
                        'lastChance' => $player->getLastChance(),
                    ]);
                    break;

                case 2 :
                    return $this->render('enigma/nearGoodAnswer.html.twig', [
                        'enigma' => $enigma,
                        'lastChance' => $player->getLastChance(),
                    ]);
                    break;

                case 3 :
                    return $this->render('enigma/goodAnswer.html.twig', [
                        'enigma' => $enigma,
                        'lastChance' => $player->getLastChance(),
                    ]);
                    break;
            }
        }

        return $this->render('player/showEnigma.html.twig', [
            'enigma' => $enigma,
            'form' => $form->createView(),
            'listOtherPlayer' => $listOtherPlayer,
            'team' => $player->getTeam(),
        ]);
    }

    /**
     * @Route("/help/{id}/{acceptHelp}/{relevanceHelp}", name="player_calcul_help", methods={"GET"})
     * @param Player|null $playerAskRecevedHelp
     * @param int $acceptHelp
     * @param int $relevanceHelp
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function Help(?Player $playerAskRecevedHelp, int $acceptHelp, int $relevanceHelp, EntityManagerInterface $entityManager)
    {
        $connectedPlayer = $this->getUser();
        $playerServices = new PlayerServices();

        // Si la personne qui demande de l'aide est bien un joueur
        if ($connectedPlayer instanceof Player) {
            $playerServices->calculHelp($connectedPlayer, $playerAskRecevedHelp, $acceptHelp, $relevanceHelp, $entityManager);
        } else throw $this->createAccessDeniedException("Vous devez etre un joueur !");

        return new Response();
    }

    /**
     * @Route("/showProgress", name="player_show_progress", methods={"GET"})
     * @param PlayerEnigmaRepository $playerEnigmaRepository
     * @return Response
     */
    public function showProgress(PlayerEnigmaRepository $playerEnigmaRepository)
    {
        $player = $this->getUser();
        $playerServices = new PlayerServices();

        if ($player instanceof Player) {
            $listSkills = $playerServices->createTableSkill($player, $playerEnigmaRepository);
            $skillMax = $playerServices->findHigherSkill($player);
        } else {
            throw $this->createAccessDeniedException("Vous devez etre un joueur !");
        }

        return $this->render('player/showProgress.html.twig', [
            'tab' => $listSkills,
            'skillMax' => $skillMax,
            'player' => $player,
        ]);
    }

    /**
     * @Route("/terminated", name="player_terminated", methods={"GET"})
     * @param EntityManagerInterface $em
     * @param EnigmaRepository $enigmaRepository
     * @return Response
     */
    public function terminated(EntityManagerInterface $em, EnigmaRepository $enigmaRepository)
    {
        $player = $this->getUser();
        $playerServices = new PlayerServices();

        if ($player instanceof Player) {

            $resultEOG = $playerServices->EndOfGame($player, $this->get('session')->getFlashBag(), $em, $enigmaRepository);

            if ($resultEOG === true) {

                return $this->render('player/lastChance.html.twig');
            } elseif ($resultEOG === false) return $this->render('player/terminated.html.twig');
            else return $this->redirectToRoute('player_list_enigmas');

        } else {
            throw $this->createAccessDeniedException("Vous devez etre un joueur !");
        }
    }

    /**
     * @Route("/challenge", name="player_challenge", methods={"GET"})
     * @param EntityManagerInterface $em
     * @param EnigmaRepository $enigmaRepository
     * @return Response
     */
    public function challenge(EntityManagerInterface $em, EnigmaRepository $enigmaRepository)
    {
        $player = $this->getUser();
        $playerServices = new PlayerServices();

        // Si c'est bien un Player
        if ($player instanceof Player) {
            $dateChallenge = $playerServices->recupererDateChallenge($player);
            $dateNow = (new \DateTime())->add(new \DateInterval("PT2H"));
            if ($dateNow < $dateChallenge) throw $this->createAccessDeniedException("Ce n'est pas le moment pour un challenge");
            $enigma = $playerServices->challenge($player, $em, $enigmaRepository);
        } else {
            throw $this->createAccessDeniedException("Vous devez etre un joueur !");
        }

        return $this->redirectToRoute('player_show_enigma', [
            'id' => $enigma->getId(),
        ]);
    }


    /**
     * @Route("/mailtoplayer/{id}", name="mail_to_player", methods={"GET"})
     * @param Swift_Mailer $mailer
     * @param Player $player
     * @return Response
     */
    public function mailToPlayer(Swift_Mailer $mailer, Player $player)
    {
        $message = (new \Swift_Message('Hello Email'))
            ->setFrom(['rdv.nomade.session@gmail.com' => 'Resultats RDV NOMADE'])
            ->setTo($player->getMail())
            ->setBody("Oui ici c'est le texte j'espere que ça marchera")
            ->attach(Swift_Attachment::fromPath($this->getParameter('image_directory').'/'.$player->getPhoto()))
        ;
        $mailer->send($message);

        return $this->redirectToRoute('login_player');
    }

    /**
     * @Route("/PDF", name="player_pdf", methods={"POST"})
     */
    public function sendPDF()
    {
        if(!empty($_POST['data'])){
            $data = base64_decode($_POST['data']);
            //$data = $_POST['data'];
            $name = "testPDF";
            $fname = $name."_bell_quote.pdf"; // name the file
            $file = fopen($this->getParameter('image_directory')."/" .$fname, 'w'); // open the file path
            fwrite($file, $data); //save data
            fclose($file);
            dump("Bell Quote saved");
        }
        else {
            throw new \Exception("No Data Sent");
        }

        return new Response();
    }
}