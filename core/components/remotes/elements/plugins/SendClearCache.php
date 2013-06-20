<?php
/**
 * SendClearCache plugin
 *
 * NOTE: register with OnSiteRefresh event
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

/* only send remote commands if remotes.enabled == true */
if ($modx->getOption('remotes.enabled', $scriptProperties, false)) {
    /* seconds property defines a default ttl value for the clearCache message [default=0] */
    $seconds = !empty($seconds) ? intval($seconds) : 0;

    /* number of seconds to delay the clearCache message before it should be executed [default=0] */
    $delay = !empty($delay) ? intval($delay) : 0;

    /* number of seconds to stagger the clearCache messages between instances [default=0] */
    $stagger = !empty($stagger) ? intval($stagger) : 0;

    /* read instances and write clear cache msg to each command directory */
    if ($modx->getService('registry', 'registry.modRegistry')) {
        $modx->registry->addRegister('remotes', 'registry.modDbRegister', array('directory' => 'remotes'));
        $modx->registry->remotes->connect();
        $modx->registry->remotes->subscribe('/distrib/instances/');
        $instances = $modx->registry->remotes->read(array('poll_limit' => 1, 'msg_limit' => 200, 'remove_read' => false));
        $modx->registry->remotes->unsubscribe('/distrib/instances/');
        if (!empty($instances)) {
            $staggerCurrent = 0;
            foreach ($instances as $instance) {
                if ($instance == $_SERVER['SERVER_ADDR']) continue;
                $modx->registry->remotes->subscribe("/distrib/commands/{$instance}/");
                $modx->registry->remotes->send("/distrib/commands/{$instance}/", array('clearCache'), array('ttl' => $seconds, 'delay' => $delay + $staggerCurrent));
                $modx->registry->remotes->unsubscribe("/distrib/commands/{$instance}/");
                $staggerCurrent = $staggerCurrent + $stagger;
            }
        }
    }
}
