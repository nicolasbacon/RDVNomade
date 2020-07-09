<?php

namespace App\Controller;

use App\Entity\Enigma;
use App\Entity\Player;
use App\Entity\Session;
use App\Entity\Team;
use App\Form\AnswerType;
use App\Form\PlayerType;
use App\Repository\EnigmaRepository;
use App\Repository\PlayerRepository;
use App\Repository\SessionRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/player")
 */
class PlayerController extends AbstractController
{
    /**
     * @Route("/login", name="login_player", methods={"GET","POST"})
     */
    public function login(Request $request)
    {
        return $this->render('player/login.html.twig', []);
    }

    /**
     * @Route("/logout", name="logout_player")
     */
    public function logout()
    {
        $this->addFlash('sucess', 'Mauvais mot de passe !');
    }

    /**
     * @Route("/", name="player_index", methods={"GET"})
     */
    public function index(PlayerRepository $playerRepository): Response
    {
        return $this->render('player/index.html.twig', [
            'players' => $playerRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="player_new", methods={"GET","POST"})
     */
    public function new(Request $request, SessionRepository $sessionRepository, UserPasswordEncoderInterface $encoder): Response
    {
        ##TODO : recuperer la session active et le groupe actif

        $player = new Player();
        $form = $this->createForm(PlayerType::class, $player);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Recherche une session active
            $session = $sessionRepository->findOneBy(['enable' => true]);
            if ($session == null) {
                $this->addFlash('error', 'Aucune session active');
                return $this->redirectToRoute('player_new');
            }
            $teams = $session->getListTeam();

            // Parcoure toutes les teams pour trouver celle qui est active
            foreach ($teams as $team) {
                if ($team->isEnable() == true) {

                    // Si trouve une team active alors attribut cette team au player
                    $player->setTeam($team);
                    // Attribut les memes enigmes de la session au joueur
                    $player->setListEnigma($session->getListEnigma());
                    // Encryption du mot de passse
                    $hashed = $encoder->encodePassword($player, '123');
                    $player->setPassword($hashed);
                    // Initialise sa derniere chance a faux
                    $player->setLastChance(false);
                    // Initialise son nombre de demande d'aide a 0
                    $player->setNbrAskHelp(0);
                    // Initialise son nombre d'aide acceptter a 0
                    $player->setNbrAcceptHelp(0);
                    // Initialise son nombre de demande d'aide recu a 0
                    $player->setNbrAskReceivedHelp(0);

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
                }
            }

            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($player);
            $entityManager->flush();

            return $this->redirectToRoute('player_index');
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
     */
    public function delete(Request $request, Player $player): Response
    {
        if ($this->isCsrfTokenValid('delete'.$player->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($player);
            $entityManager->flush();
        }

        return $this->redirectToRoute('player_index');
    }

    /**
     * @Route("/list", name="player_list_enigmas", methods={"GET"})
     */
    public function listEnigmas(): Response
    {
        $player = $this->getUser();

        return $this->render('player/listEnigmas.html.twig', [
            'enigmas' => $player->getListEnigma(),
        ]);
    }

    /**
     * @Route("/enigma/{id}", name="player_show_enigma", methods={"GET","POST"})
     */
    public function showEnigma(Request $request, Enigma $enigma): Response
    {
        $listEnigma = $this->getUser()->getListEnigma();

        if (!$listEnigma->contains($enigma)) throw $this->createNotFoundException("Cette enigme ne fait pas parti de votre session!");

        $form = $this->createForm(AnswerType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $answer = $form->get('answer')->getData();
            if ($answer == $enigma->getAnswer()) {
                return $this->render('enigma/goodAnswer.html.twig', [
                   'enigma' => $enigma,
                ]);
            }
            else return $this->render('enigma/wrongAnswer.html.twig', [
                'enigma' => $enigma,
            ]);
        }

        return $this->render('player/showEnigma.html.twig', [
                'enigma' => $enigma,
                'form' => $form->createView(),
            ]);
    }
}
