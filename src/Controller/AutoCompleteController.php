<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AutoCompleteController extends AbstractController
{
    /**
     * @Route("/auto/complete", name="auto_complete")
     */
    public function index()
    {
        return $this->render('auto_complete/index.html.twig', [
            'controller_name' => 'AutoCompleteController',
        ]);
    }
}
