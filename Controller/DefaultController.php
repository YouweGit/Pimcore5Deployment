<?php

namespace Pimcore5\DeploymentBundle\Controller;

use Pimcore\Controller\FrontendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends FrontendController
{
    /**
     * @Route("/pimcore5_deployment")
     */
    public function indexAction(Request $request)
    {
        return new Response('Hello world from pimcore5_deployment');
    }
}
