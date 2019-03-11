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
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\User;
use Twig\Environment;
use Twig\Token;

class RestaurantResultsController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(Request $request, Environment $twig)
    {
        $em = $this->getDoctrine()->getManager();
        $Users = $em->getRepository(\App\Entity\User::class)->findBy([], ['firstName' => 'ASC', "lastName" => "ASC"]);

        $is_mobile = $request->cookies->get("is_mobile");

        return $this->render("restaurant_results/index.html.twig", [
            "Users" => $Users,
            "is_mobile" => $is_mobile
        ]);

        return $response;
    }

    /**
     * @Route("/restaurant_list/generate", name="generate_restaurant_list")
     */
    public function generateList(Request $request, TokenStorageInterface $tokenStorage, Environment $twig)
    {
        $em = $this->getDoctrine()->getManager();

        $isAdmin = false;
        $User = $tokenStorage->getToken()->getUser();
        if ($User) {
            if (is_object($User)) {
                $roles = $User->getRoles();
                if (in_array("ROLE_ADMIN", $roles)) {
                    $isAdmin = true;
                }
            }
        }

        $userIDArr = $request->request->has("user_id") ? $request->request->get("user_id") : array();
        $is_mobile = $request->request->has("is_mobile") ? intval($request->request->get("is_mobile")) : 0;

        $is_mobile = $request->cookies->get("is_mobile");

        $user_id_str = implode(",", $userIDArr);

        $FinalRestaurants = array();
        if (count($userIDArr)) {
            $sql = "SELECT MIN(rating) 
                    FROM restaurant_user_rating 
                    WHERE restaurant_id = :restaurant_id 
                    LIMIT 1 ";
            $stmt_sel_min_rating = $em->getConnection()->prepare($sql);

            /**
             * Get All Restuarants with sum of ratings for each
             * and last visited date
             */
            $UseRatings = array();
            $sql = "SELECT rur.restaurant_id, r.name, 
                    SUM(rating) as rating_sum, r.last_visited_date
                    FROM restaurant_user_rating rur
                    INNER JOIN restaurant r
                      ON rur.restaurant_id = r.id
                    INNER JOIN user u 
                      ON rur.user_id = u.id 
                    WHERE u.id in (" . $user_id_str . ") 
                    GROUP BY restaurant_id ";
            $stmt = $em->getConnection()->prepare($sql);

            $stmt->execute();
            $Ratings = $stmt->fetchAll(2);
            foreach ($Ratings as $getRating) {  // loop through Restaurants

                # Get Sum
                $restaurant_score = $getRating['rating_sum'];
                $orig_score = $restaurant_score;

                # Multiplier based on minimum rating for each restaurant
                $sql = "SELECT MIN(rating) 
                        FROM restaurant_user_rating 
                        WHERE restaurant_id = :restaurant_id 
                        LIMIT 1 ";
                $stmt_sel_min_rating->execute([
                    "restaurant_id" => $getRating['restaurant_id']
                ]);
                $MinRating = $stmt_sel_min_rating->fetchAll(2);

                switch ($MinRating) {
                    case 10:
                        $restaurant_score *= 5;
                        break;
                    case 9:
                        $restaurant_score *= 4.5;
                        break;
                    case 8:
                        $restaurant_score *= 4;
                        break;
                    case 7:
                        $restaurant_score *= 3.5;
                        break;
                    case 6:
                        $restaurant_score *= 3;
                        break;
                    case 5:
                        $restaurant_score *= 2.5;
                        break;
                    case 4:
                        $restaurant_score *= 2;
                        break;
                    case 3:
                        $restaurant_score *= 1.5;
                        break;
                    case 2:
                        $restaurant_score *= 1;
                        break;
                    case 1:
                        $restaurant_score *= 0.5;
                        break;
                    case 0:
                        $restaurant_score *= 0.2;
                        break;
                }

                # Get Days passed since visited restaurant
                $lastVisitedDate = $getRating['last_visited_date'];
                $datetime1 = new \DateTime($lastVisitedDate);
                $datetime2 = new \DateTime();
                $daysPast = $datetime1->diff($datetime2)->format('%a');;

                # Multiplier based on days past
                switch (true) {
                    case ($daysPast <= 7):
                        $restaurant_score *= 0.1;
                        break;
                    case ($daysPast <= 14):
                        $restaurant_score *= 0.7;
                        break;
                    case ($daysPast <= 21):
                        $restaurant_score *= 1;
                        break;
                    case ($daysPast <= 28):
                        $restaurant_score *= 1.05;
                        break;
                    case ($daysPast <= 35):
                        $restaurant_score *= 1.12;
                        break;
                    case ($daysPast <= 42):
                        $restaurant_score *= 1.2;
                        break;
                    case ($daysPast <= 56):
                        $restaurant_score *= 1.29;
                        break;
                    case ($daysPast <= 63):
                        $restaurant_score *= 1.37;
                        break;
                    case ($daysPast <= 70):
                        $restaurant_score *= 1.46;
                        break;
                    case ($daysPast <= 77):
                        $restaurant_score *= 1.56;
                        break;
                    case ($daysPast > 77):
                        $restaurant_score *= 1.61;
                        break;
                }

                # Store top restaurants in array
                $UseRatings[(String)($restaurant_score)][] = [
                    "restaurant_id" => $getRating['restaurant_id'],
                    "daysPast" => $daysPast,
                    "combinedRating" => $orig_score,
                    "restaurant" => $getRating['name']
                ];
                krsort($UseRatings, 1);
            }

            /**
             * Put restaurants into array in best scores to worst
             * Add some extra information about each restaurant (combinedRating, daysPast)
             */
            $sql = "SELECT * FROM restaurant WHERE id = :id ";
            $stmt_sel_restaurant = $em->getConnection()->prepare($sql);

            $i = 0;
            $breakLoop = false;
            foreach ($UseRatings as $score => $getRestaurant) {

                foreach ($getRestaurant as $eachRest) {

                    $j = $i + 1;
                    $sql = "SELECT * FROM restaurant WHERE id = :id ";
                    $stmt_sel_restaurant->execute([
                        "id" => $eachRest['restaurant_id']
                    ]);
                    $GetRestaurants = $stmt_sel_restaurant->fetchAll(2);
                    if (count($GetRestaurants) > 0) {
                        $GetRestaurants[0]['score'] = $score;
                        $GetRestaurants[0]['daysPast'] = $eachRest['daysPast'];
                        $GetRestaurants[0]['combinedRating'] = $eachRest['combinedRating'];
                        $GetRestaurants[0]['num'] = $j;
                        $FinalRestaurants[] = $GetRestaurants[0];
                    }
                    # Only show top 15 restaurants
                    if ($i > 13) {
                        $breakLoop = true;
                        break;
                    }
                    $i++;
                }
                if ($breakLoop) {
                    break;
                }

            }
        }

        $response = new Response();
        $cookie = new Cookie('is_mobile', 1, time() + (365 * 24 * 60 * 60));  // Expires 1 years
        $response->headers->setCookie($cookie);
        $response->sendHeaders();

        return $this->render("restaurant_results/list.html.twig", [
            "RestaurantList" => $FinalRestaurants,
            "isAdmin" => $isAdmin,
            "is_mobile" => $is_mobile
        ], $response);
    }

    /**
     * @Route("/admin/restaurant/update_date/{id}", name="update_restaurant_date")
     */
    public function updateRestaurantDate(Restaurant $restaurant)
    {
        $em = $this->getDoctrine()->getManager();

        $restaurant->setLastVisitedDate(new \DateTime());
        $em->persist($restaurant);
        $em->flush();

        return new JsonResponse([
            "status" => "success"
        ]);
    }
}