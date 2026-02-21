<?php

declare(strict_types=1);

namespace App;

use Neu;
use Neu\Component\DependencyInjection\ContainerBuilder;
use Neu\Component\DependencyInjection\Project;
use Neu\Framework\EngineInterface;
use Psl\Env;
use Psl\SecureRandom;

require_once __DIR__ . '/../vendor/autoload.php';

/* |----------------------------------------| */
/* | Run the engine inside a closure        | */
/* | to avoid leaking variables to the      | */
/* | global scope.                          | */
/* |----------------------------------------| */
(static function (): void {
    /* |----------------------------------------| */
    /* | Retrieve the project secret.           | */
    /* |----------------------------------------| */
    $secret = Env\get_var('PROJECT_SECRET');
    $generated = false;

    /* |----------------------------------------| */
    /* | Check if the secret is missing.        | */
    /* |----------------------------------------| */
    if (null === $secret) {
        /* |----------------------------------------| */
        /* | Generate a new secret.                 | */
        /* |----------------------------------------| */
        $secret = SecureRandom\string(32);

        /* |----------------------------------------| */
        /* | Store the secret in the environment.   | */
        /* |----------------------------------------| */
        Env\set_var('PROJECT_SECRET', $secret);

        /* |----------------------------------------| */
        /* | Mark the secret as generated.          | */
        /* |----------------------------------------| */
        $generated = true;
    }

    /* |----------------------------------------| */
    /* | Create a project instance.             | */
    /* |----------------------------------------| */
    $project = Project::create(secret: $secret, directory: __DIR__ . '/../', entrypoint: __FILE__);

    /* |----------------------------------------| */
    /* | Check if the project is in production. | */
    /* |----------------------------------------| */
    if ($project->mode->isProduction() && $generated) {
        /* |----------------------------------------| */
        /* | Throw an exception if the project      | */
        /* | secret has been generated in           | */
        /* | production mode.                       | */
        /* |----------------------------------------| */
        throw new Neu\Framework\Exception\RuntimeException('The project secret has been generated in production mode.');
    }

    /* |----------------------------------------| */
    /* | Create a container builder.            | */
    /* |----------------------------------------| */
    $containerBuilder = ContainerBuilder::create($project);

    /* |----------------------------------------| */
    /* | Add configuration resources to the     | */
    /* | container builder.                     | */
    /* |----------------------------------------| */
    $containerBuilder->addConfigurationResource(__DIR__ . '/../config');
    $containerBuilder->addConfigurationResource(__DIR__ . '/../config/' . $project->mode->value . '/');

    /* |----------------------------------------| */
    /* | Add extensions to the container        | */
    /* | builder.                               | */
    /* |----------------------------------------| */
    $containerBuilder->addExtensions([
        new Neu\Bridge\Monolog\DependencyInjection\MonologExtension(),
        new Neu\Framework\DependencyInjection\FrameworkExtension(),
        new Neu\Bridge\Twig\DependencyInjection\TwigExtension(),
    ]);

    /* |----------------------------------------| */
    /* | Add paths to the container builder for | */
    /* | auto-discovery.                        | */
    /* |----------------------------------------| */
    $containerBuilder->addPathForAutoDiscovery(__DIR__);

    /* |----------------------------------------| */
    /* | Build the container.                   | */
    /* |----------------------------------------| */
    $container = $containerBuilder->build();

    /* |----------------------------------------| */
    /* | Retrieve the engine from the           | */
    /* | container.                             | */
    /* |----------------------------------------| */
    \PHPStan\dumpType($container);
    $engine = $container->getTyped(EngineInterface::class, EngineInterface::class);

    /* |----------------------------------------| */
    /* | Run the engine.                        | */
    /* |----------------------------------------| */
    $engine->run();
})();
