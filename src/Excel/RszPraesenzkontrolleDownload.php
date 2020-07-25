<?php

/**
 * @copyright  Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author     Marko Cupic
 * @package    RSZ Benutzerverwaltung
 * @license    MIT
 * @see        https://github.com/markocupic/rsz-benutzerverwaltung-bundle
 *
 */

declare(strict_types=1);

namespace Markocupic\RszPraesenzkontrolleBundle\Excel;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\Date;
use Contao\Input;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Class RszPraesenzkontrolleDownload
 * @package Markocupic\RszBenutzerverwaltungBundle\Excel
 */
class RszPraesenzkontrolleDownload
{
    /** @var ContaoFramework */
    private $framework;

    /** @var array */
    protected $opt;

    /**
     * RszPraesenzkontrolleDownload constructor.
     * @param ContaoFramework $framework
     */
    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
        $this->opt = [
            'sortingDirection' => 'tl_user.name ASC',
        ];
    }

    /**
     * @param array $opt
     * @return Response
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function excelExport(array $opt = [])
    {
        /** @var Date $dateAdapter */
        $dateAdapter = $this->framework->getAdapter(Date::class);

        // Get data
        $arrData = $this->prepareData();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->setTitle("Präsenzkontrolle " . $dateAdapter->parse("Y"));
        $spreadsheet->setActiveSheetIndex(0);

        foreach ($arrData as $intRow => $arrRow)
        {
            foreach ($arrRow as $intColumn => $strValue)
            {
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($intColumn + 1, $intRow + 1, $strValue);
            }
        }

        // Set Text Rotation to top row
        $spreadsheet->getActiveSheet()->getStyle("A1:ZZ1")->getAlignment()->setTextRotation(90);

        // Set height of top row
        $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(75);

        // Send file to browser
        $objWriter = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"praesenzkontrolle_rsz_" . $dateAdapter->parse("Y-m-d") . ".xlsx\"");
        header("Cache-Control: max-age=0");
        $objWriter->save("php://output");
        exit;
    }

    /**
     * @return array
     */
    private function prepareData()
    {
        /** @var Database $databaseAdapter */
        $databaseAdapter = $this->framework->getAdapter(Database::class);

        // Trainer Datenarray erstellen
        $data_arr_trainer = [];
        $db = $databaseAdapter->getInstance()->prepare('SELECT id, name FROM tl_user WHERE funktion LIKE ? ORDER BY name ASC')->execute('%Trainer%');
        while ($db->next())
        {
            $data_arr_trainer[$db->id]['name'] = $db->name;
            $data_arr_trainer[$db->id]['funktion'] = 'Trainer';
        }

        // Athleten Datenarray erstellen
        $data_arr_athlete = [];
        $db = $databaseAdapter->getInstance()->prepare("SELECT id, name FROM tl_user WHERE funktion LIKE ? ORDER BY " . $this->opt['sortingDirection'])->execute('%Athlet%');

        while ($db->next())
        {
            $data_arr_athlete[$db->id]['name'] = $db->name;
            $data_arr_athlete[$db->id]['funktion'] = 'Athlet';
        }

        // Hier wird geprüft, ob der Trainer am Anlass anwesend war oder nicht
        // und dann werden die entsprechenden Werte ins dataArr geschrieben
        $db = $databaseAdapter->getInstance()->execute('SELECT * FROM tl_rsz_praesenzkontrolle ORDER BY start_date');
        while ($db->next())
        {
            $trainer_arr = [];
            if (is_array(unserialize($db->trainers)))
            {
                $trainer_arr = unserialize($db->trainers);
            }

            foreach ($data_arr_trainer as $key => $username)
            {
                if (in_array($key, $trainer_arr))
                {
                    $data_arr_trainer[$key][$db->pid] = ['hours' => $db->hours, 'start_date' => $db->start_date, 'event' => $db->event];
                }
                else
                {
                    $data_arr_trainer[$key][$db->pid] = ['hours' => '', 'start_date' => $db->start_date, 'event' => $db->event];
                }
            }

            // Hier wird geprüft, ob der Athlet am Anlass anwesend war oder nicht
            // und dann werden die entsprechenden Werte ins dataArr geschrieben
            $athl_arr = [];
            if (is_array(unserialize($db->athletes)))
            {
                $athl_arr = unserialize($db->athletes);
            }

            foreach ($data_arr_athlete as $key => $username)
            {
                if (in_array($key, $athl_arr))
                {
                    $data_arr_athlete[$key][$db->pid] = ['hours' => $db->hours, 'start_date' => $db->start_date, 'event' => $db->event];
                }
                else
                {
                    $data_arr_athlete[$key][$db->pid] = ['hours' => '', 'start_date' => $db->start_date, 'event' => $db->event];
                }
            }
        }
        // Hilfsarrays mit dem
        // Datum aller Anlässe für die erste Zeile
        // und mir der pid (id) von jedem Anlass
        $db = $databaseAdapter->getInstance()->execute('SELECT pid, start_date, comment FROM tl_rsz_praesenzkontrolle ORDER BY start_date');
        $dateArr = [];
        $eventId = [];
        $eventComments = [];
        while ($db->next())
        {
            $dateArr[] = $db->start_date;
            $eventId[] = $db->pid;
            $eventComments[] = $db->comment;
        }
        // Count all events
        $countEvents = count($dateArr);

        // Hilfsarrays mit den Eventdaten
        $eventArr = [];
        foreach ($eventId as $id)
        {
            $db = $databaseAdapter->getInstance()->prepare('SELECT art FROM tl_rsz_jahresprogramm WHERE id=?')->execute($id);
            $eventArr[] = ['art' => $db->art];
        }

        $arrRows = [];

        // 1. Zeile mit Eventdatum
        $arrRow = [];
        // Leerzelle
        $arrRow[] = '';
        foreach ($dateArr as $date)
        {
            $arrRow[] = $date;
        }
        $arrRows[] = $arrRow;

        // 2. Zeile Event-Art des Events/Trainings
        $arrRow = [];
        // Leerzelle
        $arrRow[] = '';
        foreach ($eventArr as $event)
        {
            $arrRow[] = $event['art'];
        }
        $arrRows[] = $arrRow;

        // Eine Leerzeile
        $arrRows[] = [''];

        // Eine Leerzeile mit Übertitel "Trainer"
        $arrRows[] = ['Trainer'];
        // Zeilen mit den Trainern
        foreach ($data_arr_trainer as $key => $userArr)
        {
            $arrRow = [];
            $arrRow[] = $data_arr_trainer[$key]['name'];
            foreach ($eventId as $eventPid)
            {
                $arrRow[] = $data_arr_trainer[$key][$eventPid]['hours'];
            }
            $arrRows[] = $arrRow;
        }

        // Eine Leerzeile
        $arrRows[] = [''];

        // Eine Leerzeile mit Übertitel "Athleten"
        $arrRows[] = ['Athleten'];

        // Datenzeilen mit den Athleten
        foreach ($data_arr_athlete as $key => $userArr)
        {
            $arrRow = [];
            $arrRow[] = $data_arr_athlete[$key]['name'];
            foreach ($eventId as $eventPid)
            {
                $arrRow[] = $data_arr_athlete[$key][$eventPid]['hours'];
            }
            $arrRows[] = $arrRow;
        }

        // Eine Leerzeile
        $arrRows[] = [''];

        // Zeile mit Bemerkungen
        $arrRow = [];
        $arrRow[] = 'Bemerkungen';
        foreach ($eventComments as $eventComment)
        {
            $arrRow[] = utf8_decode(Input::stripTags($eventComment));
        }
        $arrRows[] = $arrRow;

        return $arrRows;
    }

}
