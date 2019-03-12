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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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

    /**
     * @Route("/admin/restaurants/show")
     */
    public function showRestaurants(TokenStorageInterface $tokenStorage)
    {
        $em = $this->getDoctrine()->getManager();

        $User = $tokenStorage->getToken()->getUser();

        $sql = "SELECT r.id, r.name, ifnull(rur.rating, -1) as rating 
                FROM restaurant r  
                LEFT JOIN restaurant_user_rating rur 
                  on r.id = rur.restaurant_id AND rur.user_id = :user_id 
                ORDER BY r.name ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute([
            "user_id" => $User->getId()
        ]);
        $RestaurantRatings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return new JsonResponse($RestaurantRatings);
    }

    /**
     * @Route("/admin/rating/update/{id}")
     * @Method({"POST"})
     */
    public function updateRating(Restaurant $restaurant, Request $request, TokenStorageInterface $tokenStorage)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$restaurant) {
            return new JsonResponse([
                "status" => "error",
                "error_msg" => "Restaurant does not exist"
            ]);
        }

        $User = $tokenStorage->getToken()->getUser();

        $rating = $request->request->has("rating") ? intval($request->request->get("rating")) : 0;

        $RestaurantUserRating = $em->getRepository(RestaurantUserRating::class)->findOneBy([
            "user" => $User->getId(),
            "restaurant" => $restaurant->getId()
        ]);

        if ($RestaurantUserRating) {
            $RestaurantUserRating->setRating($rating);
        } else {

            $RestaurantUserRating = new RestaurantUserRating();
            $RestaurantUserRating->setUser($User);
            $RestaurantUserRating->setRestaurant($restaurant);
            $RestaurantUserRating->setRating($rating);
        }
        $em->persist($RestaurantUserRating);
        $em->flush();

        return new JsonResponse([
            "status" => "success"
        ]);
    }

    /**
     * @Route("/admin/survey/add_ratings")
     */
    public function addUserRatings()
    {
        dd("don't run");

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT * FROM user";
        $stmt_sel_users = $em->getConnection()->prepare($sql);

        $sql = "INSERT INTO restaurant_user_rating 
                ( restaurant_id,  user_id,  rating) VALUES 
                (:restaurant_id, :user_id, :rating) ";
        $stmt_ins_user_ratings = $em->getConnection()->prepare($sql);

        $sql = "SELECT * FROM restaurant ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        $Restaurants = $stmt->fetchAll(2);
        foreach ($Restaurants as $getRestaurant) {

            $sql = "SELECT * FROM user";
            $stmt_sel_users->execute();
            $Users = $stmt_sel_users->fetchAll(2);
            foreach ($Users as $getUser) {

                $rating = rand(1,10);

                $sql = "INSERT INTO restaurant_user_rating 
                ( restaurant_id,  user_id,  rating) VALUES 
                (:restaurant_id, :user_id, :rating) ";
                $stmt_ins_user_ratings->execute([
                   "restaurant_id" => $getRestaurant['id'],
                   "user_id" => $getUser['id'],
                   "rating" => $rating
                ]);
            }
        }
        dd("Script Completed");
    }
}