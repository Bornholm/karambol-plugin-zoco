<?php

namespace KarambolZocoPlugin\Controller;

use Karambol\Controller\Controller;
use Karambol\KarambolApp;
use Symfony\Component\HttpFoundation\JsonResponse;
use KarambolZocoPlugin\Entity\Workgroup;
use KarambolZocoPlugin\Entity\ZocoUserExtension;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use KarambolZocoPlugin\Form\Type\WorkgroupType;

class WorkgroupController extends Controller {

  public function mount(KarambolApp $app) {
    $app->get('/zoco/workgroup', [$this, 'listWorkgroup'])->bind('plugins_zoco_workgroup');
    $app->get('/zoco/workgroup/{id}-{slug}/show', [$this, 'showWorkgroup'])
      ->value('id', '/[0-9]+/i')
      ->value('slug', '/[a-z0-9\-]+/i')
      ->bind('plugins_zoco_workgroup_show');
    $app->post('/zoco/workgroup', [$this, 'handleCreateWorkgroupForm'])->bind('plugins_zoco_handle_create_workgroup');
    $app->post('/zoco/workgroup/{workgroupId}/{tenderId}/{tenderType}', [$this, 'addTenderWorkgroup'])->bind('plugins_zoco_add_tender_workgroup');

  }

  public function listWorkgroup() {

    $user = $this->get('user');
    $ext = $user->getExtensionByName('zoco', ZocoUserExtension::class);
    $twig = $this->get('twig');
    $workgroup = new Workgroup();

    $form = $this->getCreateWorkgroupForm($workgroup);

    return $twig->render('plugins/zoco/workgroup/listWorkgroup.html.twig', [
      'workgroups' => $ext->getWorkgroups(),
      'user' => $ext,
      'workgroupForm' => $form->createView()
    ]);

  }

  public function addTenderWorkgroup($workgroupId,$tenderId,$tenderType)
  {
    $user = $this->get('user');
    $ext = $user->getExtensionByName('zoco', ZocoUserExtension::class);
    $twig = $this->get('twig');

    $workgroup = $this->get('zoco.workgroup')->getGroupById($workgroupId);

    $this->get('zoco.tender_workgroup')
      ->attachTender($ext, $workgroup, ['type' => $tenderType, 'id' => $tenderId])
    ;
    return new JsonResponse(['result' => 'OK']);
  }

  public function showWorkgroup($id,$slug) {
    $user = $this->get('user');
    $ext = $user->getExtensionByName('zoco', ZocoUserExtension::class);
    $twig = $this->get('twig');
    $es = $this->get('zoco.elasticsearch');

    $workgroup = $this->get('zoco.workgroup')->getGroup($id, $slug);
    $attachedTenders = $this->get('zoco.tender_workgroup')->getTenderWorkgroup($workgroup);
    $tenders = [];
    if(count($attachedTenders) > 0) {

      $esClient = $es->getClient();
      $params = [ 'body' => [ 'docs' => [] ] ];

      foreach($attachedTenders as $attachedTender) {
        $params['body']['docs'][] = [
          '_index' => 'zoco',
          '_type' => $attachedTender->getTenderType(),
          '_id' => $attachedTender->getTenderId()
        ];
      }

      $results = $esClient->mget($params);

      foreach($results['docs'] as $hit) {
        $tenders[] = $es->createDocumentFromHit($hit);
      }

    }

    return $twig->render('plugins/zoco/workgroup/showWorkgroup.html.twig', [
      'workgroup' => $workgroup,
      'tenders' => $tenders,
      'user' => $ext
    ]);

  }

  public function handleCreateWorkgroupForm(){

    $request = $this->get('request');
    $workgroup = new Workgroup();
    $form = $this->getCreateWorkgroupForm($workgroup);
    $user = $this->get('user');
    $ext = $user->getExtensionByName('zoco', ZocoUserExtension::class);
    $form->handleRequest($request);

    if( !$form->isValid() ) {
      die('invalide mon pote');
      return $this->redirect($urlGen->generate('plugins_zoco_workgroup'));
    }
    $workgroup->setSlug($workgroup->getName());
    $workgroup->setUser($ext);
    $ext->addWorkgroup($workgroup);
    $orm = $this->get('orm');
    $orm->persist($workgroup);
    $orm->persist($ext);
    $orm->flush();

    $urlGen = $this->get('url_generator');
    return $this->redirect($urlGen->generate('plugins_zoco_workgroup'));
  }

  protected function getCreateWorkgroupForm(Workgroup $workgroup) {

    $formFactory = $this->get('form.factory');
    $urlGen = $this->get('url_generator');

    $formBuilder = $formFactory->createBuilder(WorkgroupType::class, $workgroup);
    $action = $urlGen->generate('plugins_zoco_workgroup');

    return $formBuilder->setAction($action)
      ->setMethod('POST')
      ->getForm()
    ;

  }

}
