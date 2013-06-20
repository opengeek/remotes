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
 * Properties for the SendClearCache Plugin.
 *
 * @package remotes
 * @subpackage build
 */
$properties = array(
    array(
        'name' => 'seconds',
        'desc' => 'prop_sendclearcache.seconds_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '0',
        'lexicon' => 'remotes:properties',
    ),
    array(
        'name' => 'delay',
        'desc' => 'prop_sendclearcache.delay_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '0',
        'lexicon' => 'remotes:properties',
    ),
    array(
        'name' => 'stagger',
        'desc' => 'prop_sendclearcache.stagger_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '0',
        'lexicon' => 'remotes:properties',
    ),
);

return $properties;
