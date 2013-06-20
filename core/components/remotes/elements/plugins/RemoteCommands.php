<?php
/**
 * RemoteCommands plugin
 *
 * NOTE: register with OnHandleRequest OR OnWebPageComplete event
 *
 * Copyright 2013 by Jason Coward <jason+remotes@modx.com>
 *
 * Remotes is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * Remotes is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Remotes; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package remotes
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

/* define the IP of the master instance which does not need to execute remote commands */
$master_instance = $modx->getOption('master_instance', $scriptProperties, null);

/* get the instance IP */
$instance = $_SERVER['SERVER_ADDR'];

/* the number of seconds the remote instance is registered for */
$seconds = !empty($seconds) ? intval($seconds) : 1440;

/* find any remote commands to execute from the master instance */
if (!empty($instance) && $modx->getService('registry', 'registry.modRegistry') && (empty($master_instance) || $instance !== $master_instance)) {
    $modx->registry->addRegister('remotes', 'registry.modDbRegister', array('directory' => 'remotes'));
    $modx->registry->remotes->connect();

    /* if not already registered, register this instance for $seconds */
    $modx->registry->remotes->subscribe("/distrib/instances/{$instance}");
    $registration = $modx->registry->remotes->read(array('poll_limit' => 1, 'msg_limit' => 1, 'remove_read' => false));
    $modx->registry->remotes->unsubscribe("/distrib/instances/{$instance}");
    if (empty($registration) || !reset($registration)) {
        $modx->registry->remotes->subscribe("/distrib/instances/");
        $modx->registry->remotes->send("/distrib/instances/", array($instance => "{$instance}"), array('ttl' => $seconds));
        $modx->registry->remotes->unsubscribe("/distrib/instances/");
    }

    /* find any valid command messages for this instance and act on them */
    $modx->registry->remotes->subscribe("/distrib/commands/{$instance}/");
    $commands = $modx->registry->remotes->read(array('poll_limit' => 1, 'msg_limit' => 1));
    $modx->registry->remotes->unsubscribe("/distrib/commands/{$instance}/");
    $executed = array();
    if (!empty($commands)) {
        $command = reset($commands);
        while (!empty($command) && !in_array($command, $executed)) {
            /* customize this with your own command handlers */
            switch ($command) {
                /* refresh the remote instance's cache */
                case 'clearCache':
                    switch ($modx->event->name) {
                        case 'OnHandleRequest':
                            $modx->reloadConfig();
                            $modx->reloadContext();
                            break;
                        case 'OnWebPageComplete':
                            $results = $modx->cacheManager->refresh();
                            break;
                    }
                    break;
                case 'clearErrorLog':
                    /* empty the remote instance's error.log */
                    $modx->cacheManager->writeFile($modx->getCachePath() . 'logs/error.log', '', 'wb');
                    break;
                default:
                    break;
            }
            $executed[] = $command;
            $command = next($commands);
        }
    }
}
