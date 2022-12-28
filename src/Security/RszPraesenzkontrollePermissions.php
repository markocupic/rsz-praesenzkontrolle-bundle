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

final class RszPraesenzkontrollePermissions
{
    public const USER_CAN_EXPORT_RSZ_PRAESENZKONTROLLE = 'rsz_praesenzkontrolle_permission.export';
    public const USER_CAN_DELETE_ITEMS_IN_RSZ_PRAESENZKONTROLLE = 'rsz_praesenzkontrolle_permission.delete_items';
}
