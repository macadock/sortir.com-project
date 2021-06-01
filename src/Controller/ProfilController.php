<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{
    /**
     * @Route("/profil", name="profil_dashboard")
     */
    public function dashboard(): Response
    {
        return $this->render('profil/profil.html.twig', [
            'controller_name' => 'ProfilController',
        ]);
    }

    /**
     * @Route("/profil", name="profil_modifier")
     */
    public function modifier(): Response
    {
        return $this->render('profil/modifier.html.twig', [
            'controller_name' => 'ProfilController',
        ]);
    }
}
