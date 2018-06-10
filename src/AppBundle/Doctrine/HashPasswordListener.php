<?php

namespace AppBundle\Doctrine;
use AppBundle\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

/**
 * Created by PhpStorm.
 * User: Jakob
 * Date: 2018-06-10
 * Time: 14:38
 */
class HashPasswordListener implements EventSubscriber
{
    /**
     * @var UserPasswordEncoder $passwordEncoder
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoder $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function prePersist(LifecycleEventArgs $args){
        $entity = $args->getEntity();
        if(!$entity instanceof User){
            return;
        }

        $this->encodePassword($entity);
    }

    public function preUpdate(LifecycleEventArgs $args){
        $entity = $args->getEntity();
        if(!$entity instanceof User){
            return;
        }

        $this->encodePassword($entity);

        $em = $args->getEntityManager();
        $meta = $em->getClassMetadata(get_class($entity));
        $em->getUnitOfWork()->recomputeSingleEntityChangeSet($meta, $entity);
    }


    public function getSubscribedEvents()
    {
        return ['prePersist', 'preUpdate'];
    }


    private function encodePassword(User $entity)
    {
        $passwordEncoded = $this->passwordEncoder->encodePassword($entity, $entity->getPlainPassword());
        $entity->setPassword($passwordEncoded);
    }

}