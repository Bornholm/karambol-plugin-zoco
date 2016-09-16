<?php

namespace KarambolZocoPlugin\Provider;

use KarambolZocoPlugin;
use Doctrine\ORM\EntityManagerInterface;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Karambol\Entity\User;
use KarambolZocoPlugin\Elasticsearch\DocumentInterface;
use KarambolZocoPlugin\Entity\TenderPin;

class TenderPinServiceProvider implements ServiceProviderInterface
{

  public function register(Application $app) {
    $app['zoco.tender_pin'] = new TenderPinService($app['orm']);
  }

  public function boot(Application $app) {}

}

class TenderPinService {

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

  public function pin(User $user, $tender) {

    if($this->hasPin($user, $tender)) return $this;

    $identity = $this->getTenderIdentity($tender);

    $pin = new TenderPin();
    $pin->setUser($user);
    $pin->setTenderId($identity['id']);
    $pin->setTenderType($identity['type']);

    $this->em->persist($pin);
    $this->em->flush();

    return $this;

  }

  public function unpin(User $user, $tender) {

    if(!$this->hasPin($user, $tender)) return true;

    $identity = $this->getTenderIdentity($tender);

    $pin = $this->em->getRepository(TenderPin::class)->findOneBy([
      'user' => $user->getId(),
      'tenderId' => $identity['id'],
      'tenderType' => $identity['type']
    ]);

    $this->em->remove($pin);
    $this->em->flush();

    return $this;

  }

  public function getUserPins(User $user) {
    return $this->em->getRepository(TenderPin::class)
      ->findByUser($user->getId())
    ;
  }

  public function hasPin(User $user, $tender) {

    $identity = $this->getTenderIdentity($tender);

    $qb = $this->em->getRepository(TenderPin::class)->createQueryBuilder('p');
    $qb->select('count(p)')
      ->where($qb->expr()->andX(
        $qb->expr()->eq('p.user', $user->getId()),
        $qb->expr()->eq('p.tenderType', $qb->expr()->literal($identity['type'])),
        $qb->expr()->eq('p.tenderId', $qb->expr()->literal($identity['id']))
      ))
    ;

    return $qb->getQuery()->getSingleScalarResult() > 0;

  }

  public function havePins(User $user, array $tenders) {

    $map = [];
    $pins = $this->em->getRepository(TenderPin::class)->findBy([
      'user' => $user->getId()
    ]);

    foreach($tenders as $tender) {
      $identity = $this->getTenderIdentity($tender);
      $map[$identity['type'].'/'.$identity['id']] = false;
    }

    foreach($pins as $pin) {
      $map[$pin->getTenderType().'/'.$pin->getTenderId()] = true;
    }

    return $map;

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
