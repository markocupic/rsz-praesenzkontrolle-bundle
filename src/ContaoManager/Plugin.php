<?php

/**
 * @copyright  Marko Cupic 2022 <m.cupic@gmx.ch>
 * @license    MIT
 *
 * @see        https://github.com/markocupic/rsz-praesenzkontrolle-bundle
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

namespace Markocupic\RszPraesenzkontrolleBundle\ContaoManager;

use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ConfigPluginInterface;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * Class Plugin.
 */
class Plugin implements BundlePluginInterface, ConfigPluginInterface
{
    /**
     * @return array
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create('Markocupic\RszPraesenzkontrolleBundle\MarkocupicRszPraesenzkontrolleBundle')
                ->setLoadAfter(['Contao\CoreBundle\ContaoCoreBundle']),
        ];
    }

    /**
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader, array $managerConfig): void
    {
        $loader->load(__DIR__.'/../Resources/config/parameters.yml');
        $loader->load(__DIR__.'/../Resources/config/services.yml');
        $loader->load(__DIR__.'/../Resources/config/listener.yml');
    }
}
