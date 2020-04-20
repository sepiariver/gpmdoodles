<?php
/**
 * GPM Doodles
 * @package gpmdoodles
 * Syncs Doodles from the API
 * 
 * @author @sepiariver <info@sepiariver.com>
 * Copyright 2020 by YJ Tso
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 **/

// Exit early if the wrong Event is attached
$event = $modx->event->name;
if (!in_array($event, ['OnSiteRefresh'])) return;

// Grab the GPMDoodles class
$gpmd = null;
$gpmdPath = $modx->getOption('gpmdoodles.core_path', null, $modx->getOption('core_path') . 'components/gpmdoodles/');
$gpmdPath .= 'model/gpmdoodles/';
if (file_exists($gpmdPath . 'gpmdoodles.class.php')) $gpmd = $modx->getService('gpmdoodles', 'GPMDoodles', $gpmdPath);
if (!$gpmd || !($gpmd instanceof \SepiaRiver\GPMDoodles)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[GPM Doodles Plugin] could not load the required gpmdoodles class!');
    return;
}

// A switch statement isn't strictly necessary since we only allow one Event type
// but in the future we might want to expand on that.
switch ($event) {
    case 'OnSiteRefresh':
        $doodles = $gpmd->fetchDoodles();
        if (!$doodles || !is_array($doodles['data'])) {
            $modx->log(modX::LOG_LEVEL_ERROR, '[GPM Doodles Plugin] could not load any Doodles!');
            return;
        }
        $total = count($doodles['data']);
        $modx->log(modX::LOG_LEVEL_INFO, 'Syncing ' . $total . ' Doodles.');
        foreach ($doodles['data'] as $doodle) {
            if ($doodle['type'] !== 'Doodle') continue;
            $object = $modx->getObject('GPMDoodle', ['id' => $doodle['id']]);
            if (!$object) {
                $object = $modx->newObject('GPMDoodle');
            }
            $doodle['title'] = $doodle['attributes']['title'];
            // xPDO discards array elements that are not in the model
            $object->fromArray($doodle, '', true); // Set PKs with 3rd argument
            if ($object->save()) {
                $result = 'Saved: ' . $doodle['id'];
            } else {
                $result = 'Failed: ' . print_r($doodle, true);
            }
            $modx->log(modX::LOG_LEVEL_INFO, $result);
        }
        $modx->log(modX::LOG_LEVEL_INFO, 'Finished syncing Doodles.');
    break;
    default: 
    break;
}