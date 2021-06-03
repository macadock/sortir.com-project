<?php

namespace App\Tests\Controller;

use App\Repository\ParticipantRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class ProfilControllerTest extends WebTestCase
{
    public function testProfilePageIsProtected(): void
    {
        $client = static::createClient();

        $participantRepository = static::$container->get(ParticipantRepository::class);
        $testUser = $participantRepository->findOneByEmail('mathieu@barriere.me');
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/profil');

        $this->assertResponseIsSuccessful();
        $this->assertPageTitleContains('Profil');
        $this->assertFormValue('form', 'profil[prenom]', $testUser->getPrenom());
    }

    public function testProfilePageUnauthenticatedUser() {

        $client = static::createClient();
        $crawler = $client->request('GET', '/profil');

        $this->assertResponseStatusCodeSame(302);
    }
}
