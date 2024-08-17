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

namespace Markocupic\RszPraesenzkontrolleBundle\Controller\ContaoBackend;

use Contao\CoreBundle\Exception\AccessDeniedException;
use Markocupic\RszPraesenzkontrolleBundle\Excel\RszPraesenzkontrolleDownload;
use Markocupic\RszPraesenzkontrolleBundle\Security\RszPraesenzkontrollePermissions;
use PhpOffice\PhpSpreadsheet\Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

#[Route('/contao/_rsz_praesenzkontrolle_download', name: 'markocupic_rsz_praesenzkontrolle_download', defaults: ['_scope' => 'backend'])]
class RszPraesenzkontrolleDownloadController
{
    private Security $security;
    private RszPraesenzkontrolleDownload $rszPraesenzkontrolleDownload;

    public function __construct(Security $security, RszPraesenzkontrolleDownload $rszPraesenzkontrolleDownload)
    {
        $this->security = $security;
        $this->rszPraesenzkontrolleDownload = $rszPraesenzkontrolleDownload;
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(): Response
    {
        if (!$this->security->isGranted('ROLE_ADMIN') && !$this->security->isGranted(RszPraesenzkontrollePermissions::USER_CAN_PERFORM_OPERATION, 'download')) {
            throw new AccessDeniedException('Not enough permissions to download tl_praesenzkontrolle.');
        }

        return $this->rszPraesenzkontrolleDownload->download();
    }
}
