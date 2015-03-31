<?php

namespace MaximeLEAU\JobeetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Default Contrller Class
 * @author Maxime Léau
 *
 */
class DefaultController extends Controller
{
	/**
	 * Display the homepage
	 * 
	 * @param unknown $name
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
    public function indexAction($name)
    {
        return $this->render('MaximeLEAUJobeetBundle:Default:index.html.twig', array('name' => $name));
    }
    
    /**
     * Return the homepage with errors if someone exists
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction()
    {
    	$request = $this->getRequest();
    	$session = $request->getSession();
    
    	// get the login error if there is one
    	if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
    		$error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
    	} else {
    		$error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
    		$session->remove(SecurityContext::AUTHENTICATION_ERROR);
    	}
    
    	return $this->render('MaximeLEAUJobeetBundle:Default:login.html.twig', array(
    			// last username entered by the user
    			'last_username' => $session->get(SecurityContext::LAST_USERNAME),
    			'error'         => $error,
    	));
    }
    
    /**
     * Return the homepage with the selected language
     * 
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changeLanguageAction()
    {
    	$language = $this->getRequest()->get('language');
    	return $this->redirect($this->generateUrl('MaximeLEAUJobeetBundle_homepage', array('_locale' => $language)));
    }
}
