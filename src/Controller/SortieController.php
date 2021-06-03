<?php

namespace App\Controller;

use App\Entity\SortieFilter;
use App\Form\SortieFilterType;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    /**
     * @Route("/", name="sortie_list")
     */
    public function list(Request $request, SortieRepository $sortieRepository): Response
    {
        $sortieFilter = new SortieFilter();
        $sortieFilter->setCampus($this->getUser()->getCampus());
        $sortieFilter->setIsOrganisateur(true);
        $sortieFilter->setIsInscrit(true);
        $sortieFilter->setIsNotInscrit(true);

        $sortieFilterForm = $this->createForm(SortieFilterType::class, $sortieFilter, [
            'method' => 'GET'
        ]);

        $sortieFilterForm->handleRequest($request);

        $sorties = $sortieRepository->filter($sortieFilter);

        return $this->render('sortie/list.html.twig', [
            'sortieFilterForm' => $sortieFilterForm->createView(),
            'sorties' => $sorties,
        ]);
    }

    /**
     * @Route("/create", name="sortie_create")
     */
    public function create(): Response
    {

        return $this->render('sortie/create.html.twig', [
        ]);
    }
}
