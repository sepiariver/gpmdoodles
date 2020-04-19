<?php
/**
 * gpmd.GetDoodles
 * @package gpmd
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
$gpmdPath = $modx->getOption('gpm-doodles.core_path', null, $modx->getOption('core_path') . 'components/gpm-doodles/');
$gpmdPath .= 'model/gpm-doodles/';
if (file_exists($gpmdPath . 'gpm-doodles.class.php')) $gpmd = $modx->getService('gpm-doodles', 'GPMDoodles', $gpmdPath);
if (!$gpmd || !($gpmd instanceof \SepiaRiver\GPMDoodles)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[gpmd.GetDoodles] could not load the required gpmd class!');
    return '';
}

$doodles = $gpmd->fetchDoodles();
if (!$doodles) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[gpmd.GetDoodles] could not load any Doodles!');
    return '';
}

var_dump($doodles);