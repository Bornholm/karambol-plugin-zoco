<?php

namespace KarambolZocoPlugin\Controller;

use Karambol\Controller\Controller;
use Karambol\KarambolApp;
use Symfony\Component\HttpFoundation\JsonResponse;
use KarambolZocoPlugin\Entity\Workgroup;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WorkgroupController extends Controller {

  public function mount(KarambolApp $app) {
    $app->get('/zoco/workgroup', [$this, 'listWorkgroup'])->bind('plugins_zoco_workgroup');
  }

  public function listWorkgroup() {

    $user = $this->get('user');
    $twig = $this->get('twig');

    $workgroups = $this->get('zoco.workgroup')->getOwnsGroup($user);

    return $twig->render('plugins/zoco/workgroup/listWorkgroup.html.twig', [
      'workgroups' => $workgroups
    ]);

  }

}
