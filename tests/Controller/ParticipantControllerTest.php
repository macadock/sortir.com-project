<?php

namespace App\Tests\Controller;

use App\Entity\Participant;
use App\Repository\ParticipantRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ParticipantControllerTest extends WebTestCase
{
    public function testLogin(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Connexion');
        $this->assertSelectorTextContains('button', 'Se connecter');
        $this->assertCheckboxNotChecked('_remember_me');

        $participantRepository = static::$container->get(ParticipantRepository::class);

        $testUser = $participantRepository->findOneByEmail('mathieu@barriere.me');

        $client->loginUser($testUser);

        $client->request('GET', '/profil');
        $this->assertResponseIsSuccessful();
    }




}
