<?php

namespace App\Tests\Controller;

use App\Entity\Statut;
use App\Repository\StatutRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class StatutControllerTest extends WebTestCase{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $statutRepository;
    private string $path = '/statut/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->statutRepository = $this->manager->getRepository(Statut::class);

        foreach ($this->statutRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Statut index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'statut[id_status]' => 'Testing',
            'statut[date_status]' => 'Testing',
            'statut[localisation]' => 'Testing',
            'statut[colis]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->statutRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Statut();
        $fixture->setId_status('My Title');
        $fixture->setDate_status('My Title');
        $fixture->setLocalisation('My Title');
        $fixture->setColis('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Statut');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Statut();
        $fixture->setId_status('Value');
        $fixture->setDate_status('Value');
        $fixture->setLocalisation('Value');
        $fixture->setColis('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'statut[id_status]' => 'Something New',
            'statut[date_status]' => 'Something New',
            'statut[localisation]' => 'Something New',
            'statut[colis]' => 'Something New',
        ]);

        self::assertResponseRedirects('/statut/');

        $fixture = $this->statutRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getId_status());
        self::assertSame('Something New', $fixture[0]->getDate_status());
        self::assertSame('Something New', $fixture[0]->getLocalisation());
        self::assertSame('Something New', $fixture[0]->getColis());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Statut();
        $fixture->setId_status('Value');
        $fixture->setDate_status('Value');
        $fixture->setLocalisation('Value');
        $fixture->setColis('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/statut/');
        self::assertSame(0, $this->statutRepository->count([]));
    }
}
