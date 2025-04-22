<?php

namespace App\Tests\Controller;

use App\Entity\Transport;
use App\Repository\TransportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class TransportControllerTest extends WebTestCase{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $transportRepository;
    private string $path = '/transport/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->transportRepository = $this->manager->getRepository(Transport::class);

        foreach ($this->transportRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Transport index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'transport[type_transport]' => 'Testing',
            'transport[compagnie_transport]' => 'Testing',
            'transport[numero_vol_navire]' => 'Testing',
            'transport[date_depart]' => 'Testing',
            'transport[lieu_depart]' => 'Testing',
            'transport[date_arrivee]' => 'Testing',
            'transport[lieu_arrivee]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->transportRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Transport();
        $fixture->setType_transport('My Title');
        $fixture->setCompagnie_transport('My Title');
        $fixture->setNumero_vol_navire('My Title');
        $fixture->setDate_depart('My Title');
        $fixture->setLieu_depart('My Title');
        $fixture->setDate_arrivee('My Title');
        $fixture->setLieu_arrivee('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Transport');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Transport();
        $fixture->setType_transport('Value');
        $fixture->setCompagnie_transport('Value');
        $fixture->setNumero_vol_navire('Value');
        $fixture->setDate_depart('Value');
        $fixture->setLieu_depart('Value');
        $fixture->setDate_arrivee('Value');
        $fixture->setLieu_arrivee('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'transport[type_transport]' => 'Something New',
            'transport[compagnie_transport]' => 'Something New',
            'transport[numero_vol_navire]' => 'Something New',
            'transport[date_depart]' => 'Something New',
            'transport[lieu_depart]' => 'Something New',
            'transport[date_arrivee]' => 'Something New',
            'transport[lieu_arrivee]' => 'Something New',
        ]);

        self::assertResponseRedirects('/transport/');

        $fixture = $this->transportRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getType_transport());
        self::assertSame('Something New', $fixture[0]->getCompagnie_transport());
        self::assertSame('Something New', $fixture[0]->getNumero_vol_navire());
        self::assertSame('Something New', $fixture[0]->getDate_depart());
        self::assertSame('Something New', $fixture[0]->getLieu_depart());
        self::assertSame('Something New', $fixture[0]->getDate_arrivee());
        self::assertSame('Something New', $fixture[0]->getLieu_arrivee());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Transport();
        $fixture->setType_transport('Value');
        $fixture->setCompagnie_transport('Value');
        $fixture->setNumero_vol_navire('Value');
        $fixture->setDate_depart('Value');
        $fixture->setLieu_depart('Value');
        $fixture->setDate_arrivee('Value');
        $fixture->setLieu_arrivee('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/transport/');
        self::assertSame(0, $this->transportRepository->count([]));
    }
}
