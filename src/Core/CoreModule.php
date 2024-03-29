<?php

/*
 * This file is part of the Ekklesion project.
 * (c) Matías Navarro Carter <mnavarrocarter@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekklesion\Core;

use Doctrine\ORM\EntityManagerInterface;
use Ekklesion\Core\Factory\Middleware as MiddlewareFactory;
use Ekklesion\Core\Factory\Service as ServiceFactory;
use Ekklesion\Core\Infrastructure\CommandBus\CommandBus;
use Ekklesion\Core\Infrastructure\Filesystem\Filesystem;
use Ekklesion\Core\Infrastructure\Http\Controller;
use Ekklesion\Core\Infrastructure\Http\Middleware\AuthenticationMiddleware;
use Ekklesion\Core\Infrastructure\Http\Middleware\ChronosFormatterMiddleware;
use Ekklesion\Core\Infrastructure\Http\Middleware\ForcedPasswordChangeMiddleware;
use Ekklesion\Core\Infrastructure\Http\Middleware\LocalizationMiddleware;
use Ekklesion\Core\Infrastructure\Http\Middleware\RequiresAuthenticationMiddleware;
use Ekklesion\Core\Infrastructure\Http\Middleware\SessionStartMiddleware;
use Ekklesion\Core\Infrastructure\Http\Middleware\StoragePathConfigMiddleware;
use Ekklesion\Core\Infrastructure\Http\Security\Authenticator;
use Ekklesion\Core\Infrastructure\Module\EkklesionModule;
use Ekklesion\Core\Infrastructure\Module\Loader\MiddlewareLoader;
use Ekklesion\Core\Infrastructure\Module\Loader\ResourceLoader;
use Ekklesion\Core\Infrastructure\Module\Loader\RouteLoader;
use Ekklesion\Core\Infrastructure\Persistence\Types;
use Ekklesion\Core\Infrastructure\Templating\Templating;
use Psr\Log\LoggerInterface;

/**
 * Class CoreModule.
 *
 * @author Matías Navarro Carter <mnavarro@option.cl>
 */
class CoreModule implements EkklesionModule
{
    protected static $defaultSettings = [
        'secret' => 'c4ad2e3bb60ad33f238dc82afd12fa2712abab2fb1a502202cc608eb1b8a62ab',
        'db_url' => 'sqlite://data.db',
        'env' => 'dev',
        'log_path' => './log.txt',
        'locale' => 'en_US',
        'login_route' => '/auth/login',
        'settings_file' => './settings.json',
    ];

    /**
     * @var array
     */
    private $settings;

    /**
     * CoreModule constructor.
     *
     * @param array $settings
     */
    public function __construct(array $settings = [])
    {
        $this->settings = array_merge(self::$defaultSettings, $settings);
    }

    public const NAME = 'core';

    public function getModuleName(): string
    {
        return self::NAME;
    }

    public function dependentModules(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getServices(): array
    {
        return [
            // Services
            EntityManagerInterface::class => new ServiceFactory\EntityManagerFactory(),
            LoggerInterface::class => new ServiceFactory\LoggerFactory(),
            Templating::class => new ServiceFactory\TwigTemplatingFactory(),
            \Twig_Environment::class => new ServiceFactory\TwigEnvironmentFactory(),
            CommandBus::class => new ServiceFactory\CommandBusFactory(),
            Authenticator::class => new ServiceFactory\JwtAuthenticatorFactory(),
            Filesystem::class => new ServiceFactory\FilesystemFactory(),
            'notFoundHandler' => new ServiceFactory\NotFoundHandlerFactory(),
            'flash' => function () {
                return new \Slim\Flash\Messages();
            },

            // Middleware
            AuthenticationMiddleware::class => new MiddlewareFactory\AuthenticationMiddlewareFactory(),
            RequiresAuthenticationMiddleware::class => new MiddlewareFactory\RequiresAuthenticationMiddlewareFactory(),
            LocalizationMiddleware::class => new MiddlewareFactory\LocalizationMiddlewareFactory(),
            ForcedPasswordChangeMiddleware::class => new MiddlewareFactory\ForcedPasswordChangeMiddlewareFactory(),
            StoragePathConfigMiddleware::class => new MiddlewareFactory\StoragePathConfigMiddlewareFactory(),
        ];
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @param ResourceLoader $resourceLoader
     */
    public function loadResources(ResourceLoader $resourceLoader): void
    {
        $resourceLoader->loadTemplate(self::NAME, __DIR__.'/Resources/templates');
        $resourceLoader->loadORMType('uuid', Types\UuidType::class);
        $resourceLoader->loadORMType('chronos', Types\ChronosType::class);
        $resourceLoader->loadORMType('filename', Types\FilenameType::class);
    }

    /**
     * @param MiddlewareLoader $middlewareLoader
     */
    public function loadMiddleware(MiddlewareLoader $middlewareLoader): void
    {
        $middlewareLoader->load(700, new SessionStartMiddleware());
        $middlewareLoader->load(660, StoragePathConfigMiddleware::class);
        $middlewareLoader->load(650, new ChronosFormatterMiddleware());
        $middlewareLoader->load(200, LocalizationMiddleware::class);
        $middlewareLoader->load(100, AuthenticationMiddleware::class);
        $middlewareLoader->load(50, ForcedPasswordChangeMiddleware::class);
    }

    public function loadRoutes(RouteLoader $routeLoader): void
    {
        $routeLoader->get('/', Controller\HomeController::class)
            ->add(RequiresAuthenticationMiddleware::class);
    }
}
