Contao Workflow Engine
===================

This library is a fork of the Symfony2 [LexikWorkflowBundle](https://github.com/lexik/LexikWorkflowBundle) and prepares
the library for being used within the CMS Contao. It removed some dependencies of the Symfony2 and Doctrine, which
 unfortunately are not used in Contao.

For understanding how the workflow engine works, please visit [LexikWorkflowBundle](https://github.com/lexik/LexikWorkflowBundle)
first.

Changes
----------

* Removed Doctrines EntityManager and Doctrine based components
* Implement basic Entity and ModelManager based on [DC_General](https://github.com/MetaModels/DC_General/) interfaces
* Prepare for supporting multiple workflow process for different tables
* Implement a Registry for easily getting required components
* Change credential checking based on roles setting for BackendUser "workflow" permission

Requirements
---------

This library still have some requirements

* [DC_General](https://github.com/MetaModels/DC_General/)
* [DcaTools](https://github.com/netzmacht/contao-dcatools)
* [SymfonyEventDispatcher](https://github.com/symfony/EventDispatcher)

License
-------

Please note https://github.com/lexik/LexikWorkflowBundle/blob/master/LICENSE