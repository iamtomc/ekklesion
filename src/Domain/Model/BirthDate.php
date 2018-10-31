<?php

/*
 * This file is part of the IglesiaUNO\People project.
 * (c) Matías Navarro Carter <mnavarrocarter@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IglesiaUNO\People\Domain\Model;

use Cake\Chronos\Chronos;

/**
 * Class BirthDate.
 *
 * @author Matías Navarro Carter <mnavarro@option.cl>
 */
class BirthDate extends Chronos
{
    /**
     * @return int
     */
    public function age(): int
    {
        return $this->diffInYears(Chronos::now());
    }
}
