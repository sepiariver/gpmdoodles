<?php
/**
 * gpmd.GetDoodles
 * @package gpmdoodles
 * Gets a collection of Doodles
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

// Grab the GPMDoodles class
$gpmd = null;
$gpmdPath = $modx->getOption('gpmdoodles.core_path', null, $modx->getOption('core_path') . 'components/gpmdoodles/');
$gpmdPath .= 'model/gpmdoodles/';
if (file_exists($gpmdPath . 'gpmdoodles.class.php')) $gpmd = $modx->getService('gpmdoodles', 'GPMDoodles', $gpmdPath);
if (!$gpmd || !($gpmd instanceof \SepiaRiver\GPMDoodles)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[gpmd.GetDoodles] could not load the required gpmdoodles class!');
    return '';
}

// OPTIONS
$tpl = $modx->getOption('tpl', $scriptProperties, 'gpmd.doodle.tpl');
$debug = $modx->getOption('debug', $scriptProperties, false);

$doodles = $modx->getCollection('GPMDoodle');
if (!$doodles) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[gpmd.GetDoodles] could not get any Doodles!');
    return '';
}
if (!empty($tpl)) {
    $output = [];
    foreach ($doodles as $doodle) {
        $output[] = $gpmd->getChunk($tpl, $doodle->toArray());
    }
    return implode(PHP_EOL, $output);
} else {
    if ($debug) var_dump($doodles);
    return '';
}