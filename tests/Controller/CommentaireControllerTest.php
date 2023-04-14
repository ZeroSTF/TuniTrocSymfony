<?php

namespace App\Test\Controller;

use App\Entity\Commentaire;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommentaireControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/c/r/u/d/commentaire/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Commentaire::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Commentaire index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'commentaire[contenu]' => 'Testing',
            'commentaire[date]' => 'Testing',
            'commentaire[likes]' => 'Testing',
            'commentaire[dislikes]' => 'Testing',
            'commentaire[idPost]' => 'Testing',
            'commentaire[idUser]' => 'Testing',
        ]);

        self::assertResponseRedirects('/sweet/food/');

        self::assertSame(1, $this->getRepository()->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Commentaire();
        $fixture->setContenu('My Title');
        $fixture->setDate('My Title');
        $fixture->setLikes('My Title');
        $fixture->setDislikes('My Title');
        $fixture->setIdPost('My Title');
        $fixture->setIdUser('My Title');

        $this->repository->save($fixture, true);

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Commentaire');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Commentaire();
        $fixture->setContenu('Value');
        $fixture->setDate('Value');
        $fixture->setLikes('Value');
        $fixture->setDislikes('Value');
        $fixture->setIdPost('Value');
        $fixture->setIdUser('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'commentaire[contenu]' => 'Something New',
            'commentaire[date]' => 'Something New',
            'commentaire[likes]' => 'Something New',
            'commentaire[dislikes]' => 'Something New',
            'commentaire[idPost]' => 'Something New',
            'commentaire[idUser]' => 'Something New',
        ]);

        self::assertResponseRedirects('/c/r/u/d/commentaire/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getContenu());
        self::assertSame('Something New', $fixture[0]->getDate());
        self::assertSame('Something New', $fixture[0]->getLikes());
        self::assertSame('Something New', $fixture[0]->getDislikes());
        self::assertSame('Something New', $fixture[0]->getIdPost());
        self::assertSame('Something New', $fixture[0]->getIdUser());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Commentaire();
        $fixture->setContenu('Value');
        $fixture->setDate('Value');
        $fixture->setLikes('Value');
        $fixture->setDislikes('Value');
        $fixture->setIdPost('Value');
        $fixture->setIdUser('Value');

        $$this->manager->remove($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/c/r/u/d/commentaire/');
        self::assertSame(0, $this->repository->count([]));
    }
}
