<?php

namespace Workflow\Service;

use DcGeneral\Data\ModelInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Workflow\Handler\Environment;


/**
 * A workflow service is a listener to a workflow process.
 *
 * It will be configured without knowing the current process
 * or table it will listen to. If you need table or workflow specific operations you have to ensure it for yourself
 *
 * @package Workflow\Service
 */
interface ServiceInterface extends EventSubscriberInterface
{

	public function __construct(ModelInterface $service, Environment $environment);

	/**
	 * The initialize is used for registering all listeners depending
	 * on the current model and workflow to the dispatcher
	 *
	 */
	public function initialize();


	/**
	 * @return mixed
	 */
	public static function getConfig();

}