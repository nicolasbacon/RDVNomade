<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Entity\Session;
use App\Form\AdminType;
use App\Form\SessionType;
use App\Repository\AdminRepository;
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

        return $this->render("admin/homeAdmin.html.twig", ['personne'=>$personne]);
    }

    /**
     * @Route("/new", name="admin_new", methods={"GET","POST"})
     * @param Request $request
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
        if ($this->isCsrfTokenValid('delete'.$admin->getId(), $request->request->get('_token'))) {
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
        return $this->render('admin/gestionSession.html.twig',['personne'=>$personne]);
    }


    /**
     * @Route("/sessionNew", name="session_new_admin", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function newSession(Request $request): Response
    {
        $session = new Session();
        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);
        $session->setEnable(false);
        $personne = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($session);
            $entityManager->flush();

            return $this->redirectToRoute('gestion_session');
        }

        return $this->render('admin/créerSession.html.twig', [
            'session' => $session,
            'form' => $form->createView(),
            'personne'=>$personne,
        ]);
    }








}
