<?php

/*
 * This file is part of the Ekklesion\People project.
 * (c) Matías Navarro Carter <mnavarrocarter@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekklesion\People\Infrastructure\CommandHandler;

use Ekklesion\People\Domain\Command\CreateAccount;
use Ekklesion\People\Domain\Model\Account;
use Ekklesion\People\Domain\Presenter\AccountArrayPresenter;

/**
 * Class CreateAccountHandler.
 *
 * @author Matías Navarro Carter <mnavarro@option.cl>
 */
class CreateAccountHandler implements AccountsAware
{
    use Accounts;

    /**
     * @param CreateAccount $command
     *
     * @return Account
     */
    public function __invoke(CreateAccount $command)
    {
        $this->ensureUsernameIsUnique($command->username());

        $account = Account::create($command->username(), $command->plainPassword());
        $this->accounts->add($account);

        return \call_user_func(new AccountArrayPresenter(), $account);
    }
}