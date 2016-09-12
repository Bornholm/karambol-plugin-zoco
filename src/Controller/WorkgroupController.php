<?php

namespace KarambolZocoPlugin\Controller;

use Karambol\Controller\Controller;
use Karambol\KarambolApp;
use Symfony\Component\HttpFoundation\JsonResponse;
use KarambolZocoPlugin\Entity\Workgroup;
use KarambolZocoPlugin\Entity\ZocoUserExtension;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WorkgroupController extends Controller {

  public function mount(KarambolApp $app) {
    $app->get('/zoco/workgroup', [$this, 'listWorkgroup'])->bind('plugins_zoco_workgroup');
  }

  public function listWorkgroup() {

    $user = $this->get('user');
    $ext = $user->getExtensionByName('zoco', ZocoUserExtension::class);
    $twig = $this->get('twig');

    return $twig->render('plugins/zoco/workgroup/listWorkgroup.html.twig', [
      'workgroups' => $ext->getWorkgroups(),
      'user' => $ext
    ]);

  }

  protected function getWorkgroupForm(Workgroup $workgroup) {

    $formFactory = $this->get('form.factory');
    $urlGen = $this->get('url_generator');

    $formBuilder = $formFactory->createBuilder(ProfileType::class, $workgroup);
    $action = $urlGen->generate('handle_profile');

    return $formBuilder->setAction($action)
      ->setMethod('POST')
      ->getForm()
    ;

  }

}
