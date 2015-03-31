<?php

namespace MaximeLEAU\JobeetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use MaximeLEAU\JobeetBundle\Utils\Jobeet as Jobeet;

/**
 * Category
 */
class Category
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $jobs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $affiliates;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $active_jobs;
    
    /**
     * 
     * @var integer
     */
    private $more_jobs;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->jobs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->affiliates = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set name
     *
     * @param string $name
     * @return Category
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add jobs
     *
     * @param \MaximeLEAU\JobeetBundle\Entity\Job $jobs
     * @return Category
     */
    public function addJob(\MaximeLEAU\JobeetBundle\Entity\Job $jobs)
    {
        $this->jobs[] = $jobs;

        return $this;
    }

    /**
     * Remove jobs
     *
     * @param \MaximeLEAU\JobeetBundle\Entity\Job $jobs
     */
    public function removeJob(\MaximeLEAU\JobeetBundle\Entity\Job $jobs)
    {
        $this->jobs->removeElement($jobs);
    }

    /**
     * Get jobs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getJobs()
    {
        return $this->jobs;
    }

    /**
     * Add affiliates
     *
     * @param \MaximeLEAU\JobeetBundle\Entity\Affiliate $affiliates
     * @return Category
     */
    public function addAffiliate(\MaximeLEAU\JobeetBundle\Entity\Affiliate $affiliates)
    {
        $this->affiliates[] = $affiliates;

        return $this;
    }

    /**
     * Remove affiliates
     *
     * @param \MaximeLEAU\JobeetBundle\Entity\Affiliate $affiliates
     */
    public function removeAffiliate(\MaximeLEAU\JobeetBundle\Entity\Affiliate $affiliates)
    {
        $this->affiliates->removeElement($affiliates);
    }

    /**
     * Get affiliates
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAffiliates()
    {
        return $this->affiliates;
    }
	
    /**
     * @return Ambigous <string, \MaximeLEAU\JobeetBundle\Entity\string>
     */
	public function __toString()
	{
		return $this->getName() ? $this->getName() : "";
	}
	
	/**
	 * 
	 * @param Collection of active jobs $jobs
	 */
	public function setActiveJobs($jobs)
	{
		$this->active_jobs = $jobs;
	}
	
	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getActiveJobs()
	{
		return $this->active_jobs;
	}
	
	/**
	 * @return integer
	 * @param count of active jobs Collection for each category
	 */
	public function setMoreJobs($jobs)
	{
		$this->more_jobs = $jobs >=  0 ? $jobs : 0;
	}

	/**
	 * 
	 * @return \MaximeLEAU\JobeetBundle\Entity\integer
	 */
	public function getMoreJobs()
	{
		return $this->more_jobs;
	}
	
    /**
     * @var string
     */
    private $slug;


    /**
     * Set slug
     *
     * @param string $slug
     * @return Category
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }
    /**
     * @ORM\PrePersist
     */
    public function setSlugValue()
    {
    	$this->slug = Jobeet::slugify($this->getName());
    }
}
