<?php

declare(strict_types=1);

/*
 * This file is part of RSZ Präsenzkontrolle Bundle.
 *
 * (c) Marko Cupic 2022 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/rsz-praesenzkontrolle-bundle
 */

namespace Markocupic\RszPraesenzkontrolleBundle\Security;

use Contao\BackendUser;
use Contao\StringUtil;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RszPraesenzkontrolleVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!\in_array($attribute, [RszPraesenzkontrollePermissions::USER_CAN_EXPORT_RSZ_PRAESENZKONTROLLE], true)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof BackendUser) {
            // the user must be logged in; if not, deny access
            return false;
        }

        return match ($attribute) {
            RszPraesenzkontrollePermissions::USER_CAN_EXPORT_RSZ_PRAESENZKONTROLLE => $this->can('export', $user),
            RszPraesenzkontrollePermissions::USER_CAN_DELETE_ITEMS_IN_RSZ_PRAESENZKONTROLLE => $this->can('delete_item', $user),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function can(string $subject, BackendUser $user): bool
    {
        if ($user->isAdmin) {
            return true;
        }

        $arrPermissions = StringUtil::deserialize($user->rsz_praesenzkontrollep, true);

        if (\in_array($subject, $arrPermissions, true)) {
            return true;
        }

        return false;
    }
}
