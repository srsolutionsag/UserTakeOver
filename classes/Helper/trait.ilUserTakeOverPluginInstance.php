<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

declare(strict_types=1);

use ILIAS\DI\Container;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
trait ilUserTakeOverPluginInstance
{
    /**
     * This method can be used to retrieve the plugin instance. It will only ever
     * be created once and if this method is used the instance can be considered
     * a singleton.
     */
    protected function getPlugin(Container $dic): ilUserTakeOverPlugin
    {
        /** @var $component_factory ilComponentFactory */
        $component_factory = $dic['component.factory'];
        /** @var $plugin ilUserTakeOverPlugin */
        $plugin = $component_factory->getPlugin(ilUserTakeOverPlugin::PLUGIN_ID);

        return $plugin;
    }
}
