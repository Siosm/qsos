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
 *  index.php: lists software families and shows search box
 *
**/


  session_start();
  $_SESSION = array();

  include("config.php");
  include("lang.php");
  $lang = 'checked';
?>

<html>
  <head>
<?php
  echo "    <link REL=StyleSheet HREF='skins/$skin/o3s.css' TYPE='text/css'/>\n";
?>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>
    <script>
      function changeLang(lang) {
        window.location = 'index.php?lang='+lang
      }
    </script>
  </head>
  <body>
    <div id="bandeau">
      <center>
        <a href="index.php">Start page</a> |
        <a href="upload.php">Upload an evaluation</a> |
        <a href="search.php">Search for an evaluation</a>
      </center>
    </div>
    <center>
<?php
  echo "      <img src='skins/$skin/o3s.png'/>\n";
  echo "      <br/>\n";
  echo "      <br/>\n";
  echo "      <div style='font-weight: bold'>";
  echo "        " . $msg['s1_title'];
  echo "        <br/>\n";
  echo "        <br/>\n";
  echo "      </div>\n";

  echo "    <div>\n";
  foreach($supported_lang as $l) {
    $checked = $l;
    echo "<input type='radio' onclick=\"changeLang('$l')\"/> $l";
  }
  echo "<br/><br/></div>";

  echo "<table>\n";
  echo "<tr class='title'>\n";
  echo "<td>".$msg['s1_table_title']."</td>\n";
  echo "<td style='width: 100px; text-align: center'>".$msg['s1_table_templateversion']."</td>\n";
  echo "<td style='width: 100px; text-align: center'>".$msg['s1_table_nbeval']."</td>\n";
  echo "</tr>\n";

  $IdDB = mysql_connect($db_host ,$db_user, $db_pwd);
  mysql_select_db($db_db);
  $query = "SELECT qsosappfamily, qsosspecificformat, count(*) FROM evaluations WHERE appname <> '' AND language = '$lang' GROUP BY qsosappfamily, qsosspecificformat ORDER BY qsosappfamily, qsosspecificformat";
  $IdReq = mysql_query($query, $IdDB);
  while($row = mysql_fetch_row($IdReq)) {
    $link = "list.php?lang=$lang&family=$row[0]&qsosspecificformat=$row[1]";
    $over0 =  "onmouseover=\"this.setAttribute('class','highlight')\"
      onmouseout=\"this.setAttribute('class','level0')\"";
    $over1 =  "onmouseover=\"this.setAttribute('class','highlight')\"
      onmouseout=\"this.setAttribute('class','level1')\"";
    echo "<tr>\n";
    echo "<td class='level0' $over0><a href='$link'><b>$row[0]</b></a</td>\n";
    echo "<td align='center' class='level1' style='width: 100px; text-align: center' $over1><a href='$link'>$row[1]</a</td>\n";
    echo "<td align='center' class='level1' style='width: 1O0px; text-align: center' $over1><a href='$link'>$row[2]</a></td>\n";
    echo "</tr>\n";
  }

  echo "</table>\n";

  echo "<p>".$msg['s1_search']."<br/><form action='search.php'>
    <input type='text' name='s' size='20' maxlength='30'/>
    <input type='hidden' name='lang' value='$lang'/>
    <input type='submit' value='".$msg['s1_button']."'/>";
?>
          </form>
        </p>
      </div>
    </center>
  </body>
</html>
