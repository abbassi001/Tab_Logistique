<?php

namespace App\Tests\Controller;

use App\Entity\Colis;
use App\Repository\ColisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ColisControllerTest extends WebTestCase{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $coliRepository;
    private string $path = '/colis/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->coliRepository = $this->manager->getRepository(Colis::class);

        foreach ($this->coliRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Coli index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'coli[codeTracking]' => 'Testing',
            'coli[dimensions]' => 'Testing',
            'coli[poids]' => 'Testing',
            'coli[valeur_declaree]' => 'Testing',
            'coli[date_creation]' => 'Testing',
            'coli[nature_marchandise]' => 'Testing',
            'coli[description_marchandise]' => 'Testing',
            'coli[classification_douaniere]' => 'Testing',
            'coli[expediteur]' => 'Testing',
            'coli[destinaire]' => 'Testing',
            'coli[warehouse]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->coliRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Colis();
        $fixture->setCodeTracking('My Title');
        $fixture->setDimensions('My Title');
        $fixture->setPoids('My Title');
        $fixture->setValeur_declaree('My Title');
        $fixture->setDate_creation('My Title');
        $fixture->setNature_marchandise('My Title');
        $fixture->setDescription_marchandise('My Title');
        $fixture->setClassification_douaniere('My Title');
        $fixture->setExpediteur('My Title');
        $fixture->setDestinaire('My Title');
        $fixture->setWarehouse('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Coli');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Colis();
        $fixture->setCodeTracking('Value');
        $fixture->setDimensions('Value');
        $fixture->setPoids('Value');
        $fixture->setValeur_declaree('Value');
        $fixture->setDate_creation('Value');
        $fixture->setNature_marchandise('Value');
        $fixture->setDescription_marchandise('Value');
        $fixture->setClassification_douaniere('Value');
        $fixture->setExpediteur('Value');
        $fixture->setDestinaire('Value');
        $fixture->setWarehouse('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'coli[codeTracking]' => 'Something New',
            'coli[dimensions]' => 'Something New',
            'coli[poids]' => 'Something New',
            'coli[valeur_declaree]' => 'Something New',
            'coli[date_creation]' => 'Something New',
            'coli[nature_marchandise]' => 'Something New',
            'coli[description_marchandise]' => 'Something New',
            'coli[classification_douaniere]' => 'Something New',
            'coli[expediteur]' => 'Something New',
            'coli[destinaire]' => 'Something New',
            'coli[warehouse]' => 'Something New',
        ]);

        self::assertResponseRedirects('/colis/');

        $fixture = $this->coliRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getCodeTracking());
        self::assertSame('Something New', $fixture[0]->getDimensions());
        self::assertSame('Something New', $fixture[0]->getPoids());
        self::assertSame('Something New', $fixture[0]->getValeur_declaree());
        self::assertSame('Something New', $fixture[0]->getDate_creation());
        self::assertSame('Something New', $fixture[0]->getNature_marchandise());
        self::assertSame('Something New', $fixture[0]->getDescription_marchandise());
        self::assertSame('Something New', $fixture[0]->getClassification_douaniere());
        self::assertSame('Something New', $fixture[0]->getExpediteur());
        self::assertSame('Something New', $fixture[0]->getDestinaire());
        self::assertSame('Something New', $fixture[0]->getWarehouse());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Colis();
        $fixture->setCodeTracking('Value');
        $fixture->setDimensions('Value');
        $fixture->setPoids('Value');
        $fixture->setValeur_declaree('Value');
        $fixture->setDate_creation('Value');
        $fixture->setNature_marchandise('Value');
        $fixture->setDescription_marchandise('Value');
        $fixture->setClassification_douaniere('Value');
        $fixture->setExpediteur('Value');
        $fixture->setDestinaire('Value');
        $fixture->setWarehouse('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/colis/');
        self::assertSame(0, $this->coliRepository->count([]));
    }
}
