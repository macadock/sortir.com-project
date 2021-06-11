<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{

    /**
     * @Route("/api/villes", name="api_getVilles", methods={"GET"})
     */
    public function getVilles(VilleRepository $villeRepository):Response {
        $villes = $villeRepository->findAll();

        return $this->json($villes, 200);
    }


    /**
     * @Route("/api/{v}/lieux", name="api_getLieuxByVille", requirements={"ville"="\d"}, methods={"GET"})
     */
    public function getLieuxByVille(VilleRepository $villeRepository, $v): Response
    {
        $ville = $villeRepository->find($v);

        $lieux = $ville->getLieux();

        return $this->json($lieux, 200);

    }

    /**
     * @Route("/api/lieu/infos/{l}", name="api_getInformationsByLieu", requirements={"l"="\d"}, methods={"GET"})
     */
    public function getInformationByLieu(LieuRepository $lieuRepository, $l): Response
    {
        $lieu = $lieuRepository->find($l);

        $objet = new \ArrayObject();

        $objet->append($lieu);
        $objet->append(['code_postal' => $lieu->getVille()->getCodePostal()]);

        return $this->json($objet);
    }

    /**
     * @Route("/api/lieu/create", name="api_postCreateLieu", methods={"POST"})
     */
    public function postCreateLieu(EntityManagerInterface $manager, Request $request, VilleRepository $villeRepository): Response
    {
        $lieu = new Lieu();
        $lieu->setNom($request->request->get('nom'));
        $lieu->setRue($request->request->get('rue'));

        $latitude = $request->request->get('latitude');
        $longitude = $request->request->get('longitude');

        if (!empty($latitude)) {
            $lieu->setLatitude($request->request->get('latitude'));
        }
        if (!empty($longitude)) {
            $lieu->setLongitude($request->request->get('longitude'));
        }

        $idVille = $request->request->get('ville');
        $ville = $villeRepository->find($idVille);
        $lieu->setVille($ville);

        if (!empty($lieu->getNom()) && strlen($lieu->getNom()) > 0 && !empty($lieu->getRue()) && strlen($lieu->getRue()) > 0) {

            $manager->persist($lieu);
            $manager->flush();

            return $this->json($lieu, 200);
        }

        return Response::HTTP_BAD_REQUEST;

    }
}
