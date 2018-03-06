<?php
// src/Acme/SearchBundle/EventListener/SearchIndexerSubscriber.php
namespace TipsBundle\Events;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
// for Doctrine 2.4: Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use TipsBundle\Entity\Categoria;
use TipsBundle\Entity\Keyword;

class Listener implements EventSubscriber
{

    private $normalizator;

    public function __construct($nor)
    {
        $this->normalizator=$nor;
    }

    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'preUpdate',
        );
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->index($args);
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $this->index($args);
    }

    public function index(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        // perhaps you only want to act on some "Product" entity
        if ($entity instanceof Categoria or $entity instanceof Keyword) 
        {
            
            $slug=$this->normalizator->calculate_slug($entity->getTitulo());

            $entity->setSlug($slug);
        }
    }
}


?>