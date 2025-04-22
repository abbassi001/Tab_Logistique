<?php

namespace App\Tests\Controller;

use App\Entity\DocumentSupport;
use App\Repository\DocumentSupportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DocumentSupportControllerTest extends WebTestCase{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $documentSupportRepository;
    private string $path = '/document/support/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->documentSupportRepository = $this->manager->getRepository(DocumentSupport::class);

        foreach ($this->documentSupportRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('DocumentSupport index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'document_support[nom_fichier]' => 'Testing',
            'document_support[type_document]' => 'Testing',
            'document_support[date_upload]' => 'Testing',
            'document_support[chemin_stockage]' => 'Testing',
            'document_support[colis]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->documentSupportRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new DocumentSupport();
        $fixture->setNom_fichier('My Title');
        $fixture->setType_document('My Title');
        $fixture->setDate_upload('My Title');
        $fixture->setChemin_stockage('My Title');
        $fixture->setColis('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('DocumentSupport');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new DocumentSupport();
        $fixture->setNom_fichier('Value');
        $fixture->setType_document('Value');
        $fixture->setDate_upload('Value');
        $fixture->setChemin_stockage('Value');
        $fixture->setColis('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'document_support[nom_fichier]' => 'Something New',
            'document_support[type_document]' => 'Something New',
            'document_support[date_upload]' => 'Something New',
            'document_support[chemin_stockage]' => 'Something New',
            'document_support[colis]' => 'Something New',
        ]);

        self::assertResponseRedirects('/document/support/');

        $fixture = $this->documentSupportRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getNom_fichier());
        self::assertSame('Something New', $fixture[0]->getType_document());
        self::assertSame('Something New', $fixture[0]->getDate_upload());
        self::assertSame('Something New', $fixture[0]->getChemin_stockage());
        self::assertSame('Something New', $fixture[0]->getColis());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new DocumentSupport();
        $fixture->setNom_fichier('Value');
        $fixture->setType_document('Value');
        $fixture->setDate_upload('Value');
        $fixture->setChemin_stockage('Value');
        $fixture->setColis('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/document/support/');
        self::assertSame(0, $this->documentSupportRepository->count([]));
    }
}
