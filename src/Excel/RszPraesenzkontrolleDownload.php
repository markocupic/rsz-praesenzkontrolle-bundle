<?php

/**
 * @copyright  Marko Cupic 2022 <m.cupic@gmx.ch>
 * @license    MIT
 *
 * @see        https://github.com/markocupic/rsz-benutzerverwaltung-bundle
 */

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

namespace Markocupic\RszPraesenzkontrolleBundle\Excel;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\Date;
use Contao\Input;
use Contao\StringUtil;
use JetBrains\PhpStorm\NoReturn;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RszPraesenzkontrolleDownload
{
    private ContaoFramework $framework;
    protected array $opt;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
        $this->opt = [
            'sortingDirection' => 'tl_user.name ASC',
        ];
    }

    /**
     * @throws Exception
     */
    #[NoReturn]
 public function excelExport(): void
 {
     /** @var Date $dateAdapter */
     $dateAdapter = $this->framework->getAdapter(Date::class);

     // Get data
     $arrData = $this->prepareData();

     $spreadsheet = new Spreadsheet();
     $spreadsheet->getActiveSheet()->setTitle('Präsenzkontrolle '.$dateAdapter->parse('Y'));
     $spreadsheet->setActiveSheetIndex(0);

     foreach ($arrData as $intRow => $arrRow) {
         foreach ($arrRow as $intColumn => $strValue) {
             $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($intColumn + 1, $intRow + 1, $strValue);
         }
     }

     // Set Text Rotation to top row
     $spreadsheet->getActiveSheet()->getStyle('A1:ZZ1')->getAlignment()->setTextRotation(90);

     // Set height of top row
     $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(75);

     // Send file to browser
     $objWriter = new Xlsx($spreadsheet);
     header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
     header('Content-Disposition: attachment; filename="praesenzkontrolle_rsz_'.$dateAdapter->parse('Y-m-d').'.xlsx"');
     header('Cache-Control: max-age=0');
     $objWriter->save('php://output');
     exit;
 }

    private function prepareData(): array
    {
        /** @var Database $databaseAdapter */
        $databaseAdapter = $this->framework->getAdapter(Database::class);

        /** @var Date $dateAdapter */
        $dateAdapter = $this->framework->getAdapter(Date::class);

        /** @var StringUtil $stringUtilAdapter */
        $stringUtilAdapter = $this->framework->getAdapter(StringUtil::class);

        // Trainer Datenarray erstellen
        $data_arr_trainer = [];
        $db = $databaseAdapter->getInstance()->prepare('SELECT id, name FROM tl_user WHERE funktion LIKE ? ORDER BY name ASC')->execute('%Trainer%');

        while ($db->next()) {
            $data_arr_trainer[$db->id]['name'] = $db->name;
            $data_arr_trainer[$db->id]['funktion'] = 'Trainer';
        }

        // Athleten Datenarray erstellen
        $data_arr_athlete = [];
        $db = $databaseAdapter->getInstance()->prepare('SELECT id, name FROM tl_user WHERE funktion LIKE ? ORDER BY '.$this->opt['sortingDirection'])->execute('%Athlet%');

        while ($db->next()) {
            $data_arr_athlete[$db->id]['name'] = $db->name;
            $data_arr_athlete[$db->id]['funktion'] = 'Athlet';
        }

        // Hier wird geprüft, ob der Trainer am Anlass anwesend war oder nicht
        // und dann werden die entsprechenden Werte ins dataArr geschrieben
        $db = $databaseAdapter->getInstance()->execute('SELECT * FROM tl_rsz_praesenzkontrolle ORDER BY start_date');

        while ($db->next()) {
            $trainer_arr = $stringUtilAdapter->deserialize($db->trainers, true);

            foreach (array_keys($data_arr_trainer) as $key) {
                if (\in_array($key, $trainer_arr, true)) {
                    $data_arr_trainer[$key][$db->pid] = ['hours' => $db->hours, 'start_date' => $db->start_date, 'event' => $db->event];
                } else {
                    $data_arr_trainer[$key][$db->pid] = ['hours' => '', 'start_date' => $db->start_date, 'event' => $db->event];
                }
            }

            // Hier wird geprüft, ob der Athlet am Anlass anwesend war oder nicht
            // und dann werden die entsprechenden Werte ins dataArr geschrieben
            $athl_arr = $stringUtilAdapter->deserialize($db->athletes, true);

            foreach (array_keys($data_arr_athlete) as $key) {
                if (\in_array($key, $athl_arr, true)) {
                    $data_arr_athlete[$key][$db->pid] = ['hours' => $db->hours, 'start_date' => $db->start_date, 'event' => $db->event];
                } else {
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

        while ($db->next()) {
            $dateArr[] = $db->start_date;
            $eventId[] = $db->pid;
            $eventComments[] = $db->comment;
        }

        // Hilfsarrays mit den Eventdaten
        $eventArr = [];

        foreach ($eventId as $id) {
            $db = $databaseAdapter->getInstance()->prepare('SELECT art FROM tl_rsz_jahresprogramm WHERE id=?')->execute($id);
            $eventArr[] = ['art' => $db->art];
        }

        $arrRows = [];

        // 1. Zeile mit Eventdatum
        $arrRow = [];
        // Leerzelle
        $arrRow[] = '';

        foreach ($dateArr as $date) {
            $arrRow[] = $dateAdapter->parse('Y-m-d', $date);
        }
        $arrRows[] = $arrRow;

        // 2. Zeile Event-Art des Events/Trainings
        $arrRow = [];
        // Leerzelle
        $arrRow[] = '';

        foreach ($eventArr as $event) {
            $arrRow[] = $event['art'];
        }
        $arrRows[] = $arrRow;

        // Eine Leerzeile
        $arrRows[] = [''];

        // Eine Leerzeile mit Übertitel "Trainer"
        $arrRows[] = ['Trainer'];
        // Zeilen mit den Trainern
        foreach (array_keys($data_arr_trainer) as $key) {
            $arrRow = [];
            $arrRow[] = $data_arr_trainer[$key]['name'];

            foreach ($eventId as $eventPid) {
                $arrRow[] = $data_arr_trainer[$key][$eventPid]['hours'];
            }
            $arrRows[] = $arrRow;
        }

        // Eine Leerzeile
        $arrRows[] = [''];

        // Eine Leerzeile mit Übertitel "Athleten"
        $arrRows[] = ['Athleten'];

        // Datenzeilen mit den Athleten
        foreach (array_keys($data_arr_athlete) as $key) {
            $arrRow = [];
            $arrRow[] = $data_arr_athlete[$key]['name'];

            foreach ($eventId as $eventPid) {
                $arrRow[] = $data_arr_athlete[$key][$eventPid]['hours'];
            }
            $arrRows[] = $arrRow;
        }

        // Eine Leerzeile
        $arrRows[] = [''];

        // Zeile mit Bemerkungen
        $arrRow = [];
        $arrRow[] = 'Bemerkungen';

        foreach ($eventComments as $eventComment) {
            $arrRow[] = utf8_decode((string) Input::stripTags($eventComment));
        }
        $arrRows[] = $arrRow;

        return $arrRows;
    }
}
