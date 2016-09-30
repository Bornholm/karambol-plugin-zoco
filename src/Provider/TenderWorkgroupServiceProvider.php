<?php

namespace KarambolZocoPlugin\Provider;

use KarambolZocoPlugin;
use Doctrine\ORM\EntityManagerInterface;
use Silex\Application;
use Silex\ServiceProviderInterface;
use KarambolZocoPlugin\Entity\ZocoUserExtension;
use KarambolZocoPlugin\Elasticsearch\DocumentInterface;
use KarambolZocoPlugin\Entity\TenderWorkgroup;
use KarambolZocoPlugin\Entity\Workgroup;

class TenderWorkgroupServiceProvider implements ServiceProviderInterface
{

  public function register(Application $app) {
    $app['zoco.tender_workgroup'] = new TenderWorkgroupService($app['orm']);
  }

  public function boot(Application $app) {}

}

class TenderWorkgroupService {

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

  public function attachTender(ZocoUserExtension $user, Workgroup $workgroup, $tender) {

    if($this->hasAttached($workgroup, $tender)) return $this;

    $identity = $this->getTenderIdentity($tender);

    $entry = new TenderWorkgroup();
    $entry->setUser($user);
    $entry->setWorkgroup($workgroup);
    $entry->setTenderId($identity['id']);
    $entry->setTenderType($identity['type']);

    $this->em->persist($entry);
    $this->em->flush();

    return $this;

  }

  public function getUserTenders(ZocoUserExtension $user) {
    return $this->em->getRepository(TenderWorkgroup::class)
      ->findByUser($user->getId())
    ;
  }

  public function getTenderWorkgroup(WorkGroup $workgroup)
  {
    return $this->em->getRepository(TenderWorkgroup::class)
      ->findByWorkgroup($workgroup->getId())
    ;
  }

  public function hasAttached(Workgroup $workgroup, $tender) {

    $identity = $this->getTenderIdentity($tender);

    $qb = $this->em->getRepository(TenderWorkgroup::class)->createQueryBuilder('p');
    $qb->select('count(p)')
      ->where($qb->expr()->andX(
        $qb->expr()->eq('p.workgroup', $workgroup->getId()),
        $qb->expr()->eq('p.tenderType', $qb->expr()->literal($identity['type'])),
        $qb->expr()->eq('p.tenderId', $qb->expr()->literal($identity['id']))
      ))
    ;

    return $qb->getQuery()->getSingleScalarResult() > 0;

  }

  protected function getTenderIdentity($tender) {

    $tenderType = null;
    $tenderId = null;

    if(is_array($tender)) {
      $tenderType = $tender['type'];
      $tenderId = $tender['id'];
    } else if($tender instanceof DocumentInterface) {
      $tenderType = $tender->getType();
      $tenderId = $tender->getId();
    }

    if( empty($tenderType) || empty($tenderId) ) {
      throw new \Exception(sprintf('Canno\'t retrieve identity for document "%s" !', $tender));
    }

    return [
      'type' => $tenderType,
      'id' => $tenderId
    ];

  }


}
