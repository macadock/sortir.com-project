<?php


namespace App\SortieUpdate;


use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;

class SortieUpdate
{

    const DRAFT = "Créée";
    const PUBLISHED = "Ouverte";
    const CLOSED = 'Clôturée';
    const PROGRESS = 'Activité en cours';
    const FINISHED = 'Passée';
    const CANCELED = 'Annulée';

    private $entityManager;
    private $sortieRepository;
    private $etatRepository;
    private $today;

    public function __construct(EntityManagerInterface $entityManager, SortieRepository $sortieRepository, EtatRepository $etatRepository) {
        $this->entityManager = $entityManager;
        $this->sortieRepository = $sortieRepository;
        $this->etatRepository = $etatRepository;
        $this->today = new \DateTime();
    }


    public function update() {

        $this->updateToExpired();
        $this->closeInscriptions();
        $this->inProgress();
        $this->expired();

    }

    private function updateToExpired() {
        $sorties = $this->sortieRepository->sortiesExpired();
        $etat = $this->etatRepository->findOneBy(['libelle' => self::FINISHED]);

        if ($sorties) {
            foreach ($sorties as $sortie) {
                $sortie->setEtat($etat);
                $this->entityManager->persist($sortie);
            }
            $this->entityManager->flush();
        }

    }

    private function closeInscriptions() {
        $sorties = $this->sortieRepository->closedInscriptions();
        $etat = $this->etatRepository->findOneBy(['libelle' => self::CLOSED]);

        if ($sorties) {
            foreach ($sorties as $sortie) {
                $sortie->setEtat($etat);
                $this->entityManager->persist($sortie);
            }
            $this->entityManager->flush();
        }

    }

    private function inProgress() {
        $sorties = $this->sortieRepository->inProgress();
        $inProgress = $this->etatRepository->findOneBy(['libelle' => self::PROGRESS]);
        $finished = $this->etatRepository->findOneBy(['libelle' => self::FINISHED]);

        if ($sorties) {
            foreach ($sorties as $sortie) {

                $interval = 'PT'.$sortie->getDuree().'M';
                $endDate = date_add($this->today, new \DateInterval($interval));

                if ($endDate > $this->today) {
                    $sortie->setEtat($inProgress);
                    $this->entityManager->persist($sortie);
                } else {
                    $sortie->setEtat($finished);
                    $this->entityManager->persist($sortie);
                }

            }
            $this->entityManager->flush();
        }
    }

    private function expired() {
        $sorties = $this->sortieRepository->expired();

        if ($sorties) {
            foreach ($sorties as $sortie) {

                $this->entityManager->remove($sortie);

            }

            $this->entityManager->flush();

        }

    }


}