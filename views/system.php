<?php
//
// ZoneMinder web console file, $Date: 2009-02-19 10:05:31 +0000 (Thu, 19 Feb 2009) $, $Revision: 2780 $
// Copyright (C) 2001-2008 Philip Coombes
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//


noCacheHeaders();

xhtmlHeaders( __FILE__, $SLANG['System'] );
?>
<body>
   <?php require_once("header.php"); ?>
   <div id="content" class="clearfix">
    <h3 id="systemStats"><?= $SLANG['Load'] ?>: <?= getLoad() ?> / <?= $SLANG['Disk'] ?>: <?= getDiskPercent() ?>%</h3>
  </div>

<?php
exec('/usr/local/bin/zmlog.pl',$output);

$s = '';
foreach($output as $row)
	if(!empty($row)) $s .= '<div>'.$row.'</div>';
echo '<div align="left">'.$s.'</div>';

require_once("footer.php"); 

