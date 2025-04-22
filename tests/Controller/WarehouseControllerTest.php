<?php

namespace App\Tests\Controller;

use App\Entity\Warehouse;
use App\Repository\WarehouseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class WarehouseControllerTest extends WebTestCase{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $warehouseRepository;
    private string $path = '/warehouse/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->warehouseRepository = $this->manager->getRepository(Warehouse::class);

        foreach ($this->warehouseRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Warehouse index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'warehouse[code_ut]' => 'Testing',
            'warehouse[localisation_warehouse]' => 'Testing',
            'warehouse[description]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->warehouseRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Warehouse();
        $fixture->setCode_ut('My Title');
        $fixture->setLocalisation_warehouse('My Title');
        $fixture->setDescription('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Warehouse');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Warehouse();
        $fixture->setCode_ut('Value');
        $fixture->setLocalisation_warehouse('Value');
        $fixture->setDescription('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'warehouse[code_ut]' => 'Something New',
            'warehouse[localisation_warehouse]' => 'Something New',
            'warehouse[description]' => 'Something New',
        ]);

        self::assertResponseRedirects('/warehouse/');

        $fixture = $this->warehouseRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getCode_ut());
        self::assertSame('Something New', $fixture[0]->getLocalisation_warehouse());
        self::assertSame('Something New', $fixture[0]->getDescription());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Warehouse();
        $fixture->setCode_ut('Value');
        $fixture->setLocalisation_warehouse('Value');
        $fixture->setDescription('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/warehouse/');
        self::assertSame(0, $this->warehouseRepository->count([]));
    }
}
