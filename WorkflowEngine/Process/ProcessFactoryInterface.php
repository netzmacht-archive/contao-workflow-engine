<?php

namespace WorkflowEngine\Process;


interface ProcessFactoryInterface
{

	/**
	 * @param string $name
	 *
	 * @return \WorkflowEngine\Flow\Process
	 */
	public function create($name);

}
