<?php

namespace MaximeLEAU\JobeetBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use MaximeLEAU\JobeetBundle\Entity\Job;
use MaximeLEAU\JobeetBundle\Form\JobType;

/**
 * Job controller.
 *
 */
class JobController extends Controller
{

    /**
     * Lists all categories and set active jobs for each category.
     *
     */    
	public function indexAction()
	{
		$request = $this->getRequest();
		
		if($request->get('_route') == 'MaximeLEAUJobeetBundle_nonlocalized') {
			return $this->redirect($this->generateUrl('MaximeLEAUJobeetBundle_homepage'));
		}
		
	    $em = $this->getDoctrine()->getManager();
	
	    $categories = $em->getRepository('MaximeLEAUJobeetBundle:Category')->getWithJobs();
	
	    foreach($categories as $category)
	    {
	        $category->setActiveJobs($em->getRepository('MaximeLEAUJobeetBundle:Job')->getActiveJobs($category->getId(), $this->container->getParameter('max_jobs_on_homepage')));
	        $category->setMoreJobs($em->getRepository('MaximeLEAUJobeetBundle:Job')->countActiveJobs($category->getId()) - $this->container->getParameter('max_jobs_on_homepage'));
	    }
	
	    $format = $this->getRequest()->getRequestFormat();
	    
	    $latestJob = $em->getRepository('MaximeLEAUJobeetBundle:Job')->getLatestPost();
	    
	    if($latestJob) {
	    	$lastUpdated = $latestJob->getCreatedAt()->format(DATE_ATOM);
	    } else {
	    	$lastUpdated = new \DateTime();
	    	$lastUpdated = $lastUpdated->format(DATE_ATOM);
	    }
	    
	    $format = $this->getRequest()->getRequestFormat();
	    return $this->render('MaximeLEAUJobeetBundle:Job:index.'.$format.'.twig', array(
	    		'categories' => $categories,
	    		'lastUpdated' => $lastUpdated,
	    		'feedId' => sha1($this->get('router')->generate('MaximeLEAU_job', array('_format'=> 'atom'), true)),
	    ));
	}
	
	/**
	 * 
	 * @param The Job token $token
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function previewAction($token)
	{
		$em = $this->getDoctrine()->getManager();
	
		$entity = $em->getRepository('MaximeLEAUJobeetBundle:Job')->findOneByToken($token);
	
		if (!$entity) {
			throw $this->createNotFoundException('Unable to find Job entity.');
		}
	
		$deleteForm = $this->createDeleteForm($entity->getToken());
	    $publishForm = $this->createPublishForm($entity->getToken());
	    $extendForm = $this->createExtendForm($entity->getToken());
	    
	    return $this->render('MaximeLEAUJobeetBundle:Job:show.html.twig', array(
	        'entity'      => $entity,
	        'delete_form' => $deleteForm->createView(),
	        'publish_form' => $publishForm->createView(),
        	'extend_form' => $extendForm->createView(),
	    ));
	}
	
	/**
	 * 
	 * @param Request $request
	 * @param The Job token $token
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function publishAction(Request $request, $token)
	{
		$form = $this->createPublishForm($token);
		$form->bind($request);
	
		if ($form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$entity = $em->getRepository('MaximeLEAUJobeetBundle:Job')->findOneByToken($token);
	
			if (!$entity) {
				throw $this->createNotFoundException('Unable to find Job entity.');
			}
	
			$entity->publish();
			$em->persist($entity);
			$em->flush();
	
			$this->get('session')->getFlashBag()->add('notice', 'Your job is now online for 30 days.');
		}
	
		return $this->redirect($this->generateUrl('MaximeLEAU_job_preview', array(
				'company' => $entity->getCompanySlug(),
				'location' => $entity->getLocationSlug(),
				'token' => $entity->getToken(),
				'position' => $entity->getPositionSlug()
		)));
	}
	
	/**
	 * 
	 * @param The Job token $token
	 * @return \Symfony\Component\Form\Form
	 */
	private function createPublishForm($token)
	{
		return $this->createFormBuilder(array('token' => $token))
		->add('token', 'hidden')
		->getForm()
		;
	}

