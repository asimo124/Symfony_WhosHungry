<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RestaurantRepository")
 */
class Restaurant
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $zip;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RestaurantUserRating", mappedBy="restaurant")
     */
    private $restaurantUserRatings;


    public function __construct()
    {
        $this->restaurantUserRatings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getZip(): ?string
    {
        return $this->zip;
    }

    public function setZip(?string $zip): self
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * @return Collection|RestaurantUserRating[]
     */
    public function getRestaurantUserRatings(): Collection
    {
        return $this->restaurantUserRatings;
    }

    public function addRestaurantUserRating(RestaurantUserRating $restaurantUserRating): self
    {
        if (!$this->restaurantUserRatings->contains($restaurantUserRating)) {
            $this->restaurantUserRatings[] = $restaurantUserRating;
            $restaurantUserRating->setRestaurant($this);
        }

        return $this;
    }

    public function removeRestaurantUserRating(RestaurantUserRating $restaurantUserRating): self
    {
        if ($this->restaurantUserRatings->contains($restaurantUserRating)) {
            $this->restaurantUserRatings->removeElement($restaurantUserRating);
            // set the owning side to null (unless already changed)
            if ($restaurantUserRating->getRestaurant() === $this) {
                $restaurantUserRating->setRestaurant(null);
            }
        }

        return $this;
    }
}
