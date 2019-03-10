<?php
/**
 * Created by PhpStorm.
 * User: asimo
 * Date: 2/28/2019
 * Time: 10:38 PM
 */

namespace App\Controller;


use App\Entity\Restaurant;
use App\Entity\RestaurantUserRating;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\User;

class ProfileController extends AbstractController
{

    /**
     * @Route("/admin/profile_settings/{saved}", name="profile_settings", defaults={"saved"="0"})
     */
    public function show(UserPasswordEncoderInterface $passwordEncoder, $saved)
    {
        /*/
        $User = $this->getDoctrine()->getRepository(\App\Entity\User::class)->findOneBy([
            "username" => "ahawley"
        ]);
        $newPass = $passwordEncoder->encodePassword($User, "fish90");
        dd($newPass);
        //*/

        return $this->render("profile/index.html.twig", [
            "saved" => $saved
        ]);
    }

    /**
     * @Route("/admin/update_password", name="update_password")
     * @Method({"POST"})
     */
    public function updatePassword(Request $request, TokenStorageInterface $tokenStorage, UserPasswordEncoderInterface $passwordEncoder, UrlGeneratorInterface $urlGenerator)
    {
        $em = $this->getDoctrine()->getManager();

        $User = $tokenStorage->getToken()->getUser();

        $password = $request->request->get("password");
        $newPass = $passwordEncoder->encodePassword($User, $password);
        $User->setPassword($newPass);

        $em->persist($User);
        $em->flush();


        return $this->redirectToRoute("profile_settings", ["saved" => 1]);
    }
}