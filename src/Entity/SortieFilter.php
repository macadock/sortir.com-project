<?php

namespace App\Entity;


use DateTime;

class SortieFilter
{
    /**
     * @var Campus
     */
    private $campus;

    /**
     * @var string|null
     */
    private $query;

    /**
     * @var DateTime|null
     */
    private $startDate;

    /**
     * @var DateTime|null
     */
    private $endDate;

    /**
     * @var boolean
     */
    private $isOrganisateur;

    /**
     * @var boolean
     */
    private $isInscrit;

    /**
     * @var boolean
     */
    private $isNotInscrit;

    /**
     * @var boolean
     */
    private $isPassed;

    public function __construct() {
        $this->isOrganisateur = false;
        $this->isInscrit = false;
        $this->isNotInscrit = false;
        $this->isPassed = false;
    }

    /**
     * @return Campus
     */
    public function getCampus(): Campus
    {
        return $this->campus;
    }

    /**
     * @param Campus $campus
     */
    public function setCampus(Campus $campus): void
    {
        $this->campus = $campus;
    }

    /**
     * @return bool
     */
    public function isInscrit(): bool
    {
        return $this->isInscrit;
    }

    /**
     * @param bool $isInscrit
     */
    public function setIsInscrit(bool $isInscrit): void
    {
        $this->isInscrit = $isInscrit;
    }

    /**
     * @return DateTime|null
     */
    public function getEndDate(): ?DateTime
    {
        return $this->endDate;
    }

    /**
     * @param DateTime|null $endDate
     */
    public function setEndDate(?DateTime $endDate): void
    {
        $this->endDate = $endDate;
    }

    /**
     * @return bool
     */
    public function isNotInscrit(): bool
    {
        return $this->isNotInscrit;
    }

    /**
     * @param bool $isNotInscrit
     */
    public function setIsNotInscrit(bool $isNotInscrit): void
    {
        $this->isNotInscrit = $isNotInscrit;
    }

    /**
     * @return bool
     */
    public function isOrganisateur(): bool
    {
        return $this->isOrganisateur;
    }

    /**
     * @param bool $isOrganisateur
     */
    public function setIsOrganisateur(bool $isOrganisateur): void
    {
        $this->isOrganisateur = $isOrganisateur;
    }

    /**
     * @return bool
     */
    public function isPassed(): bool
    {
        return $this->isPassed;
    }

    /**
     * @param bool $isPassed
     */
    public function setIsPassed(bool $isPassed): void
    {
        $this->isPassed = $isPassed;
    }

    /**
     * @return string|null
     */
    public function getQuery(): ?string
    {
        return $this->query;
    }

    /**
     * @param string|null $query
     */
    public function setQuery(?string $query): void
    {
        $this->query = $query;
    }

    /**
     * @return DateTime|null
     */
    public function getStartDate(): ?DateTime
    {
        return $this->startDate;
    }

    /**
     * @param DateTime|null $startDate
     */
    public function setStartDate(?DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }


}
