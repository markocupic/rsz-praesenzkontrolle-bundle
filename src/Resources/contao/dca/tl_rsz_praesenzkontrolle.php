<?php

/**
 * @copyright  Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author     Marko Cupic
 * @package    Rsz Praesenzkontrolle
 * @license    MIT
 * @see        https://github.com/markocupic/rsz-praesenzkontrolle-bundle
 *
 */

/**
 * Table tl_rsz_praesenzkontrolle
 */
$GLOBALS['TL_DCA']['tl_rsz_praesenzkontrolle'] = [

    // Config
    'config'   => [
        'dataContainer'    => 'Table',
        'pTable'           => 'tl_jahresprogramm',
        'enableVersioning' => true,
        'closed'           => true,
        'notCopyable'      => true,
        // Except admins (see tl_rsz_praesenzkontrolle.modifyDca())
        'notDeletable'     => true,
        'sql'              => [
            'keys' => [
                'id'  => 'primary',
                'pid' => 'index',
            ]
        ],
        'onload_callback'  => [
            ['tl_rsz_praesenzkontrolle', 'createAllEvents'],
            ['tl_rsz_praesenzkontrolle', 'modifyDca'],
        ],
    ],
    'list'     => [
        'sorting'           => [
            'mode'            => 2,
            'fields'          => ['start_date'],
            'flag'            => 1,
            'panelLayout'     => 'filter;sort,search,limit',
            'disableGrouping' => true,
        ],
        'label'             => [
            'fields'         => ['start_date', 'event'],
            'format'         => '%s',
            'format'         => '<span>#STATUS# %s [%s]&nbsp;&nbsp;&nbsp;Trainer: #TRAINERS#</span>',
            'label_callback' => ['tl_rsz_praesenzkontrolle', 'labelCallback'],
        ],
        'global_operations' => [
            'all'         => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
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
                'icon'  => 'edit.gif'
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_rsz_praesenzkontrolle']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ],
            'show'   => [
                'label'      => &$GLOBALS['TL_LANG']['tl_rsz_praesenzkontrolle']['show'],
                'href'       => 'act=show',
                'icon'       => 'show.gif',
                'attributes' => 'style="margin-right:3px"'
            ],
        ]
    ],
    // Palettes
    'palettes' => ['__selector__' => [],
                   'default'      => 'start_date,end_date,event,hours;{participiants},athletes,trainers;{Kommentar zum Training},comment'
    ],
    // Fields
    'fields'   => [
        'id'         => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"],
        'pid'        => [
            'foreignKey' => 'tl_jahresprogramm.id',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'lazy']
        ],
        'tstamp'     => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'start_date' => [
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'sorting'   => true,
            'eval'      => ['readonly' => true, 'mandatory' => true, 'maxlength' => 10, 'tl_class' => 'w50'],
            'sql'       => "varchar(30) NOT NULL default ''"
        ],
        'end_date'   => [
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'sorting'   => true,
            'eval'      => ['readonly' => true, 'mandatory' => true, 'maxlength' => 10, 'tl_class' => 'w50'],
            'sql'       => "varchar(30) NOT NULL default ''"
        ],
        'event'      => [
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'filter'    => true,
            'sorting'   => true,
            'eval'      => ['readonly' => true, 'mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'trainers'   => [
            'exclude'          => true,
            'inputType'        => 'checkbox',
            'search'           => true,
            'sorting'          => true,
            'flag'             => 1,
            'options_callback' => ['tl_rsz_praesenzkontrolle', 'getTrainers'],
            'eval'             => ['multiple' => true, 'tl_class' => ''], 'sql' => "text NOT NULL"
        ],
        'athletes'   => [
            'exclude'          => true,
            'inputType'        => 'checkbox',
            'search'           => true,
            'sorting'          => true,
            'flag'             => 1,
            'options_callback' => ['tl_rsz_praesenzkontrolle', 'getAthletes'],
            'eval'             => ['multiple' => true, 'tl_class' => ''],
            'sql'              => "text NOT NULL"
        ],
        'hours'      => [
            'exclude'   => true,
            'inputType' => 'select',
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'flag'      => 1,
            'default'   => '3',
            'options'   => ['3' => '3 Stunden', '5' => '5 Stunden'],
            'eval'      => ['tl_class' => 'w50'], 'sql' => "varchar(2) NOT NULL default ''"

        ],
        'comment'    => [
            'inputType' => 'textarea',
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'filter'    => true,
            'eval'      => ['tl_class' => '', 'rte' => false, 'allowHtml' => true, 'rows' => 4, 'style' => 'height: 80px;'],
            'sql'       => "text NOT NULL"
        ]
    ]
];

/**
 * Class tl_rsz_praesenzkontrolle
 */
