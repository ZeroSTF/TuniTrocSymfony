<?php

namespace App\Test\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/user/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(User::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('User index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'user[email]' => 'Testing',
            'user[pwd]' => 'Testing',
            'user[nom]' => 'Testing',
            'user[prenom]' => 'Testing',
            'user[photo]' => 'Testing',
            'user[numTel]' => 'Testing',
            'user[ville]' => 'Testing',
            'user[valeurFidelite]' => 'Testing',
            'user[role]' => 'Testing',
            'user[salt]' => 'Testing',
            'user[token]' => 'Testing',
            'user[etat]' => 'Testing',
        ]);

        self::assertResponseRedirects('/sweet/food/');

        self::assertSame(1, $this->getRepository()->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new User();
        $fixture->setEmail('My Title');
        $fixture->setPwd('My Title');
        $fixture->setNom('My Title');
        $fixture->setPrenom('My Title');
        $fixture->setPhoto('My Title');
        $fixture->setNumTel('My Title');
        $fixture->setVille('My Title');
        $fixture->setValeurFidelite('My Title');
        $fixture->setRole('My Title');
        $fixture->setSalt('My Title');
        $fixture->setToken('My Title');
        $fixture->setEtat('My Title');

        $this->repository->save($fixture, true);

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('User');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new User();
        $fixture->setEmail('Value');
        $fixture->setPwd('Value');
        $fixture->setNom('Value');
        $fixture->setPrenom('Value');
        $fixture->setPhoto('Value');
        $fixture->setNumTel('Value');
        $fixture->setVille('Value');
        $fixture->setValeurFidelite('Value');
        $fixture->setRole('Value');
        $fixture->setSalt('Value');
        $fixture->setToken('Value');
        $fixture->setEtat('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'user[email]' => 'Something New',
            'user[pwd]' => 'Something New',
            'user[nom]' => 'Something New',
            'user[prenom]' => 'Something New',
            'user[photo]' => 'Something New',
            'user[numTel]' => 'Something New',
            'user[ville]' => 'Something New',
            'user[valeurFidelite]' => 'Something New',
            'user[role]' => 'Something New',
            'user[salt]' => 'Something New',
            'user[token]' => 'Something New',
            'user[etat]' => 'Something New',
        ]);

        self::assertResponseRedirects('/user/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getEmail());
        self::assertSame('Something New', $fixture[0]->getPwd());
        self::assertSame('Something New', $fixture[0]->getNom());
        self::assertSame('Something New', $fixture[0]->getPrenom());
        self::assertSame('Something New', $fixture[0]->getPhoto());
        self::assertSame('Something New', $fixture[0]->getNumTel());
        self::assertSame('Something New', $fixture[0]->getVille());
        self::assertSame('Something New', $fixture[0]->getValeurFidelite());
        self::assertSame('Something New', $fixture[0]->getRole());
        self::assertSame('Something New', $fixture[0]->getSalt());
        self::assertSame('Something New', $fixture[0]->getToken());
        self::assertSame('Something New', $fixture[0]->getEtat());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new User();
        $fixture->setEmail('Value');
        $fixture->setPwd('Value');
        $fixture->setNom('Value');
        $fixture->setPrenom('Value');
        $fixture->setPhoto('Value');
        $fixture->setNumTel('Value');
        $fixture->setVille('Value');
        $fixture->setValeurFidelite('Value');
        $fixture->setRole('Value');
        $fixture->setSalt('Value');
        $fixture->setToken('Value');
        $fixture->setEtat('Value');

        $$this->manager->remove($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/user/');
        self::assertSame(0, $this->repository->count([]));
    }
}
