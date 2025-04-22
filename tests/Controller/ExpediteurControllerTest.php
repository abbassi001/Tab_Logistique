<?php

namespace App\Tests\Controller;

use App\Entity\Expediteur;
use App\Repository\ExpediteurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ExpediteurControllerTest extends WebTestCase{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $expediteurRepository;
    private string $path = '/expediteur/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->expediteurRepository = $this->manager->getRepository(Expediteur::class);

        foreach ($this->expediteurRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Expediteur index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'expediteur[nom_entreprise_individu]' => 'Testing',
            'expediteur[telephone]' => 'Testing',
            'expediteur[email]' => 'Testing',
            'expediteur[pays]' => 'Testing',
            'expediteur[adresse_complete]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->expediteurRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Expediteur();
        $fixture->setNom_entreprise_individu('My Title');
        $fixture->setTelephone('My Title');
        $fixture->setEmail('My Title');
        $fixture->setPays('My Title');
        $fixture->setAdresse_complete('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Expediteur');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Expediteur();
        $fixture->setNom_entreprise_individu('Value');
        $fixture->setTelephone('Value');
        $fixture->setEmail('Value');
        $fixture->setPays('Value');
        $fixture->setAdresse_complete('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'expediteur[nom_entreprise_individu]' => 'Something New',
            'expediteur[telephone]' => 'Something New',
            'expediteur[email]' => 'Something New',
            'expediteur[pays]' => 'Something New',
            'expediteur[adresse_complete]' => 'Something New',
        ]);

        self::assertResponseRedirects('/expediteur/');

        $fixture = $this->expediteurRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getNom_entreprise_individu());
        self::assertSame('Something New', $fixture[0]->getTelephone());
        self::assertSame('Something New', $fixture[0]->getEmail());
        self::assertSame('Something New', $fixture[0]->getPays());
        self::assertSame('Something New', $fixture[0]->getAdresse_complete());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Expediteur();
        $fixture->setNom_entreprise_individu('Value');
        $fixture->setTelephone('Value');
        $fixture->setEmail('Value');
        $fixture->setPays('Value');
        $fixture->setAdresse_complete('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/expediteur/');
        self::assertSame(0, $this->expediteurRepository->count([]));
    }
}