    /**
     * Creates a new Job entity.
     *
     */
	public function createAction(Request $request)
	{
		$entity  = new Job();
		$form = $this->createForm(new JobType(), $entity);
		$form->bind($request);
	
		if ($form->isValid()) {
			$em = $this->getDoctrine()->getManager();
	
			$em->persist($entity);
			$em->flush();
	
			return $this->redirect($this->generateUrl('MaximeLEAU_job_preview', array(
	            'company' => $entity->getCompanySlug(),
	            'location' => $entity->getLocationSlug(),
	            'token' => $entity->getToken(),
	            'position' => $entity->getPositionSlug()
	        )));
		}
	
		return $this->render('MaximeLEAUJobeetBundle:Job:new.html.twig', array(
				'entity' => $entity,
				'form'   => $form->createView(),
		));
	}

    /**
     * Creates a form to create a Job entity.
     *
     * @param Job $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Job $entity)
    {
        $form = $this->createForm(new JobType(), $entity, array(
            'action' => $this->generateUrl('MaximeLEAU_job_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Job entity.
     *
     */
    public function newAction()
    {
    	$entity = new Job();
    	$entity->setType('full-time');
    	$form = $this->createForm(new JobType(), $entity);
    
    	return $this->render('MaximeLEAUJobeetBundle:Job:new.html.twig', array(
    			'entity' => $entity,
    			'form'   => $form->createView()
    	));
    }