class tl_rsz_praesenzkontrolle extends Contao\Backend
{

    /** @var string */
    private const SORTING_DIRECTION_ATHLETES = 'name ASC';

    /**
     * tl_rsz_praesenzkontrolle constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');

        if ($this->Input->get('act') === 'excelExport')
        {
            $this->excelExport();
        }
    }

    /**
     * Onload callback
     * Modify data container array
     */
    public function modifyDca()
    {
        if (!$this->User->isAdmin)
        {
            unset($GLOBALS['TL_DCA']['tl_rsz_praesenzkontrolle']['config']['notDeletable']);
            unset($GLOBALS['TL_DCA']['tl_rsz_praesenzkontrolle']['list']['operations']['delete']);
            unset($GLOBALS['TL_DCA']['tl_rsz_praesenzkontrolle']['list']['global_operations']['all']);
        }
    }

    /**
     * Onload callback
     * Create all events
     */
    public function createAllEvents()
    {
        $db = $this->Database->execute('SELECT * FROM tl_jahresprogramm');

        while ($db->next())
        {
            $db2 = $this->Database
                ->prepare('SELECT * FROM tl_rsz_praesenzkontrolle WHERE pid=?')
                ->execute($db->id);
            if ($db2->numRows < 1)
            {
                $arrSet = ['start_date' => \Contao\Date::parse("Y-m-d", $db->start_date), 'end_date' => \Contao\Date::parse("Y-m-d", $db->end_date), 'event' => $db->art, 'pid' => $db->id,];
                $this->Database->prepare("INSERT INTO tl_rsz_praesenzkontrolle %s")
                    ->set($arrSet)
                    ->execute();
            }
            else
            {
                $arrSet = [
                    'start_date' => \Contao\Date::parse("Y-m-d", $db->start_date),
                    'end_date'   => \Contao\Date::parse("Y-m-d", $db->end_date),
                    'event'      => $db->art,
                    'pid'        => $db->id,
                ];
                $this->Database->prepare('UPDATE tl_rsz_praesenzkontrolle %s WHERE pid=?')->set($arrSet)->execute($db->id);
            }
        }
    }

    /**
     * Options callback
     * Return all athletes as array
     * @return array
     */
    public function getAthletes()
    {
        $db = $this->Database
            ->prepare('SELECT id, name, niveau, trainingsgruppe FROM tl_user WHERE funktion LIKE ? ORDER BY ' . static::SORTING_DIRECTION_ATHLETES)
            ->execute('%Athlet%');
        $array = [];
        while ($db->next())
        {
            $trainingGroup = $db->trainingsgruppe != '' ? 'Gr. ' . $db->trainingsgruppe : '';
            $array[$db->id] = sprintf('%s [%s %s]', $db->name, $db->niveau, $trainingGroup);
        }
        return $array;
    }

    /**
     * Options callback
     * Return all trainers as array
     * @return array
     */
    public function getTrainers()
    {
        $db = $this->Database->prepare('SELECT id, name FROM tl_user WHERE funktion LIKE ?')->execute('%Trainer%');
        $array = [];
        while ($db->next())
        {
            $array[$db->id] = $db->name;
        }
        return $array;
    }

    /**
     * Excel export
     */
    private function excelExport()
    {
        $objExport = \Contao\System::getContainer()
            ->get('Markocupic\RszPraesenzkontrolleBundle\BenutzerverwaltungBundle\Excel\RszPraesenzkontrolleDownload');
        $objExport->excelExport();
    }

    /**
     * Label Callback
     * @param array
     * @param string
     * @return string
     */
    public function labelCallback($row, $label)
    {
        $strTrainers = "";
        if (count(\Contao\StringUtil::deserialize($row['trainers'], true)))
        {
            $arrTrainer = [];
            $objStmt = $this->Database->execute("SELECT username FROM tl_user WHERE id IN(" . implode(',', array_map('intval', unserialize($row['trainers']))) . ")");
            while ($objStmt->next())
            {
                $arrTrainer[] = $objStmt->username;
            }
            $strTrainers = implode(', ', $arrTrainer);
        }

        $label = str_replace('#TRAINERS#', $strTrainers, $label);

        $mysql = $this->Database->prepare('SELECT start_date,trainers FROM tl_rsz_praesenzkontrolle WHERE id=?')->execute($row['id']);
        if (time() > strtotime($mysql->start_date))
        {
            $status = '<div style="display:inline; padding-right:3px;"><img src="bundles/markocupicrszpraesenzkontrolle/check.svg" alt="history" title="abgelaufen"></div>';
        }
        else
        {
            $status = '<div style="display:inline; padding-right:15px;">&nbsp;</div>';
        }
        $label = str_replace('#STATUS#', $status, $label);
        return $label;
    }

}


