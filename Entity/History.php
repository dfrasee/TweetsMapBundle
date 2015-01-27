<?php

namespace TweetsMapBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TweetsMapBundle\Entity\History
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks() 
 * @ORM\Table(name="search_histories")
 */
class History
{
         
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
	
    /**
     * @var string $user
     *
     * @ORM\Column(name="user", type="string", length=128)
     */
    protected $user;

    /**
     * @var string $key_search
     *
     * @ORM\Column(name="key_search", type="string", length=100)
     */
    protected $key_search;

        /**
     * @var decimal $lat
     *
     * @ORM\Column(name="lat", type="decimal", precision=10, scale=7)
     */
    protected $lat;
    
        /**
     * @var decimal $lng
     *
     * @ORM\Column(name="lng", type="decimal", precision=10, scale=7)
     */
    protected $lng;
    
    /**
     * @var string $count
     *
     * @ORM\Column(name="count", type="integer", options={"default" = 0})
     */
    protected $count;
    
    /**
     * @var \Datetime 
     * 
     * @ORM\Column(name="last_search", type="datetime")
     */
    protected $last_search;
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set user
     *
     * @param string $country
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return string 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set key_search
     *
     * @param string $key_search
     */
    public function setKeySearch($key_search)
    {
        $this->key_search = $key_search;
    }

    /**
     * Get key_search
     *
     * @return string 
     */
    public function getKeySearch()
    {
        return $this->key_search;
    }
    
    
    /**
     * Set lat
     *
     * @param decimal $lat
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
    }

    /**
     * Get lat
     *
     * @return decimal 
     */
    public function getLat()
    {
        return $this->lat;
    }
    
    /**
     * Set lng
     *
     * @param decimal $lng
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
    }

    /**
     * Get lng
     *
     * @return decimal 
     */
    public function getLng()
    {
        return $this->lng;
    }
    
    /**
     * Set count
     *
     * @param integer $count_search
     */
    public function setCount($count_search)
    {
        $this->count = $count_search;
    }

    /**
     * Get count_search
     *
     * @return integer 
     */
    public function getCount()
    {
        return $this->count;
    }
    
    /**
     * Set last_search
     * Tell doctrine that before we persist or update
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setLastSearch()
    {
        $this->last_search = new \DateTime();
    }

    /**
     * Get last_search
     *
     * @return \Datetime 
     */
    public function getLastSearch()
    {
        return $this->last_search;
    }
    
}