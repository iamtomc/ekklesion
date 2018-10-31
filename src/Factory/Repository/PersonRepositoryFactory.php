<?php

/*
 * This file is part of the IglesiaUNO\People project.
 * (c) Matías Navarro Carter <mnavarrocarter@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IglesiaUNO\People\Factory\Repository;

use Doctrine\ORM\EntityRepository;
use IglesiaUNO\People\Domain\Repository\PersonRepository;

/**
 * Class PersonRepositoryFactory.
 *
 * @author Matías Navarro Carter <mnavarro@option.cl>
 */
class PersonRepositoryFactory extends EntityRepository implements PersonRepository
{
}
