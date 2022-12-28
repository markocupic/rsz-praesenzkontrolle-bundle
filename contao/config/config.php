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

/*
 * Backend modules
 */
$GLOBALS['BE_MOD']['rsz_tools']['rsz_praesenzkontrolle'] = [
    'tables' => ['tl_rsz_praesenzkontrolle'],
];

/**
 * Add permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'rsz_praesenzkontrollep';
