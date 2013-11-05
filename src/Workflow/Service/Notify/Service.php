<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 01.11.13
 * Time: 23:12
 */

namespace Workflow\Service\Notify;

use DcaTools\Model\FilterBuilder;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Workflow\Service\AbstractService;
use Workflow\Event\StepEvent;


/**
 * Class Service
 * @package Workflow\Service\Notify
 */
class Service extends AbstractService
{

	/**
	 * @param EventDispatcher $dispatcher
	 */
	public function initialize()
	{
		global $container;

		$driver = $this->environment->getDriverManager()->getDataProvider('tl_workflow_step');

		/** @var \DcGeneral\Data\ModelInterface $step */
		foreach($this->model->getProperty('steps') as $step)
		{
			$eventName = sprintf('workflow.%s.%s.%s.',
				$this->environment->getCurrentWorkflow()->getProperty('forTable'),
				$this->environment->getProcessManager()->getProcess()->getName(), $step);

			foreach($this->model->getProperty('events') as $event)
			{
				$dispatcher->addListener($eventName . $event , array($this, 'notify'));
			}
		}
	}


	/**
	 * @param StepEvent $event
	 */
	public function notify(StepEvent $event)
	{
		var_dump('called');
		$state = $event->getModelState();

		if($state->getSuccessful())
		{
			$mailTemplate = 'be_workflow_mail_success';
		}
		else {
			$mailTemplate = 'be_workflow_mail_fail';
		}

		$template = new \BackendTemplate($mailTemplate);
		$template->errors = $state->getErrors();
		$template->process = $state->getProcessName();
		$template->step = $state->getStepName();
		$template->model = $this->getModelProperties($event->getModel());

		$email = new \Email();
		$email->from = $GLOBALS['TL_CONFIG']['adminEmail'];
		$email->fromName = $GLOBALS['TL_CONFIG']['websiteTitle'];
		$email->subject = $GLOBALS['TL_LANG']['workflow']['services']['notify']['subject'];
		$email->text = $template->parse();

		// notify to a given email
		if($this->model->getProperty('notify_email'))
		{
			$email->sendTo($this->model->getProperty('notify_email'));
		}

		// notify selected users
		if($this->model->getProperty('notify_users'))
		{
			$userIds = deserialize($this->model->getProperty('notify_users'), true);

			/** @var \DcGeneral\Data\DriverInterface $driver */
			$driver = $GLOBALS['container']['workflow.driver-manager']->getDataProvider('tl_user');

			$config = FilterBuilder::create()->addIn('id', $userIds)->getConfig($driver);
			$config->setFields(array('email'));

			foreach($driver->fetchAll($config) as $user)
			{
				$email->sendTo($user->getProperty('email'));
			}
		}
	}

}
