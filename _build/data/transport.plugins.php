<?php
/**
 * Package in plugins
 *
 * @package remotes
 * @subpackage build
 */
$plugins = array();

/* create the plugin object */
$plugins['RemoteCommands'] = $modx->newObject('modPlugin');
$plugins['RemoteCommands']->set('name','RemoteCommands');
$plugins['RemoteCommands']->set('description','Registers a web node for receiving and executing remote commands.');
$plugins['RemoteCommands']->set('plugincode', file_get_contents($sources['plugins'] . 'RemoteCommands.php'));

/* create the plugin object */
$plugins['SendClearCache'] = $modx->newObject('modPlugin');
$plugins['SendClearCache']->set('name','SendClearCache');
$plugins['SendClearCache']->set('description','Send a remote clearCache command to all registered web nodes.');
$plugins['SendClearCache']->set('plugincode', file_get_contents($sources['plugins'] . 'SendClearCache.php'));

return $plugins;
