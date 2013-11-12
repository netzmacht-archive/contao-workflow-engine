<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 01.11.13
 * Time: 23:12
 */

namespace Workflow\Service;

use DcaTools\Data\ConfigBuilder;
use Workflow\Event\StepEvent;


/**
 * Class Service
 * @package Workflow\Service\Notify
 */
class NotifyService extends AbstractService
{

	/**
	 * @var array
	 */
	protected static $config = array
	(
		'identifier' => 'notify',
		'version'    => '1.0',
		'events'     => array
		(
			'reached',
			'validation_fail',
		),
		'properties' => array
		(
			'scope' => array
			(
				'events'
			),

			'config' => array
			(
				'notify_email',
				'notify_users',
			),
		),
	);


	/**
	 *
	 */
	public function initialize()
	{
		$processName = $this->controller->getProcessHandler()->getProcess()->getName();

		/** @var \DcGeneral\Data\ModelInterface $step */
		foreach($this->service->getProperty('steps') as $step)
		{
			$eventName = sprintf('workflow.%s.%s.', $processName, $step);

			foreach($this->service->getProperty('events') as $event)
			{
				$this->controller->getEventDispatcher()->addListener($eventName . $event , array($this, 'notify'));
			}
		}
	}


	/**
	 * @param StepEvent $event
	 */
	public function notify(StepEvent $event)
	{
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
		//$template->model = $this->getModelProperties($event->getModel());

		$email = new \Email();
		$email->from = $GLOBALS['TL_CONFIG']['adminEmail'];
		$email->fromName = $GLOBALS['TL_CONFIG']['websiteTitle'];
		$email->subject = $GLOBALS['TL_LANG']['workflow']['services']['notify']['subject'];
		$email->text = $template->parse();

		// notify to a given email
		if($this->service->getProperty('notify_email'))
		{
			try {
				$email->sendTo($this->service->getProperty('notify_email'));
			}
			catch(\Exception $e) {
				\Controller::log($e->getMessage(), 'Workflow\Service\Notify\Service notify()', 'TL_ERROR');
			}
		}

		// notify selected users
		if($this->service->getProperty('notify_users'))
		{
			$userIds = deserialize($this->service->getProperty('notify_users'), true);
			$driver  = $this->controller->getDriverManager()->getDataProvider('tl_user');
			$builder = ConfigBuilder::create($driver)
				->filterIn('id', $userIds)
				->field($email);

			/** @var \DcGeneral\Data\ModelInterface $user */
			foreach($builder->fetchAll() as $user)
			{
				try {
					$email->sendTo($user->getProperty('email'));
				}
				catch(\Exception $e) {
					\Controller::log($e->getMessage(), 'Workflow\Service\Notify\Service notify()', 'TL_ERROR');
				}
			}
		}
	}
}
