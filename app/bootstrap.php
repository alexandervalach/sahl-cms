<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

//$configurator->setDebugMode(FALSE); // enable for your remote IP
$configurator->enableDebugger(__DIR__ . '/../log');

$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
        ->addDirectory(__DIR__)
        ->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();

//Setup MultipleFileUpload
MultipleFileUpload\MultipleFileUpload::register();

MultipleFileUpload\MultipleFileUpload::getUIRegistrator()
        ->clear()
        ->register('MultipleFileUpload\UI\HTML4SingleUpload')
        ->register('MultipleFileUpload\UI\Uploadify');

return $container;
