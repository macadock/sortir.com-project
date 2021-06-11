<?php


namespace App\SortieService;


use App\Entity\Participant;
use App\Entity\Sortie;
use App\Repository\EtatRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class SortieService
{
    const DRAFT = "Créée";
    const PUBLISH = "Ouverte";
    const CLOSED = 'Clôturée';
    const PROGRESS = 'Activité en cours';
    const FINISHED = 'Passée';
    const CANCELED = 'Annulée';

    private EntityManagerInterface $entityManager;
    private EtatRepository $etatRepository;

    public function __construct(EntityManagerInterface $entityManager, EtatRepository $etatRepository) {
        $this->entityManager = $entityManager;
        $this->etatRepository = $etatRepository;

    }

    public function isSortieOpenForReservation(Sortie $sortie): bool {

        $result = false;

        if ($sortie != null) {


            if ($this->isStatusPublished($sortie) && $this->isInscriptionAllowed($sortie)) {

                $nbParticipants = $sortie->getParticipants()->count();

                if ($nbParticipants < $sortie->getNbInscriptionsMax()) {

                    $result = true;

                }

            }

        }
        return $result;

    }

    public function isParticipantNotReservedSortie(Sortie $sortie, Participant $participant): bool {

        $nbParticipants = $sortie->getParticipants()->count();
        $participants = $sortie->getParticipants();
        $result = true;


        for ($i = 0; $i < $nbParticipants; $i++) {

            if ($participants[$i]->getId() == $participant->getId()) {
                $result = false;
                break;
            }

        }

        return $result;

    }

    public function isSortieExpired(Sortie $sortie = null): bool {

        $today = new DateTime();
        $result = false;

        if ($sortie) {

            if ($sortie->getDateHeureDebut() <= $today) {

                $result = true;

            }

        }

        return $result;

    }

    public function isInscriptionAllowed(Sortie $sortie = null): bool {

        $today = new DateTime();
        $result = false;

        if ($sortie) {
            if ($sortie->getDateLimiteInscription() >= $today) {

                $result = true;

            }

        }

        return $result;

    }

    public function isStatusPublished(Sortie $sortie): bool {

        $STATUS = 'Ouverte';
        $result = false;

        if ($sortie->getEtat()->getLibelle() == $STATUS) {

            $result = true;

        }

        return $result;

    }

    public function isStatusDraft(Sortie $sortie): bool {

        $STATUS = 'Créée';
        $result = false;

        if ($sortie->getEtat()->getLibelle() == $STATUS) {

            $result = true;

        }

        return $result;

    }

    public function isUserTheOwner(Sortie $sortie, Participant $user): bool {

        $result = false;

        if ($sortie->getOrganisateur()->getId() == $user->getId()) {

            $result = true;

        }

        return $result;

    }

    public function isModifiable(Sortie $sortie, Participant $user):bool {

        $result = false;

        if ($this->isStatusDraft($sortie) && $this->isUserTheOwner($sortie, $user)) {

            $result = true;

        }

        return $result;

    }

    public function isAnnulable(Sortie $sortie): bool {

        $result = false;

        if ($sortie->getEtat()->getLibelle() == static::PUBLISH ||
            $sortie->getEtat()->getLibelle() == static::CLOSED ||
            $sortie->getEtat()->getLibelle() == static::PROGRESS) {

            $result = true;

        }



        return $result;

    }

    public function publish(Sortie $sortie, Participant $participant): bool {

        $published = false;

        if ($this->isSortieExpired($sortie) == false) {

            if ($this->isModifiable($sortie, $participant)) {

                $etat = $this->etatRepository->findOneBy(['libelle' => 'Ouverte']);
                $sortie->setEtat($etat);

                $this->entityManager->persist($sortie);
                $this->entityManager->flush();

                $published = true;

            }

        }

        return $published;

    }

    public function inscrire(Sortie $sortie, Participant $participant):bool {

        $inscrit = false;


        if ($this->isSortieOpenForReservation($sortie) && $this->isParticipantNotReservedSortie($sortie, $participant)) {

            $sortie->addParticipant($participant);

            $this->entityManager->persist($sortie);
            $this->entityManager->flush();

            $inscrit = true;

        }

        return $inscrit;

    }

    public function desinscrire(Sortie $sortie, Participant $participant):bool {

        $desinscrit = false;

        if ($this->isParticipantNotReservedSortie($sortie, $participant) == null) {

            $sortie->removeParticipant($participant);
            $this->entityManager->persist($sortie);
            $this->entityManager->flush();

            $desinscrit = true;

        }

        return $desinscrit;

    }

    public function creer(Sortie $sortie, Participant $participant, bool $publish = false):int {

        if($publish) {
            $etat = $this->etatRepository->findOneBy(['libelle' => self::PUBLISH]);
        } else {
            $etat = $this->etatRepository->findOneBy(['libelle' => self::DRAFT]);
        }

        $sortie->setEtat($etat);
        $sortie->setOrganisateur($participant);

        $this->entityManager->persist($sortie);
        $this->entityManager->flush();

        return $sortie->getId();

    }


    public function modifier(Sortie $sortie, Participant $user, bool $publish = false):bool {

        $result = false;

        if ($this->isModifiable($sortie, $user)) {

            $etat = null;
            if($publish) {
                $etat = $this->etatRepository->findOneBy(['libelle' => self::PUBLISH]);
            } else {
                $etat = $this->etatRepository->findOneBy(['libelle' => self::DRAFT]);
            }

            $sortie->setEtat($etat);
            $sortie->setOrganisateur($user);

            $this->entityManager->persist($sortie);
            $this->entityManager->flush();

            $result = true;

        }

        return $result;

    }

    public function annuler(Sortie $sortie, Participant $user): bool {

        $result = false;

        if ($sortie) {

            if ($this->isUserTheOwner($sortie, $user) && $this->isAnnulable($sortie)) {

                $etat = $this->etatRepository->findOneBy(['libelle' => self::CANCELED]);
                $sortie->setEtat($etat);
                $this->entityManager->persist($sortie);
                $this->entityManager->flush();

                $result = true;

            }

        }

        return $result;

    }

    public function supprimer(Sortie $sortie, Participant $user): bool {

        $result = false;

        if ($sortie) {

            if ($this->isUserTheOwner($sortie, $user) && $this->isStatusDraft($sortie)) {

                $this->entityManager->remove($sortie);
                $this->entityManager->flush();

                $result = true;

            }

        }

        return $result;

    }

}