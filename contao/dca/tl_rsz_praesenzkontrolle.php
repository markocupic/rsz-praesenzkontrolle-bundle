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

use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\DC_Table;
use Contao\DataContainer;
use Contao\Backend;
use Contao\StringUtil;
use Contao\System;
use Markocupic\RszPraesenzkontrolleBundle\Security\RszPraesenzkontrollePermissions;
use Markocupic\RszPraesenzkontrolleBundle\Excel\RszPraesenzkontrolleDownload;

$GLOBALS['TL_DCA']['tl_rsz_praesenzkontrolle'] = [
    // Config
    'config'   => [
        'dataContainer'    => DC_Table::class,
        'pTable'           => 'tl_rsz_jahresprogramm',
        'enableVersioning' => true,
        'closed'           => false,
        'notCopyable'      => false,
        // Except admins (see tl_rsz_praesenzkontrolle.modifyDca())
        'notDeletable'     => false,
        'sql'              => [
            'keys' => [
                'id'  => 'primary',
                'pid' => 'index',
            ],
        ],
        'onload_callback'  => [
            [
                tl_rsz_praesenzkontrolle::class,
                'createAllEvents',
            ],
            [
                tl_rsz_praesenzkontrolle::class,
                'checkPermissions',
            ],
        ],
    ],
    'list'     => [
        'sorting'           => [
            'mode'            => DataContainer::MODE_SORTABLE,
            'fields'          => ['start_date'],
            'flag'            => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout'     => 'filter;sort,search,limit',
            'disableGrouping' => true,
        ],
        'label'             => [
            'fields'         => [
                'start_date',
                'event',
            ],
            'format'         => '<span>#STATUS# %s [%s]&nbsp;&nbsp;&nbsp;Trainer: #TRAINERS#</span>',
            'label_callback' => [tl_rsz_praesenzkontrolle::class, 'labelCallback'],
        ],
        'global_operations' => [
            'all'         => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
            'excelExport' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_rsz_praesenzkontrolle']['excelExport'],
                'href'       => 'act=excelExport',
                'class'      => 'header_icon',
                'icon'       => 'bundles/markocupicrszpraesenzkontrolle/excel.svg',
                'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="i"',
            ],
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_rsz_praesenzkontrolle']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.svg',
            ],
            'delete' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_rsz_praesenzkontrolle']['delete'],
                'href'            => 'act=delete',
                'icon'            => 'delete.svg',
                'attributes'      => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
                'button_callback' => ['tl_rsz_praesenzkontrolle', 'deleteElement'],
            ],
            'show'   => [
                'label'      => &$GLOBALS['TL_LANG']['tl_rsz_praesenzkontrolle']['show'],
                'href'       => 'act=show',
                'icon'       => 'show.svg',
                'attributes' => 'style="margin-right:3px"',
            ],
        ],
    ],
    // Palettes
    'palettes' => [
        'default' => '{event_legend},start_date,end_date,event,hours;{participants},athletes,trainers;{Kommentar zum Training},comment',
    ],
    // Fields
    'fields'   => [
        'id'         => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid'        => [
            'foreignKey' => 'tl_rsz_jahresprogramm.id',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'tstamp'     => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'start_date' => [
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_DAY_ASC,
            'eval'      => ['readonly' => true, 'mandatory' => true, 'datepicker' => false, 'rgxp' => 'date', 'tl_class' => 'w50'],
            'sql'       => "int(10) unsigned NOT NULL default '0'",
        ],
        'end_date'   => [
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_DAY_ASC,
            'eval'      => ['readonly' => true, 'mandatory' => true, 'datepicker' => false, 'rgxp' => 'date', 'tl_class' => 'w50'],
            'sql'       => "int(10) unsigned NOT NULL default '0'",
        ],
        'event'      => [
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'filter'    => true,
            'sorting'   => true,
            'eval'      => ['readonly' => true, 'mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'trainers'   => [
            'exclude'          => true,
            'inputType'        => 'checkbox',
            'search'           => true,
            'sorting'          => true,
            'flag'             => DataContainer::SORT_INITIAL_LETTER_ASC,
            'options_callback' => [tl_rsz_praesenzkontrolle::class, 'getTrainers',],
            'eval'             => ['multiple' => true, 'tl_class' => ''],
            'sql'              => 'blob NULL',
        ],
        'athletes'   => [
            'exclude'          => true,
            'inputType'        => 'checkbox',
            'search'           => true,
            'sorting'          => true,
            'flag'             => DataContainer::SORT_INITIAL_LETTER_ASC,
            'options_callback' => [tl_rsz_praesenzkontrolle::class, 'getAthletes'],
            'eval'             => ['multiple' => true, 'tl_class' => ''],
            'sql'              => 'blob NULL',
        ],
        'hours'      => [
            'exclude'   => true,
            'inputType' => 'select',
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'options'   => [
                '3' => '3 Stunden',
                '5' => '5 Stunden',
            ],
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "varchar(2) NOT NULL default '3'",
        ],
        'comment'    => [
            'inputType' => 'textarea',
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'filter'    => true,
            'eval'      => ['tl_class' => '', 'rte' => false, 'allowHtml' => true, 'rows' => 4, 'style' => 'height: 80px;'],
            'sql'       => 'mediumtext NULL',
        ],
    ],
];

class tl_rsz_praesenzkontrolle extends Backend
{
    private const SORTING_DIRECTION_ATHLETES = 'name ASC';

