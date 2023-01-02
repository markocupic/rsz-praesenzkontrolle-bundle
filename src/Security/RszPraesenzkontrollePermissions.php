<?php

declare(strict_types=1);

/*
 * This file is part of RSZ Präsenzkontrolle Bundle.
 *
 * (c) Marko Cupic 2023 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/rsz-praesenzkontrolle-bundle
 */

namespace Markocupic\RszPraesenzkontrolleBundle\Security;

final class RszPraesenzkontrollePermissions
{
    /**
     * Access is granted if the current user can perform an operation on tl_rsz_praesenzkontrolle.
     * Subject must be an operation: Either delete or download.
     */
    public const USER_CAN_PERFORM_OPERATION = 'contao_user.rsz_praesenzkontrollep';
}
