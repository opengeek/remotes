<?php
/**
 * Remotes
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
 */
/**
 * Resolve events for RemoteCommands plugin
 *
 * @package remotes
 * @subpackage build
 */
$success = array();
if ($object && $pluginid= $object->get('id')) {
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            if (isset($options['activateRemoteCommands']) && !empty($options['activateRemoteCommands'])) {
                $events = array(
                    'OnHandleRequest'
                );
                foreach ($events as $eventName) {
                    $event = $object->xpdo->getObject('modEvent',array('name' => $eventName));
                    if ($event) {
                        $pluginEvent = $object->xpdo->getObject('modPluginEvent',array(
                                'pluginid' => $pluginid,
                                'event' => $event->get('name'),
                            ));
                        if (!$pluginEvent) {
                            $pluginEvent= $object->xpdo->newObject('modPluginEvent');
                            $pluginEvent->set('pluginid', $pluginid);
                            $pluginEvent->set('event', $event->get('name'));
                            $pluginEvent->set('priority', 0);
                            $pluginEvent->set('propertyset', 0);
                            $success[$eventName]= $pluginEvent->save();
                        }
                    }
                    unset($event,$pluginEvent);
                }
                unset($events,$eventName);
            }
            break;
        case xPDOTransport::ACTION_UNINSTALL: break;
    }
}
return array_search(false, $success, true) === false;