    /**
     * tl_rsz_praesenzkontrolle constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');

        if ('excelExport' === $this->Input->get('act')) {
            $this->excelExport();
        }
    }

    /**
     * Excel export
     */
    private function excelExport(): void
    {

        $security = System::getContainer()->get('security.helper');

        if ($this->User->isAdmin || $security->isGranted(RszPraesenzkontrollePermissions::USER_CAN_EXPORT_RSZ_PRAESENZKONTROLLE)) {
            $objExport = System::getContainer()->get(RszPraesenzkontrolleDownload::class);
            $objExport->excelExport();
        }

        throw new AccessDeniedException('Not enough permissions to export tl_praesenzkontrolle.');

    }

    /**
     * Onload callback
     * Modify data container array.
     */
    public function checkPermissions(): void
    {
        if ($this->User->isAdmin) {
            return;
        }

        $GLOBALS['TL_DCA']['tl_rsz_praesenzkontrolle']['config']['closed'] = true;
        $GLOBALS['TL_DCA']['tl_rsz_praesenzkontrolle']['config']['notCopyable'] = true;
        $GLOBALS['TL_DCA']['tl_rsz_praesenzkontrolle']['config']['notDeletable'] = true;

        /** @var \Symfony\Component\Security\Core\Security $security */
        $security = System::getContainer()->get('security.helper');

        $granted = $security->isGranted(RszPraesenzkontrollePermissions::USER_CAN_EXPORT_RSZ_PRAESENZKONTROLLE);

        if (!$granted) {
            unset($GLOBALS['TL_DCA']['tl_rsz_praesenzkontrolle']['list']['global_operations']['excelExport']);
        }

        // Check current action
        if (Input::get('act') && Input::get('act') != 'paste') {
            $permission = null;

            // Set permission
            switch (Input::get('act')) {
                case 'edit':
                case 'toggle':
                case 'move':
                case 'create':
                case 'copy':
                case 'copyAll':
                case 'cut':
                case 'cutAll':
                    break;

                case 'delete':
                    $permission = RszPraesenzkontrollePermissions::USER_CAN_DELETE_ITEMS_IN_RSZ_PRAESENZKONTROLLE;
                    if (!$security->isGranted($permission)) {
                        throw new AccessDeniedException('Not enough permissions to delete items.');
                    }

                    break;
            }
        }
    }

    /**
     * Onload callback
     * Create all events.
     */
    public function createAllEvents(): void
    {
        $db = $this->Database->execute('SELECT * FROM tl_rsz_jahresprogramm');

        while ($db->next()) {
            $db2 = $this->Database
                ->prepare('SELECT * FROM tl_rsz_praesenzkontrolle WHERE pid=?')
                ->execute($db->id);

            $arrSet = [
                'start_date' => $db->start_date,
                'end_date'   => $db->end_date,
                'event'      => $db->art,
                'pid'        => $db->id,
            ];

            if (!$db2->numRows) {
                $this->Database->prepare('INSERT INTO tl_rsz_praesenzkontrolle %s')
                    ->set($arrSet)
                    ->execute();
            } else {
                $this->Database->prepare('UPDATE tl_rsz_praesenzkontrolle %s WHERE pid=?')->set($arrSet)->execute($db->id);
            }
        }
    }

    /**
     * Return the delete content element button
     *
     * @param array $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function deleteElement($row, $href, $label, $title, $icon, $attributes)
    {
        $granted = false;

        if ($this->User->isAdmin) {
            $granted = true;
        }

        // Disable the button if the element type is not allowed
        if (System::getContainer()->get('security.helper')->isGranted(RszPraesenzkontrollePermissions::USER_CAN_DELETE_ITEMS_IN_RSZ_PRAESENZKONTROLLE)) {
            $granted = true;
        }

        return $granted === false ? Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ' : '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
    }

    /**
     * Options callback
     * Return all athletes as array.
     */
    public function getAthletes(): array
    {
        $db = $this->Database
            ->prepare('SELECT id, name, niveau, trainingsgruppe FROM tl_user WHERE funktion LIKE ? ORDER BY '.static::SORTING_DIRECTION_ATHLETES)
            ->execute('%Athlet%');
        $array = [];

        while ($db->next()) {
            $trainingGroup = '' !== $db->trainingsgruppe ? 'Gr. '.$db->trainingsgruppe : '';
            $array[$db->id] = sprintf('%s [%s %s]', $db->name, $db->niveau, $trainingGroup);
        }

        return $array;
    }

    /**
     * Options callback
     * Return all trainers as array.
     */
    public function getTrainers(): array
    {
        $db = $this->Database->prepare('SELECT id, name FROM tl_user WHERE funktion LIKE ?')->execute('%Trainer%');
        $array = [];

        while ($db->next()) {
            $array[$db->id] = $db->name;
        }

        return $array;
    }

    /**
     * Label Callback.
     */
    public function labelCallback(array $row, string $label): string
    {
        $strTrainers = '';

        if (count(StringUtil::deserialize($row['trainers'], true))) {
            $arrTrainer = [];
            $objStmt = $this->Database->execute('SELECT username FROM tl_user WHERE id IN('.implode(',', array_map('intval', unserialize($row['trainers']))).')');

            while ($objStmt->next()) {
                $arrTrainer[] = $objStmt->username;
            }
            $strTrainers = implode(', ', $arrTrainer);
        }

        $label = str_replace('#TRAINERS#', $strTrainers, $label);

        $mysql = $this->Database->prepare('SELECT * FROM tl_rsz_praesenzkontrolle WHERE id=?')->execute($row['id']);

        if (time() > $mysql->start_date) {
            $status = '<div style="display:inline; padding-right:3px;"><img src="bundles/markocupicrszpraesenzkontrolle/check.svg" alt="history" title="abgelaufen"></div>';
        } else {
            $status = '<div style="display:inline; padding-right:15px;">&nbsp;</div>';
        }

        return str_replace('#STATUS#', $status, $label);
    }
}