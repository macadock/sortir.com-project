<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilPictureType;
use App\Form\ProfilType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProfilController extends AbstractController
{
    /**
     * @Route("/profil", name="profil_dashboard")
     */
    public function dashboard(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, UserPasswordEncoderInterface $encoder): Response
    {
        $participant = $this->getUser();
        $participantForm = $this->createForm(ProfilType::class, $participant);

        $profilePictureForm = $this->createForm(ProfilPictureType::class, $participant);

        $participantForm->handleRequest($request);
        $profilePictureForm->handleRequest($request);

        if ($profilePictureForm->isSubmitted() && $profilePictureForm->isValid()) {

            $image = $profilePictureForm->get('image')->getData();

            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();


                try {
                    $image->move(
                        $this->getParameter('profile_picture_directory'),
                        $newFilename
                    );
                } catch (\Exception $e) {
                    // TODO Gérer erreur sur erreur sur upload
                }

                $participant->setImage($newFilename);
            }

            $entityManager->persist($participant);
            $entityManager->flush();
        }

        if ($participantForm->isSubmitted() && $participantForm->isValid()) {

            $plainPassword = $participant->getPassword();
            $encodedPassword = $encoder->encodePassword($this->getUser(),$plainPassword);
            $participant->setPassword($encodedPassword);

            $entityManager->persist($participant);
            $entityManager->flush();
        }

        return $this->render('profil/profil.html.twig', [
            'participantForm' => $participantForm->createView(),
            'profilePictureForm' => $profilePictureForm->createView(),
        ]);
    }

    /**
     * @Route("/profil/{slug}", name="profil_afficher", requirements={"slug"="[a-zA-Z-]{1,}"})
     */
    public function afficher($slug, ParticipantRepository $participantRepository): Response
    {
        if ($slug == $this->getUser()->getSlug()) {

            return $this->redirectToRoute('profil_dashboard');

        } else {

            $participant = $participantRepository->findOneBy(['slug' => $slug]);

            if ($participant) {

                return $this->render('profil/afficher.html.twig', [
                    'participant' => $participant
                ]);

            }

        }

        return $this->redirectToRoute('sortie_list');

    }

}
