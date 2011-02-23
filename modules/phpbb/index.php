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

/*===========================================================================
phpbb/index.php
@last update: 2006-07-23 by Artemios G. Voyiatzis
@authors list: Artemios G. Voyiatzis <bogart@upnet.gr>

based on Claroline version 1.7 licensed under GPL
copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

Claroline authors: Piraux Sebastien <pir@cerdecam.be>
Lederer Guillaume <led@cerdecam.be>

based on phpBB version 1.4.1 licensed under GPL
copyright (c) 2001, The phpBB Group
==============================================================================
@Description: This module implements a per course forum for supporting
discussions between teachers and students or group of students.
It is a heavily modified adaptation of phpBB for (initially) Claroline
and (later) eclass. In the future, a new forum should be developed.
Currently we use only a fraction of phpBB tables and functionality
(viewforum, viewtopic, post_reply, newtopic); the time cost is
enormous for both core phpBB code upgrades and migration from an
existing (phpBB-based) to a new eclass forum :-(

@Comments:

@todo:
==============================================================================
*/

/*
* Open eClass 2.x standard stuff
*/
$require_login = TRUE;
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'For';
include '../../include/baseTheme.php';
$nameTools = $langForums;
$tool_content = "";

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_FORUM');
/**************************************/


include_once("./config.php");
include "functions.php";
include "../group/group_functions.php";

/*
* First, some decoration
*/
if ($is_adminOfCourse || $is_admin) {
	$tool_content .= "
    <div id='operations_container'>
      <ul id='opslist'>
        <li><a href='../forum_admin/forum_admin.php'>$langAdm</a></li>
      </ul>
    </div>";
}

