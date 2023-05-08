<?php

namespace App\Test\Controller;

use App\Entity\Projet;
use App\Repository\ProjetRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProjetControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private ProjetRepository $repository;
    private string $path = '/projet/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Projet::class);

        foreach ($this->repository->findAll() as $object) {
            $this->repository->remove($object, true);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Projet index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'projet[nomProjet]' => 'Testing',
            'projet[description]' => 'Testing',
            'projet[duree]' => 'Testing',
            'projet[prix]' => 'Testing',
        ]);

        self::assertResponseRedirects('/projet/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Projet();
        $fixture->setNomProjet('My Title');
        $fixture->setDescription('My Title');
        $fixture->setDuree('My Title');
        $fixture->setPrix('My Title');

        $this->repository->save($fixture, true);

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Projet');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Projet();
        $fixture->setNomProjet('My Title');
        $fixture->setDescription('My Title');
        $fixture->setDuree('My Title');
        $fixture->setPrix('My Title');

        $this->repository->save($fixture, true);

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'projet[nomProjet]' => 'Something New',
            'projet[description]' => 'Something New',
            'projet[duree]' => 'Something New',
            'projet[prix]' => 'Something New',
        ]);

        self::assertResponseRedirects('/projet/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getNomProjet());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getDuree());
        self::assertSame('Something New', $fixture[0]->getPrix());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Projet();
        $fixture->setNomProjet('My Title');
        $fixture->setDescription('My Title');
        $fixture->setDuree('My Title');
        $fixture->setPrix('My Title');

        $this->repository->save($fixture, true);

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/projet/');
    }
}
