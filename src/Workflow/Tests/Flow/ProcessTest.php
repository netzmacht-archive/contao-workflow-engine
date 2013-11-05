<?php

namespace Workflow\Tests\Flow;

use Workflow\Tests\TestCase;
use Workflow\DependencyInjection\LexikWorkflowExtension;
use Workflow\Flow\Process;
use Workflow\Flow\Step;

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class ProcessTest extends TestCase
{
    public function testProcessService()
    {
        $extension = new LexikWorkflowExtension();
        $extension->load(array($this->getConfig()), $container = new ContainerBuilder());

        $process = $container->get('lexik_workflow.process.document_proccess');
        $this->assertTrue($process instanceof Process);
        $this->assertTrue($process->getSteps() instanceof ArrayCollection);
        $this->assertEquals(3, $process->getSteps()->count());
        $this->assertTrue($process->getSteps()->get('step_create_doc') instanceof Step);
    }
}
