<?php

/*
 * This file is part of the Ekklesion project.
 * (c) Matías Navarro Carter <mnavarrocarter@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekklesion\People\Factory\Service;

use Ekklesion\People\Infrastructure\Context\ApplicationSettings;
use Psr\Container\ContainerInterface;

/**
 * Class ApplicationSettingsFactory.
 *
 * @author Matías Navarro Carter <mnavarro@option.cl>
 */
class ApplicationSettingsFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return ApplicationSettings
     */
    public function __invoke(ContainerInterface $container)
    {
        return ApplicationSettings::fromFile($container->get('settings')['core']['settings_file']);
    }
}
