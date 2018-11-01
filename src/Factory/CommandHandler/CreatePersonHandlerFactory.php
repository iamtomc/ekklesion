<?php

/*
 * This file is part of the IglesiaUNO\People project.
 * (c) Matías Navarro Carter <mnavarrocarter@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IglesiaUNO\People\Factory\CommandHandler;

use IglesiaUNO\People\Domain\Repository\PersonRepository;
use IglesiaUNO\People\Infrastructure\CommandHandler\CreatePersonHandler;
use Psr\Container\ContainerInterface;

/**
 * Class CreatePersonHandlerFactory.
 *
 * @author Matías Navarro Carter <mnavarro@option.cl>
 */
class CreatePersonHandlerFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return CreatePersonHandler
     */
    public function __invoke(ContainerInterface $container)
    {
        $handler = new CreatePersonHandler();
        $handler->setPeople($container->get(PersonRepository::class));

        return $handler;
    }
}
