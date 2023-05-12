<?php

namespace App\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use App\Entity\Reclamation;
use App\Entity\User;

class ReclamationListener implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        // Check if the entity is a Reclamation object
        if ($entity instanceof Reclamation) {
            $entityManager = $args->getObjectManager();

            // Get the user being reclamated against
            $user = $entity->getIdUserr();

            // Get the number of reclamations against the user
            $numReclamations = $entityManager->getRepository(Reclamation::class)->count([
                'idUserr' => $user,
            ]);

            // If the user has more than 5 reclamations, set their etat to "BLOCKED"
            if ($numReclamations >= 5) {
                $user->setEtat('BLOCKED');
                $entityManager->persist($user);
            }
        }
    }
}