if(isset($_GET['forumcatnotify'])) { // modify forum category notification
		if (isset($_GET['cat_id'])) {
			$cat_id = intval($_GET['cat_id']);
		}
		$rows = mysql_num_rows(db_query("SELECT * FROM forum_notify 
			WHERE user_id = $uid AND cat_id = $cat_id AND course_id = $cours_id", $mysqlMainDb));
		if ($rows > 0) {
			db_query("UPDATE forum_notify SET notify_sent = " . intval($_GET['forumcatnotify']) . "
				WHERE user_id = $uid AND cat_id = $cat_id AND course_id = $cours_id", $mysqlMainDb);
	} else {
		db_query("INSERT INTO forum_notify SET user_id = $uid,
		cat_id = $cat_id, notify_sent = 1, course_id = $cours_id", $mysqlMainDb);
	}
} elseif(isset($_GET['forumnotify'])) { // modify forum notification
	if (isset($_GET['forum_id'])) {
		$forum_id = intval($_GET['forum_id']);
	}
	$rows = mysql_num_rows(db_query("SELECT * FROM forum_notify 
		WHERE user_id = $uid AND forum_id = $forum_id AND course_id = $cours_id", $mysqlMainDb));
	if ($rows > 0) {
		db_query("UPDATE forum_notify SET notify_sent = " . intval($_GET['forumnotify']) . "
			WHERE user_id = $uid AND forum_id = $forum_id AND course_id = $cours_id", $mysqlMainDb);
	} else {
		db_query("INSERT INTO forum_notify SET user_id = $uid,
		forum_id = $forum_id, notify_sent = 1, course_id = $cours_id", $mysqlMainDb);
	}
}

/*
* Populate data with forum categories
*/
$sql = "SELECT c.* FROM catagories c, forums f
	 WHERE f.cat_id=c.cat_id
	 GROUP BY c.cat_id, c.cat_title, c.cat_order
	 ORDER BY cat_order, c.cat_id DESC";

$result = db_query($sql, $currentCourseID); 
$total_categories = mysql_num_rows($result);

if ($total_categories) {
	$tool_content .= "
         <table width='99%' class='tbl_border'>
	 <tr>
	   <th colspan='2'>&nbsp;$langForums</th>
	   <th class='center'>$langSubjects</th>
	   <th class='center'>$langPosts</th>
	   <th class='center'>$langLastPost</th>
	   <th class='center' width='5'>$langNotifyActions</th>
 	 </tr>\n";

	if (isset($viewcat)) {
		if (!$viewcat) {
			$viewcat = -1;
		}
	} else {
		$viewcat = -1;
	}
	while ($cat_row = mysql_fetch_array($result)) {
		$categories[] = $cat_row;
	}
	$limit_forums = "";
	if ($viewcat != -1) {
		$limit_forums = "WHERE f.cat_id = $viewcat";
	}
	$sql = "SELECT f.*, p.post_time, p.nom, p.prenom, p.topic_id
		FROM forums f LEFT JOIN posts p ON p.post_id = f.forum_last_post_id
		$limit_forums ORDER BY f.cat_id, f.forum_id";
	$f_res = db_query($sql, $currentCourseID); 
	while ($forum_data = mysql_fetch_array($f_res)) {
		$forum_row[] = $forum_data;
	}
	for($i=0; $i < $total_categories; $i++) {
		if ($viewcat != -1) {
			if ($categories[$i][cat_id] != $viewcat) {
				$title = stripslashes($categories[$i][cat_title]);
				$tool_content .= "        <tr>
          <td colspan='6'>&nbsp;$title</td>
        </tr>";
				continue;
			}
		}
		$title = stripslashes($categories[$i]["cat_title"]);
		$catNum = $categories[$i]["cat_id"];
		list($action_notify) = mysql_fetch_row(db_query("SELECT notify_sent FROM forum_notify 
				WHERE user_id = $uid AND cat_id = $catNum AND course_id = $cours_id", $mysqlMainDb));
		if (!isset($action_notify)) {
			$link_notify = FALSE;
			$icon = '_off';
		} else {
			$link_notify = toggle_link($action_notify);
			$icon = toggle_icon($action_notify);
		}
		$tool_content .= "         <tr class='odd'>
           <td colspan='5'>&nbsp;<b>$title</b></td>
           <td class='center'><a href='$_SERVER[PHP_SELF]?forumcatnotify=$link_notify&amp;cat_id=$catNum'><img src='../../template/classic/img/announcements$icon.png' title='$langNotify' alt='$langNotify' /></a></td>
         </tr>\n";
			
		@reset($forum_row);
		for ($x=0; $x < count($forum_row); $x++) {
			unset($last_post);
			if ($forum_row[$x]['cat_id'] == $categories[$i]['cat_id']) {
				if ($forum_row[$x]["post_time"]) {
					$last_post = $forum_row[$x]["post_time"];
					$last_post_datetime = $forum_row[$x]["post_time"];
					list($last_post_date, $last_post_time) = explode(' ', $last_post_datetime);
					list($year, $month, $day) = explode('-', $last_post_date);
					list($hour, $min) = explode(':', $last_post_time);
					$last_post_time = mktime($hour, $min, 0, $month, $day, $year);
					$human_last_post_time = date('d/m/Y -  H:i', $last_post_time);
				}
				if (empty($last_post)) {
					$last_post = $langNoPosts;
				}
				$tool_content .= "         <tr class='even'>\n";
				if (!isset($last_visit)) {
					$last_visit = 0;
				}
				if(@$last_post_time > $last_visit && $last_post != $langNoPosts) {
					$tool_content .= "<td width='1'><img src='$newposts_image' /></td>\n";
				} else {
					$tool_content .= "<td width='2'><img src='$folder_image' /></td>\n";
				}
				$forum_name = q($forum_row[$x]['forum_name']);
				$last_post_nom = q($forum_row[$x]['nom']);
				$last_post_prenom = q($forum_row[$x]['prenom']);
				$last_post_topic_id = $forum_row[$x]['topic_id'];
				$total_posts = $forum_row[$x]['forum_posts'];
				$total_topics = $forum_row[$x]['forum_topics'];
				$desc = q($forum_row[$x]['forum_desc']);
				$tool_content .= "<td>";
				$forum_id = $forum_row[$x]['forum_id'];
				$is_member = false;
				$group_id = init_forum_group_info($forum_id);
				$member = $is_member? "&nbsp;&nbsp;($langMyGroup)": '';
				// Show link to forum if:
				//  - user is admin of course
				//  - forum doesn't belong to group
				//  - forum belongs to group and group forums are enabled and
				//     - forum is not private or
				//     - user is member of group
                                if ($is_adminOfCourse or !$group_id or ($has_forum and (!$private_forum or $is_member))) {
                                        $tool_content .= "<a href='viewforum.php?forum=$forum_id'>$forum_name</a>" . $member;
                                } else {
                                        $tool_content .= $forum_name;
                                }
				$tool_content .= "<br />$desc";
				$tool_content .= "</td>\n";
				$tool_content .= "<td width='65' class='center'>$total_topics</td>\n";
				$tool_content .= "<td width='65' class='center'>$total_posts</td>\n";
				$tool_content .= "<td width='200' class='center'>";
				if ($total_topics > 0 && $total_posts > 0) {
					$tool_content .= "$last_post_prenom $last_post_nom <a href='viewtopic.php?topic=$last_post_topic_id&amp;forum=$forum_id'> <img src='$icon_topic_latest' /></a><br />$human_last_post_time</td>\n";
				} else {
					$tool_content .= "<div class='inactive'>$langNoPosts</div></td>";
				}
				list($forum_action_notify) = mysql_fetch_row(db_query("SELECT notify_sent FROM forum_notify 
					WHERE user_id = $uid AND forum_id = $forum_id AND course_id = $cours_id", $mysqlMainDb));
				if (!isset($forum_action_notify)) {
					$forum_link_notify = false;
					$forum_icon = '_off';
				} else {
					$forum_link_notify = toggle_link($forum_action_notify);
					$forum_icon = toggle_icon($forum_action_notify);
				}
				$tool_content .= "           <td class='center'><a href='$_SERVER[PHP_SELF]?forumnotify=$forum_link_notify&amp;forum_id=$forum_id'><img src='../../template/classic/img/announcements$forum_icon.png' title='$langNotify' alt='$langNotify' /></a></td>\n" .
				                 "         </tr>\n";
			}
		}
	}
} else {
	$tool_content .= "\n   <p class='alert1'>$langNoForums</p>";
}
$tool_content .= "         </table>";
add_units_navigation(true);
draw($tool_content, 2);
