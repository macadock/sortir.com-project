<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\SortieFilter;
use App\Form\SortieFilterType;
use App\Form\SortieType;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\SortieService\SortieService;
use App\SortieUpdate\SortieUpdate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    /**
     * @Route("/", name="sortie_list")
     */
    public function list(Request $request, SortieRepository $sortieRepository, SortieUpdate $sortieUpdate): Response
    {
        $sortieUpdate->update();

        $sortieFilter = new SortieFilter();
        $sortieFilter->setCampus($this->getUser()->getCampus());
        $sortieFilter->setIsOrganisateur(true);
        $sortieFilter->setIsInscrit(true);
        $sortieFilter->setIsNotInscrit(true);
        $sortieFilter->setUser($this->getUser());

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
     * @Route("/sortie/creer", name="sortie_creer")
     */
    public function creer(Request $request, ParticipantRepository $participantRepository, SortieService $sortieService): Response
    {
        $sortie = new Sortie();
        $sortie->setCampusOrganisateur($this->getUser()->getCampus());
        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            $participant = $participantRepository->find($this->getUser()->getId());
            $sortie->setOrganisateur($participant);

            $publish = false;
            if($sortieForm->get('publish')->isClicked()) {
                $publish = true;
            }

            $sortieId = $sortieService->creer($sortie, $participant, $publish);

            if ($sortieId != null) {

                $this->addFlash(
                    'success',
                    'Sortie cr????e'
                );
                return $this->redirectToRoute('sortie_afficher', ['id' => $sortieId]);

            }

        }
        return $this->render('sortie/create.html.twig', [
            'sortieForm' => $sortieForm->createView()
        ]);
    }

    /**
     * @Route("/sortie/{id}/modifier", name="sortie_modifier", requirements={"id"="\d{1,}"})
     */
    public function modifier($id, Request $request, SortieRepository $sortieRepository, LieuRepository $lieuRepository, ParticipantRepository $participantRepository, SortieService $sortieService): Response
    {

        $sortie = $sortieRepository->find($id);
        $participant = $participantRepository->find($this->getUser()->getId());

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieService->isModifiable($sortie, $participant)) {

            if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

                $lieuId = $sortieForm->get('lieu')->getViewData();
                $lieu = $lieuRepository->find($lieuId);
                $sortie->setLieu($lieu);

                $publish = false;
                if ($sortieForm->get('publish')->isClicked()) {
                    $publish = true;
                }

                if ($sortieService->modifier($sortie, $participant, $publish)) {

                    $this->addFlash(
                        'success',
                        'Sortie modifi??e'
                    );
                    return $this->redirectToRoute('sortie_afficher', ['id' => $id]);

                }

                $this->addFlash(
                    'notice',
                    'Erreur pendant la modification'
                );
                return $this->redirectToRoute('sortie_afficher', ['id' => $id]);

            }


            return $this->render('sortie/modifier.html.twig', [
                'sortieForm' => $sortieForm->createView(),
                'id' => $id
            ]);
        }

        $this->addFlash(
            'notice',
            'Sortie non modifiable'
        );

        return $this->redirectToRoute('sortie_afficher', ['id' => $id]);

    }

    /**
     * @Route("/sortie/{id}", name="sortie_afficher", requirements={"id"="\d{1,}"})
     */
    public function afficher($id, SortieRepository $sortieRepository): Response {

        $sortie = $sortieRepository->find($id);

        if ($sortie) {
            return $this->render('sortie/afficher.html.twig', [
                'sortie' => $sortie
            ]);
        }

        throw $this->createNotFoundException();

    }

    /**
     * @Route("/sortie/{id}/inscrire", name="sortie_inscription", requirements={"id"="\d{1,}"})
     */
    public function inscription($id, SortieRepository $sortieRepository, ParticipantRepository $participantRepository, SortieService $sortieService): Response {

        $sortie = $sortieRepository->find($id);
        $participant = $participantRepository->find($this->getUser()->getId());

        if ($sortieService->inscrire($sortie, $participant)) {

            $this->addFlash(
                'success',
                "Vous ??tes inscrit");

            return $this->redirectToRoute('sortie_afficher', ['id' => $id]);

        }

        $this->addFlash(
            'notice',
            'Inscription impossible');

        return $this->redirectToRoute('sortie_afficher', ['id' => $id]);

    }

    /**
     * @Route("/sortie/{id}/desinscrire", name="sortie_desinscription", requirements={"id"="\d{1,}"})
     */
    public function desinscrire($id, SortieRepository $sortieRepository, ParticipantRepository $participantRepository, SortieService $sortieService): Response
    {

        $sortie = $sortieRepository->find($id);
        $participant = $participantRepository->find($this->getUser()->getId());

        if ($sortieService->desinscrire($sortie, $participant)) {

            $this->addFlash(
                'success',
                "Vous ??tes d??sinscrit de cette sortie");

            return $this->redirectToRoute('sortie_list');

        }

        $this->addFlash(
            'notice',
            "Erreur ! Vous n'??tes pas inscrit ?? cette sortie");

        return $this->redirectToRoute('sortie_list');

    }

        /**
         * @Route("/sortie/{id}/publier", name="sortie_publier", requirements={"id"="\d{1,}"})
         */
        public function publier($id, SortieService $sortieService, SortieRepository $sortieRepository, ParticipantRepository $participantRepository): Response {

            $sortie = $sortieRepository->find($id);
            $participant = $participantRepository->find($this->getUser()->getId());

            if ($sortieService->publish($sortie, $participant)) {

                $this->addFlash(
                    'success',
                    'Sortie publi??e !');

                return $this->redirectToRoute('sortie_afficher', ['id' => $id]);

            }

            $this->addFlash(
                'notice',
                'Sortie introuvable');

            return $this->redirectToRoute('sortie_afficher', ['id' => $id]);

        }

        /**
         * @Route("/sortie/{id}/annuler", name="sortie_annuler", requirements={"id"="\d{1,}"})
         */
        public function annuler($id, SortieService $sortieService, SortieRepository $sortieRepository, ParticipantRepository $participantRepository): Response {
            $sortie = $sortieRepository->find($id);
            $participant = $participantRepository->find($this->getUser()->getId());

            if ($sortieService->annuler($sortie, $participant)) {

                $this->addFlash(
                    'success',
                    'La sortie '. $sortie->getNom() .' a ??t?? annul??e !');

                return $this->redirectToRoute('sortie_afficher', ['id' => $id]);

            }

            $this->addFlash(
                'notice',
                "Impossible d'annuler la sortie");

            return $this->redirectToRoute('sortie_afficher', ['id' => $id]);


        }

    /**
     * @Route("/sortie/{id}/supprimer", name="sortie_supprimer", requirements={"id"="\d{1,}"})
     */
    public function supprimer($id, SortieService $sortieService, SortieRepository $sortieRepository, ParticipantRepository $participantRepository): Response {

        $sortie = $sortieRepository->find($id);
        $participant = $participantRepository->find($this->getUser()->getId());

        if ($sortieService->supprimer($sortie, $participant)) {

            $this->addFlash(
                'success',
                'Sortie supprim??e !');

            return $this->redirectToRoute('sortie_list');

        }

        $this->addFlash(
            'notice',
            'Sortie introuvable');

        return $this->redirectToRoute('sortie_afficher', ['id' => $id]);

    }

}
