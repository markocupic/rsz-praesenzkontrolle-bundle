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

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Markocupic\RszPraesenzkontrolleBundle\Security\ContaoProjectPermissions;

// Extend the default palettes
PaletteManipulator::create()
    ->addLegend('rsz_praesenzkontrolle_legend', 'amg_legend', PaletteManipulator::POSITION_BEFORE)
    ->addField(['rsz_praesenzkontrollep'], 'rsz_praesenzkontrolle_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('extend', 'tl_user')
    ->applyToPalette('custom', 'tl_user');

// Add fields to tl_user
$GLOBALS['TL_DCA']['tl_user']['fields']['rsz_praesenzkontrollep'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'options'   => ['download', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC']['rsz_praesenzkontrolle'],
    'eval'      => ['multiple' => true],
    'sql'       => "blob NULL",
];
