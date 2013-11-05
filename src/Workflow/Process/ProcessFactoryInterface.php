<?php

namespace Workflow\Process;


interface ProcessFactoryInterface
{

	/**
	 * @param string $name
	 *
	 * @return \Workflow\Flow\Process
	 */
	public function create($name);

}
