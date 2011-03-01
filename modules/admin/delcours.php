<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

/**===========================================================================
	delcours.php
	@last update: 31-05-2006 by Pitsiougas Vagelis
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================
        @Description: Delete a course

 	This script allows the administrator to delete a course

 	The user can : - Confirm for course deletion
 	               - Delete a cours
                 - Return to course list

 	@Comments: The script is organised in three sections.

  1) Confirm course deletion
  2) Delete course
  3) Display all on an HTML page

==============================================================================*/

$require_admin = TRUE;
// Include baseTheme
include '../../include/baseTheme.php';

if(isset($_GET['c'])) {
	$cours_id = intval($_GET['c']);
} else {
	$cours_id = '';
}

$nameTools = $langCourseDel;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'listcours.php', 'name' => $langListCours);

// Delete course
if (isset($_GET['delete']) && $cours_id)  {
        delete_course($cours_id);
        $tool_content .= "<p>".$langCourseDelSuccess."</p>";
}
// Display confirmatiom message for course deletion
else {
	$row = mysql_fetch_array(db_query("SELECT * FROM cours WHERE cours_id = $cours_id"));

	$tool_content .= "<table><caption>".$langCourseDelConfirm."</caption><tbody>";
	$tool_content .= "<tr><td>
		<br />".$langCourseDelConfirm2." <em>".course_id_to_title($cours_id)."</em>;
		<br /><br /><i>".$langNoticeDel."</i><br />
		</td></tr>";
	$tool_content .= "<tr>
	<td><ul><li><a href='".$_SERVER['PHP_SELF']."?c=".htmlspecialchars($_GET['c'])."&amp;delete=yes'><b>$langYes</b></a><br />&nbsp;</li>
	<li><a href='listcours.php'><b>$langNo</b></a></li></ul></td>
	</tr>";
	$tool_content .= "</tbody></table><br />";
}
// If course deleted go back to listcours.php
if (isset($_GET['c']) && !isset($_GET['delete'])) {
	$tool_content .= "<center><p><a href='listcours.php'>$langBack</a></p></center>";
}
// Display link to index.php
else {
	$tool_content .= "<br /><p align='right'><a href='index.php'>$langBack</a></p>";
}
draw($tool_content, 3);
