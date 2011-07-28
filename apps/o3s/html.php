<?php
/**
 *  Copyright (C) 2007-2011 Atos
 *
 *  Author: Raphael Semeteys <raphael.semeteys@atos.net>
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 *
 *  O3S
 *
**/


$id = $_GET['id'];
if (!isset($id)) die("No QSOS file to process");

include("config.php");
$IdDB = mysql_connect($db_host ,$db_user, $db_pwd);
mysql_select_db($db_db);

$query = "SELECT file FROM evaluations WHERE id = \"$id\"";
$IdReq = mysql_query($query, $IdDB);

if ($file = mysql_fetch_row($IdReq)) {
  # LOAD XML FILE
  $XML = new DOMDocument();
  $XML->load($file[0]);

  # START XSLT
  $xslt = new XSLTProcessor();

  # IMPORT STYLESHEET
  $XSL = new DOMDocument();
  $XSL->load('xslt/qsos-xhtml.xsl');
  $xslt->importStylesheet($XSL);

  #PRINT
  print $xslt->transformToXML($XML);
} else {
  print "Error: no $id found in QSOS database!";
}
?>
