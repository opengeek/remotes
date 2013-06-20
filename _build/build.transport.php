<?php
/**
 * Remotes build script
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
 * @subpackage build
 */
$tstart = microtime(true);
set_time_limit(0);

/* define package */
define('PKG_NAME', 'Remotes');
define('PKG_NAME_LOWER', strtolower(PKG_NAME));
define('PKG_VERSION', '1.0.0');
define('PKG_RELEASE', 'pl');

/* define sources */
$root = dirname(dirname(__FILE__)) . '/';
$sources = array(
    'root'          => $root,
    'build'         => $root . '_build/',
    'data'          => $root . '_build/data/',
    'events'        => $root . '_build/data/events/',
    'properties'    => $root . '_build/data/properties/',
    'resolvers'     => $root . '_build/resolvers/',
    'chunks'        => $root . 'core/components/' . PKG_NAME_LOWER . '/elements/chunks/',
    'snippets'      => $root . 'core/components/' . PKG_NAME_LOWER . '/elements/snippets/',
    'plugins'       => $root . 'core/components/' . PKG_NAME_LOWER . '/elements/plugins/',
    'lexicon'       => $root . 'core/components/' . PKG_NAME_LOWER . '/lexicon/',
    'docs'          => $root . 'core/components/' . PKG_NAME_LOWER . '/docs/',
    'pages'         => $root . 'core/components/' . PKG_NAME_LOWER . '/elements/pages/',
    'source_core'   => $root . 'core/components/' . PKG_NAME_LOWER,
    'source_assets' => $root . 'assets/components/' . PKG_NAME_LOWER,
);
unset($root);

/* override with your own defines here (see build.config.sample.php) */
require_once dirname(__FILE__) . '/build.config.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx = new modX();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');

$modx->loadClass('transport.modPackageBuilder', '', false, true);
$builder = new modPackageBuilder($modx);
$builder->createPackage(PKG_NAME_LOWER, PKG_VERSION, PKG_RELEASE);
$builder->registerNamespace(PKG_NAME_LOWER, false, true, '{core_path}components/' . PKG_NAME_LOWER . '/');

$modx->log(modX::LOG_LEVEL_INFO, "Created Transport Package and Namespace for " . PKG_NAME_LOWER . ".");

/* create Category */
$category = $modx->newObject('modCategory');
$category->set('id', 1);
$category->set('category', PKG_NAME);

/* add Plugins */
$plugins = include $sources['data'] . 'transport.plugins.php';
if (!is_array($plugins)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in plugins.');
} else {
    /** @var modPlugin $plugin */
    foreach ($plugins as $pluginName => $plugin) {
        $plugin->addOne($category);
        $properties = include $sources['properties'] . "properties.{$pluginName}.php";
        $plugin->setProperties($properties);
        $vehicle = $builder->createVehicle($plugin, array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'name',
            xPDOTransport::RELATED_OBJECTS => true,
            xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
                'Category' => array(
                    xPDOTransport::PRESERVE_KEYS => false,
                    xPDOTransport::UNIQUE_KEY => 'category',
                    xPDOTransport::UPDATE_OBJECT => false,
                )
            )
        ));
        if ($pluginName === 'RemoteCommands') {
            $modx->log(modX::LOG_LEVEL_INFO, 'Adding file resolver to RemoteCommands plugin...');
            $vehicle->resolve(
                'file', array(
                    'source' => $sources['source_core'],
                    'target' => "return MODX_CORE_PATH . 'components/';",
                )
            );
        }
        $vehicle->resolve('php',array(
            'source' => $sources['resolvers'] . "resolve.{$pluginName}.php",
        ));
        $builder->putVehicle($vehicle);
    }
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($plugins) . ' plugins.');
}

/* load system settings */
$settings = include $sources['data'] . 'transport.settings.php';
if (!is_array($settings)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not package System Settings.');
} else {
    $attributes = array(
        xPDOTransport::UNIQUE_KEY    => 'key',
        xPDOTransport::PRESERVE_KEYS => true,
        xPDOTransport::UPDATE_OBJECT => false,
    );
    foreach ($settings as $settingKey => $setting) {
        $vehicle = $builder->createVehicle($setting, $attributes);
        if ($settingKey === 'remotes.enabled') {
            $vehicle->resolve('php',array(
                'source' => $sources['resolvers'] . "resolve.{$settingKey}.php",
            ));
        }
        $builder->putVehicle($vehicle);
    }
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($settings) . ' System Settings.');
}
unset($settings, $setting, $attributes);

/* now pack in the license file, readme, changelog and setup options */
$builder->setPackageAttributes(
    array(
        'license'       => file_get_contents($sources['docs'] . 'license.txt'),
        'readme'        => file_get_contents($sources['docs'] . 'readme.txt'),
        'setup-options' => array(
            'source' => $sources['build'] . 'setup.options.php',
        ),
    )
);
$modx->log(modX::LOG_LEVEL_INFO, 'Added package attributes and setup options.');

/* zip up package */
$modx->log(modX::LOG_LEVEL_INFO, 'Packing up transport package...');
$builder->pack();

$tend = microtime(true);
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);

$modx->log(modX::LOG_LEVEL_INFO, "Package {$builder->filename} built in {$totalTime}\n");

exit ();
