<?php
/**
 * Created by PhpStorm.
 * User: asimo
 * Date: 2/28/2019
 * Time: 10:38 PM
 */

namespace App\Controller;


use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\User;

class SurveyController extends AbstractController
{

    /**
     * @Route("/admin/survey", name="survey_index")
     */
    public function show(UserPasswordEncoderInterface $passwordEncoder)
    {
        /*/
        $User = $this->getDoctrine()->getRepository(\App\Entity\User::class)->findOneBy([
            "username" => "ahawley"
        ]);
        $newPass = $passwordEncoder->encodePassword($User, "fish90");
        dd($newPass);
        //*/

        return $this->render("survey/index.html.twig");
    }
}