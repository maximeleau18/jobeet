<?php
namespace MaximeLEAU\JobeetBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use MaximeLEAU\JobeetBundle\Entity\Affiliate;
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * Affiliate Admin Class
 * @author Maxime Léau
 *
 */
class AffiliateAdmin extends Admin
{
	protected $datagridValues = array(
        '_sort_order' => 'ASC',
        '_sort_by' => 'is_active',
        'is_active' => array('value' => 2) // The value 2 represents that the displayed affiliate accounts are not activated yet
    );

	/**
	 * 
	 * (non-PHPdoc)
	 * @see \Sonata\AdminBundle\Admin\Admin::configureFormFields()
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
		->add('email')
		->add('url')
		;
	}

	/**
	 * (non-PHPdoc)
	 * @see \Sonata\AdminBundle\Admin\Admin::configureDatagridFilters()
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('email')
		->add('is_active');
	}

	/**
	 * (non-PHPdoc)
	 * @see \Sonata\AdminBundle\Admin\Admin::configureListFields()
	 */
	protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('is_active')
            ->addIdentifier('email')
            ->add('url')
            ->add('created_at')
            ->add('token')
            ->add('_action', 'actions', array( 'actions' => array('activate' => array('template' => 'MaximeLEAUJobeetBundle:AffiliateAdmin:list__action_activate.html.twig'),
                'deactivate' => array('template' => 'MaximeLEAUJobeetBundle:AffiliateAdmin:list__action_deactivate.html.twig'))))
        ;
    }
	
    /**
     * (non-PHPdoc)
     * @see \Sonata\AdminBundle\Admin\Admin::getBatchActions()
     */
	public function getBatchActions()
	{
		$actions = parent::getBatchActions();
	
		if($this->hasRoute('edit') && $this->isGranted('EDIT') && $this->hasRoute('delete') && $this->isGranted('DELETE')) {
			$actions['activate'] = array(
					'label'            => 'Activate',
					'ask_confirmation' => true
			);
	
			$actions['deactivate'] = array(
					'label'            => 'Deactivate',
					'ask_confirmation' => true
			);
		}
	
		return $actions;
	}
	
	/**
	 * Configure routes to activate or deactivate an affiliate
	 * 
	 * (non-PHPdoc)
	 * @see \Sonata\AdminBundle\Admin\Admin::configureRoutes()
	 */
	protected function configureRoutes(RouteCollection $collection) {
		parent::configureRoutes($collection);
	
		$collection->add('activate',
				$this->getRouterIdParameter().'/activate')
				;
	
		$collection->add('deactivate',
				$this->getRouterIdParameter().'/deactivate')
				;
	}
}
