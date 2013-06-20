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
 * @subpackage build
 */
$settings = array();

$settings['remotes.enabled']= $modx->newObject('modSystemSetting');
$settings['remotes.enabled']->fromArray(array(
    'key' => 'remotes.enabled',
    'value' => false,
    'xtype' => 'combo-boolean',
    'namespace' => 'remotes',
    'area' => 'remotes',
),'',true,true);

return $settings;
