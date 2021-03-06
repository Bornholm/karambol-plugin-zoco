<?php

namespace KarambolZocoPlugin\Provider;

use KarambolZocoPlugin;
use Doctrine\ORM\EntityManagerInterface;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Karambol\Entity\User;
use KarambolZocoPlugin\Elasticsearch\DocumentInterface;
use KarambolZocoPlugin\Entity\Workgroup;
use KarambolZocoPlugin\Entity\ZocoUserExtension;

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

  public function getGroup($id, $slug)
  {
    return $this->em->getRepository(Workgroup::class)->findOneBy([
      'id' => $id,
      'slug' => $slug
      ]);
  }

  public function getGroupById($id)
  {
    return $this->em->getRepository(Workgroup::class)->findOneBy([
      'id' => $id
      ]);
  }

}
