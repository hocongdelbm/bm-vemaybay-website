<?php
// file has been separated out for testing and mocking purposes
$version = 8;

$container = new \Slim\Container;
// Load Containers
$paths = new \SuiteCRM\Utility\Paths();
$containerFiles = (array)glob($paths->getLibraryPath() . '/API/v8/container/*.php');
$customContainerFiles = (array)glob($paths->getCustomLibraryPath() . '/API/v8/container/*.php');

// load core files
foreach ($containerFiles as $containerFile) {
    require $containerFile;
}

// load custom files
foreach ($customContainerFiles as $containerFile) {
    require $containerFile;
}

/**
 * @param \Psr\Container\ContainerInterface $container
 * @return Closure
 */
$container['notAllowedHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        /**
         * @var \SuiteCRM\API\v8\Controller\ApiController $ApiController
         */
        $ApiController = $container->get('ApiController');
        $exception = new \SuiteCRM\API\v8\Exception\NotAllowedException();

        return $ApiController->generateJsonApiErrorResponse($request, $response, $exception);
    };
};

/**
 * @param \Psr\Container\ContainerInterface $container
 * @return Closure
 */
$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        /**
         * @var \SuiteCRM\API\v8\Controller\ApiController $ApiController
         */
        $exception = new \SuiteCRM\API\v8\Exception\NotFoundException('[Resource]');
        $ApiController = $container->get('ApiController');

        return $ApiController->generateJsonApiErrorResponse($request, $response, $exception);
    };
};

/**
 * @param \Psr\Container\ContainerInterface $container
 * @return Closure
 */
$container['errorHandler'] = function ($container) {
    return function ($request, $response, $exception) use ($container) {
        /**
         * @var \SuiteCRM\API\v8\Controller\ApiController $ApiController
         */
        $ApiController = $container->get('ApiController');

        return $ApiController->generateJsonApiErrorResponse($request, $response, $exception);
    };
};


/**
 * @param \Psr\Container\ContainerInterface $container
 * @return Closure
 */
$container['phpErrorHandler'] = function ($container) {
    return function ($request, $response, $exception) use ($container) {
        /**
         * @var \SuiteCRM\API\v8\Controller\ApiController $ApiController
         */
        $ApiController = $container->get('ApiController');

        return $ApiController->generateJsonApiErrorResponse($request, $response, $exception);
    };
};

if (isset($GLOBALS['container']) === false) {
    $GLOBALS['container'] = $container;
}
