<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Entity\Session;
use App\Entity\Team;
use App\Form\AdminType;
use App\Form\SessionType;
use App\Form\TeamType;
use App\Repository\AdminRepository;
use App\Repository\SessionRepository;
use App\Services\AdminServices;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="admin_index", methods={"GET"})
     * @param AdminRepository $adminRepository
     * @return Response
     */
    public function index(AdminRepository $adminRepository): Response
    {
        return $this->render('admin/index.html.twig', [
            'admins' => $adminRepository->findAll(),
        ]);
    }

    /**
     * @Route("/login", name="login_admin")
     * @param AdminRepository $ar
     * @return RedirectResponse|Response
     */
    public function login(AdminRepository $ar)
    {
        return $this->render("admin/login.html.twig", []);
    }

    /**
     * Symfony gere la route entièrement
     * @Route("/logoutAdmin", name="logout_admin")
     */
    public function logout()
    {
    }

    /**
     * @Route("/homeAdmin", name="home_admin")
     */
    public function homeAdmin()
    {
        $personne = $this->getUser();

        return $this->render("admin/homeAdmin.html.twig", ['personne' => $personne]);
    }

    /**
     * @Route("/new", name="admin_new", methods={"GET","POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function new(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $admin = new Admin();
        $form = $this->createForm(AdminType::class, $admin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashed = $encoder->encodePassword($admin, $admin->getPassword());
            $admin->setPassword($hashed);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($admin);
            $entityManager->flush();

            return $this->redirectToRoute('admin_index');
        }

        return $this->render('admin/new.html.twig', [
            'admin' => $admin,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Admin $admin): Response
    {
        if ($this->isCsrfTokenValid('delete' . $admin->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($admin);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_index');
    }

    /**
     * @Route("/gestionSession", name="gestion_session")
     */
    public function gestionSession()
    {
        $personne = $this->getUser();
        return $this->render('admin/gestionSession.html.twig', ['personne' => $personne]);
    }


    /**
     * @Route("/sessionNew", name="session_new_admin", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function newSession(Request $request): Response
    {
        // Double création de formulaire, un qui récupère les éléments liés à la session
        // l'autre qui récupère les éléments liés à un groupe, en particulier le Temps de Jeu
        $session = new Session();
        $team = new Team();

        $form = $this->createForm(SessionType::class, $session);
        $formTeam = $this->createForm(TeamType::class, $team);

        $form->handleRequest($request);
        $formTeam->handleRequest($request);

        $nbrTeam = $request->get('nbrTeam');

        $session->setEnable(false);
        $personne = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $AdminService = new AdminServices();
            $AdminService->creationSession($session, $entityManager, $nbrTeam, $team);
            return $this->redirectToRoute('gestion_session', ['personne' => $personne,]);
        }
        return $this->render('admin/créerSession.html.twig', [
            'session' => $session,
            'form' => $form->createView(),
            'formTeam' => $formTeam->createView(),
            'personne' => $personne,
        ]);
    }

    /**
     * @Route("/listeSession", name="liste_session", methods={"GET"})
     * @param SessionRepository $sessionRepository
     * @return Response
     */
    public function indexSessions(SessionRepository $sessionRepository): Response
    {
        //Affichage des 10 dernieres sessions de la liste
        $personne = $this->getUser();
        return $this->render('admin/listeSessions.html.twig', [
            'sessions' => $sessionRepository->findTenSessions(),
            'personne' => $personne,
        ]);
    }

    /**
     * @Route("/session/{id}", name="session_show_admin", methods={"GET"})
     * @param Session $session
     * @return Response
     */
    public function showSession(Session $session): Response
    {
        $personne = $this->getUser();
        //On prend la liste qui est dans la session pour avoir tous les groupes
        $groupes = $session->getListTeam();
        return $this->render('admin/showSession.html.twig', [
            'session' => $session,
            'groupes' => $groupes,
            'personne' => $personne,
        ]);
    }

    /**
     * @Route("/activeSession/{id}", name="admin_active_session", methods={"GET"})
     * @param Session $session
     * @return Response
     */
    public function activerSession(Session $session): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $AdminService = new AdminServices();
        $session = $AdminService->activerSession($session, $entityManager);
        return $this->showSession($session);
    }

    /**
     * @Route("/Session/Team/{id}", name="team_show_admin", methods={"GET"})
     * @param Team $team
     * @return Response
     */
    public function showTeam(Team $team): Response
    {
        $personne = $this->getUser();
        $players = $team->getListPlayer();

        return $this->render('admin/gestionGroupe.html.twig', [
            'team' => $team,
            'personne' => $personne,
            'players' => $players,
        ]);
    }


    /**
     * @Route("/activeTeam/{id}", name="admin_active_team", methods={"GET"})
     * @param Team $team
     * @return Response
     */
    public function activerGroupe(Team $team): Response
    {

        $entityManager = $this->getDoctrine()->getManager();
        $AdminService = new AdminServices();
        $team = $AdminService->ouvrirGroupe($team, $entityManager);
        return $this->showTeam($team);
    }

    /**
     * @Route("/BeginTheGame/{id}", name="admin_letsplay_team", methods={"GET"})
     * @param Team $team
     * @return Response
     */
    public function demarrerJeu(Team $team): Response
    {
        $AdminService = new AdminServices();

        $entityManager = $this->getDoctrine()->getManager();

        $team = $AdminService->commencerJeu($team, $entityManager);
        return $this->showTeam($team);
    }

}
