<?php
/**
 * Created by PhpStorm.
 * User: asimo
 * Date: 2/28/2019
 * Time: 10:38 PM
 */

namespace App\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LoginController extends AbstractController
{
    /**
     * @Route("/login")
     */
    public function index()
    {

        return $this->render("login/index.html.twig", []);
    }
}