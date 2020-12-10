<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    private const CONNECTED = 'IS_AUTHENTICATED_FULLY';
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        if(!$this->isGranted($this::CONNECTED))
        {
            return $this->redirectToRoute('app_login');
        }
        return $this->redirectToRoute('user_index');
    }

}