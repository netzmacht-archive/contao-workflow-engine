<?php

namespace WorkflowEngine\Tests\DependencyInjection;

use WorkflowEngine\Tests\TestCase;
use WorkflowEngine\DependencyInjection\LexikWorkflowExtension;
use WorkflowEngine\Flow\Process;
use WorkflowEngine\Handler\ProcessAggregator;
use WorkflowEngine\Handler\ProcessHandler;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class LexikWorkflowExtensionTest extends TestCase
{
    public function testLoad()
    {
        $container = new ContainerBuilder();

        // fake entity manager and security context services
        $container->set('doctrine.orm.entity_manager', $this->getMockSqliteEntityManager());
        $container->set('security.context', $this->getMockSecurityContext());
        $container->set('event_dispatcher', new EventDispatcher());

        $extension = new LexikWorkflowExtension();
        $extension->load(array($this->getSimpleConfig()), $container);

        $this->assertTrue($container->getDefinition('lexik_workflow.process.document_proccess') instanceof Definition);

        $extension = new LexikWorkflowExtension();
        $extension->load(array($this->getConfig()), $container);

        $this->assertTrue($container->getDefinition('lexik_workflow.process.document_proccess') instanceof Definition);
        $this->assertTrue($container->getDefinition('lexik_workflow.process.document_proccess.step.step_create_doc') instanceof Definition);
        $this->assertTrue($container->getDefinition('lexik_workflow.process.document_proccess.step.step_validate_doc') instanceof Definition);
        $this->assertTrue($container->getDefinition('lexik_workflow.process.document_proccess.step.step_remove_doc') instanceof Definition);
        $this->assertTrue($container->getDefinition('lexik_workflow.handler.document_proccess') instanceof Definition);

        $processHandlerFactory = $container->get('lexik_workflow.process_aggregator');
        $this->assertTrue($processHandlerFactory instanceof ProcessAggregator);
        $this->assertTrue($processHandlerFactory->getProcess('document_proccess') instanceof Process);

        $processHandler = $container->get('lexik_workflow.handler.document_proccess');
        $this->assertTrue($processHandler instanceof ProcessHandler);
    }
}
