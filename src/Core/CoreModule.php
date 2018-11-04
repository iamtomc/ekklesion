<?php

/*
 * This file is part of the Ekklesion project.
 * (c) Matías Navarro Carter <mnavarrocarter@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekklesion\Core;

use Doctrine\ORM\EntityManagerInterface;
use Ekklesion\Core\Domain\Command;
use Ekklesion\Core\Domain\Repository\AccountRepository;
use Ekklesion\Core\Factory\CommandHandler as HandlerFactory;
use Ekklesion\Core\Factory\Middleware as MiddlewareFactory;
use Ekklesion\Core\Factory\Repository\AccountRepositoryFactory;
use Ekklesion\Core\Factory\Service as ServiceFactory;
use Ekklesion\Core\Infrastructure\CommandBus\CommandBus;
use Ekklesion\Core\Infrastructure\Http\Controller;
use Ekklesion\Core\Infrastructure\Http\Middleware\AuthenticationMiddleware;
use Ekklesion\Core\Infrastructure\Http\Middleware\LocalizationMiddleware;
use Ekklesion\Core\Infrastructure\Http\Middleware\RequiresAuthenticationMiddleware;
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
    public const NAME = 'core';

    public function getModuleName(): string
    {
        return self::NAME;
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
            CommandBus::class => new ServiceFactory\CommandBusFactory(),
            Authenticator::class => new ServiceFactory\JwtAuthenticatorFactory(),
            'notFoundHandler' => new ServiceFactory\NotFoundHandlerFactory(),

            // Middleware
            AuthenticationMiddleware::class => new MiddlewareFactory\AuthenticationMiddlewareFactory(),
            RequiresAuthenticationMiddleware::class => new MiddlewareFactory\RequiresAuthenticationMiddlewareFactory(),
            LocalizationMiddleware::class => new MiddlewareFactory\LocalizationMiddlewareFactory(),

            // Repository
            AccountRepository::class => new AccountRepositoryFactory(),

            // Command => Command Handler
            Command\CreateAccount::class => new HandlerFactory\CreateAccountHandlerFactory(),
            Command\Login::class => new HandlerFactory\LoginHandlerFactory(),
        ];
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return [
            'secret' => getenv('APP_SECRET'),
            'db_url' => getenv('DATABASE_URL'),
            'env' => getenv('APP_ENV'),
            'log_path' => getenv('LOG_PATH'),
            'locale' => getenv('LOCALE'),
            'login_route' => '/auth/login',
        ];
    }

    public function loadResources(ResourceLoader $resourceLoader): void
    {
        $resourceLoader->loadTemplate('core', __DIR__.'/Resources/templates');
        $resourceLoader->loadORMMapping('Ekklesion\Core\Domain\Model', __DIR__.'/Resources/mappings');
        $resourceLoader->loadORMType('uuid', Types\UuidType::class);
        $resourceLoader->loadORMType('chronos', Types\ChronosType::class);
        $resourceLoader->loadORMType('filename', Types\FilenameType::class);
    }

    public function loadMiddleware(MiddlewareLoader $middlewareLoader): void
    {
        $middlewareLoader->load(100, AuthenticationMiddleware::class);
        $middlewareLoader->load(200, LocalizationMiddleware::class);
    }

    public function loadRoutes(RouteLoader $routeLoader): void
    {
        $routeLoader->get('/', Controller\HomeController::class)
            ->add(RequiresAuthenticationMiddleware::class);

        // Auth Endpoints
        $routeLoader->group('/auth', function () use ($routeLoader) {
            $routeLoader->get('/login', Controller\SecurityController::class.':renderLogin');
            $routeLoader->post('/login', Controller\SecurityController::class.':doLogin');
            $routeLoader->post('/create-account', Controller\SecurityController::class.':createAccount');
            $routeLoader->post('/logout', Controller\SecurityController::class.':logout');
            $routeLoader->get('/install', Controller\SecurityController::class.':install');
            $routeLoader->post('/install', Controller\SecurityController::class.':doInstall');
        });
    }
}
