<?php

namespace KarambolZocoPlugin\Provider;

use KarambolZocoPlugin;
use Doctrine\ORM\EntityManagerInterface;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Karambol\Account\UserInterface;
use KarambolZocoPlugin\Elasticsearch\DocumentInterface;
use KarambolZocoPlugin\Entity\Workgroup;

class WorkgroupServiceProvider implements ServiceProviderInterface
{

  public function register(Application $app) {
    $app['zoco.workgroup'] = new WorkgroupService($app['orm']);
  }

  public function boot(Application $app) {}

}

class WorkgroupService {

  /**
   * @var Doctrine\ORM\EntityManagerInterface
   */
  protected $em;

  /**
   * @param Doctrine\ORM\EntityManagerInterface $em
   */
  public function __construct(EntityManagerInterface $em)
  {
    $this->em = $em;
  }

  public function getOwnsGroup(UserInterface $user)
  {
    return $this->em->getRepository(Workgroup::class)->findBy(['user_id' =>$user->getId()]);
  }

}