    /**
     * Finds and displays a Job entity.
     *
     */
    public function showAction($id)
    {        
        $em = $this->getDoctrine()->getManager();
        
        $entity = $em->getRepository('MaximeLEAUJobeetBundle:Job')->getActiveJob($id);
        
        if (!$entity) {
        	throw $this->createNotFoundException('Unable to find Job entity.');
        }
        
        $session = $this->getRequest()->getSession();
        
        // fetch jobs already stored in the job history
        $jobs = $session->get('job_history', array());
        
        // store the job as an array so we can put it in the session and avoid entity serialize errors
        $job = array('id' => $entity->getId(), 'position' =>$entity->getPosition(), 'company' => $entity->getCompany(), 'companyslug' => $entity->getCompanySlug(), 'locationslug' => $entity->getLocationSlug(), 'positionslug' => $entity->getPositionSlug());
        
        if (!in_array($job, $jobs)) {
        	// add the current job at the beginning of the array
        	array_unshift($jobs, $job);
        
        	// store the new job history back into the session
        	$session->set('job_history', array_slice($jobs, 0, 3));
        }
        
        $deleteForm = $this->createDeleteForm($id);
        
        return $this->render('MaximeLEAUJobeetBundle:Job:show.html.twig', array(
        		'entity'      => $entity,
        		'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Job entity.
     *
     */
    public function editAction($token)
    {
    	$em = $this->getDoctrine()->getManager();
    
    	$entity = $em->getRepository('MaximeLEAUJobeetBundle:Job')->findOneByToken($token);
    
    	if (!$entity) {
    		throw $this->createNotFoundException('Unable to find Job entity.');
    	}
    
    	if ($entity->getIsActivated()) {
    		throw $this->createNotFoundException('Job is activated and cannot be edited.');
    	}
    	
    	$editForm = $this->createForm(new JobType(), $entity);
    	$deleteForm = $this->createDeleteForm($token);
    
    	return $this->render('MaximeLEAUJobeetBundle:Job:edit.html.twig', array(
    			'entity'      => $entity,
    			'edit_form'   => $editForm->createView(),
    			'delete_form' => $deleteForm->createView(),
    	));
    }

    /**
    * Creates a form to edit a Job entity.
    *
    * @param Job $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Job $entity)
    {
        $form = $this->createForm(new JobType(), $entity, array(
            'action' => $this->generateUrl('MaximeLEAU_job_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    
    /**
     * Edits an existing Job entity.
     *
     */
    public function updateAction(Request $request, $token)
    {
    	$em = $this->getDoctrine()->getManager();
    
    	$entity = $em->getRepository('MaximeLEAUJobeetBundle:Job')->findOneByToken($token);
    
    	if (!$entity) {
    		throw $this->createNotFoundException('Unable to find Job entity.');
    	}
    
    	$editForm   = $this->createForm(new JobType(), $entity);
    	$deleteForm = $this->createDeleteForm($token);
    
    	$editForm->bind($request);
    
    	if ($editForm->isValid()) {
    		$em->persist($entity);
    		$em->flush();
    
    		return $this->redirect($this->generateUrl('MaximeLEAU_job_preview', array(
	            'company' => $entity->getCompanySlug(),
	            'location' => $entity->getLocationSlug(),
	            'token' => $entity->getToken(), 
	            'position' => $entity->getPositionSlug()
	        )));
    	}
    
    	return $this->render('MaximeLEAUJobeetBundle:Job:edit.html.twig', array(
    			'entity'      => $entity,
    			'edit_form'   => $editForm->createView(),
    			'delete_form' => $deleteForm->createView(),
    	));
    }    
    
    /**
     * Deletes a Job entity.
     *
     */
    public function deleteAction(Request $request, $token)
    {
        $form = $this->createDeleteForm($token);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('MaximeLEAUJobeetBundle:Job')->findOneByToken($token);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Job entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('MaximeLEAU_job'));
    }

    /**
     * Creates a form to delete a Job entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($token)
    {
        return $this->createFormBuilder(array('token' => $token))
            ->add('token', 'hidden')
            ->getForm()
        ;
    }
    
    /**
     * Extends job's validity action
     * 
     * @param Request $request
     * @param The job token $token
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function extendAction(Request $request, $token)
    {
    	$form = $this->createExtendForm($token);
    	$request = $this->getRequest();
    
    	$form->bind($request);
    
    	if($form->isValid()) {
    		$em=$this->getDoctrine()->getManager();
    		$entity = $em->getRepository('MaximeLEAUJobeetBundle:Job')->findOneByToken($token);
    
    		if(!$entity){
    			throw $this->createNotFoundException('Unable to find Job entity.');
    		}
    
    		if(!$entity->extend()){
    			throw $this->createNodFoundException('Unable to extend the Job');
    		}
    
    		$em->persist($entity);
    		$em->flush();
    
    		$this->get('session')->getFlashBag()->add('notice', sprintf('Your job validity has been extended until %s', $entity->getExpiresAt()->format('m/d/Y')));
    	}
    
    	return $this->redirect($this->generateUrl('MaximeLEAU_job_preview', array(
    			'company' => $entity->getCompanySlug(),
    			'location' => $entity->getLocationSlug(),
    			'token' => $entity->getToken(),
    			'position' => $entity->getPositionSlug()
    	)));
    }
    
    /**
     * Create form to extend job's validity
     * 
     * @param The job token  $token
     * @return \Symfony\Component\Form\Form
     */
    private function createExtendForm($token)
    {
    	return $this->createFormBuilder(array('token' => $token))
    	->add('token', 'hidden')
    	->getForm();
    }
        
    /**
     * Launch Search Action
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
    	$query = $this->getRequest()->get('query');
    
    	if(!$query) {
    		if(!$request->isXmlHttpRequest()) {
    			return $this->redirect($this->generateUrl('MaximeLEAU_job'));
    		} else {
    			return new Response('No results.');
    		}
    	}
    
    	$jobs = $em->getRepository('MaximeLEAUJobeetBundle:Job')->getForLuceneQuery($query);
    

    	if($request->isXmlHttpRequest()) {
    		if('*' == $query || !$jobs || $query == '') {
    			return new Response('No results.');
    		}
    
    		return $this->render('MaximeLEAUJobeetBundle:Job:list.html.twig', array('jobs' => $jobs));
    	}
    
    	return $this->render('MaximeLEAUJobeetBundle:Job:search.html.twig', array('jobs' => $jobs));
    }
    
}
