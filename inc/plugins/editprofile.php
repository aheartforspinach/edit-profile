<?php

if (!defined("IN_MYBB")) die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");

function editprofile_info()
{
    return array(
        "name"            => "Steckbrief bearbeiten",
        "description"    => "Änderungen an Steckbriefen müssen zuerst von einem Teamie freigeschaltet werden",
        "author"        => "aheartforspinach",
        "authorsite"    => "https://github.com/aheartforspinach",
        "version"        => "1.0",
        "compatibility" => "18*"
    );
}

function editprofile_install()
{
    global $db;
    // database
    if ($db->engine == 'mysql' || $db->engine == 'mysqli') {
        $db->query("CREATE TABLE `" . TABLE_PREFIX . "editprofile` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `pid` int(11) DEFAULT NULL,
        `uid` int(11) DEFAULT NULL,
        `message` text,
        `editreason` varchar(128) DEFAULT NULL,
        PRIMARY KEY (`id`)
        ) ENGINE=MyISAM" . $db->build_create_table_collation());
    }

    //Einstellungen 
    $setting_group = array(
        'name' => 'editprofile',
        'title' => 'Steckbrief bearbeiten',
        'description' => 'Einstellungen für das Steckbrief bearbeiten-Plugin',
        'isdefault' => 0
    );

    $gid = $db->insert_query("settinggroups", $setting_group);
    $setting_array = array(
        'editprofile_forum' => array(
            'title' => 'Foren',
            'description' => 'In welchen Foren soll eine Überprüfung stattfinden?',
            'optionscode' => 'forumselect',
            'value' => -1,
            'disporder' => 1
        ),
        'editprofile_teamie' => array(
            'title' => 'Rechte für Teammitglieder',
            'description' => 'Dürfen Teammitglieder ihre eigenen Steckbriefe annehmen?',
            'optionscode' => 'yesno',
            'value' => 0,
            'disporder' => 2
        )
    );

    foreach ($setting_array as $name => $setting) {
        $setting['name'] = $name;
        $setting['gid'] = $gid;

        $db->insert_query('settings', $setting);
    }

    // CSS	
	$css = array(
		'name' => 'editprofile.css',
		'tid' => 1,
		'attachedto' => '',
		"stylesheet" =>	'.alert-warning {
    text-align: center;
    background-color: #fff3cd;
    padding: 0.5rem;
    font-size: 8.5pt;
}
.alert-success {
    text-align: center;
    background-color: #74c365;
    padding: 0.5rem;
    font-size: 8.5pt;
    color: #fbfbfb;
}
.diffDeleted {
    background-color: #e3bbbb;
    border: 1px solid #956666;
}
.diffInserted {
    background-color: #acddac;
    border: 1px solid #54a854;
}
.diff {
    background-color: #f2f2f2;
}
.diff td {
    vertical-align: top;
    white-space: pre;
    white-space: pre-wrap;
    font-family: monospace;
}',
		'cachefile' => 'editprofile.css',
		'lastmodified' => time()
	);

	require_once MYBB_ADMIN_DIR."inc/functions_themes.php";

	$sid = $db->insert_query("themestylesheets", $css);
	$db->update_query("themestylesheets", array("cachefile" => "css.php?stylesheet=".$sid), "sid = '".$sid."'", 1);

	$tids = $db->simple_select("themes", "tid");
	while($theme = $db->fetch_array($tids)) {
		update_theme_stylesheet_list($theme['tid']);
	}

    //create templates
    $templategroup = array(
        //	"gid" => "",
        "prefix" => "editprofile",
        "title" => $db->escape_string("Steckbrief bearbeiten"),
    );

    $db->insert_query("templategroups", $templategroup);

    $insert_array = array(
        'title'        => 'editprofile_modcp_nav',
        'template'    => $db->escape_string('<tr><td class="trow1 smalltext"><a href="modcp.php?action=steckichanges" class="modcp_nav_item">Steckbriefänderungen</a></td></tr>'),
        'sid'        => '-2',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'editprofile_modcp',
        'template'    => $db->escape_string('<html>
        <head>
            <title>{$mybb->settings[\'bbname\']} - Steckbriefe freischalten</title>
            {$headerinclude}
        </head>
        
        <body>
            {$header}
            <table width="100%" border="0" align="center">
                <tr>
                    {$modcp_nav}
                    <td valign="top">
        
                        {$banner}
                        <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
                            <tr>
                                <td class="thead" colspan="3">Anträge auf Steckbriefänderung</td>
                            </tr>
                            <tr>
                                <td class="tcat">Charakter</td>
                                <td class="tcat">Grund</td>
                                <td class="tcat">Optionen</td>
                            </tr>
                            {$changes}
                        </table>
        
        
                    </td>
                </tr>
            </table>
            {$footer}
        </body>
        
        </html>'),
        'sid'        => '-2',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'editprofile_banner',
        'template'    => $db->escape_string('<div class="alert-{$type}">
    {$text}
</div>'),
        'sid'        => '-2',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'editprofile_misc_overview',
        'template'    => $db->escape_string('<html>
        <head>
        <title>{$mybb->settings[\'bbname\']} - Änderung</title>
        {$headerinclude}
        </head>
        <body>
        {$header}
        <div class="panel" id="panel">
			<div id="panel">$menu</div>
			<h1>Änderung von {$character}</h1>
			<center><b>Grund:</b> {$reason}<br>			rot: gelöscht, grün: hinzugefügt</center><br><br>
			
			<div style="white-space: pre-line;">{$output}</div>
			<br><br>
			<center>
			<form method="post" action="modcp.php?action=steckichanges">
			<input name="id" value="{$id}" type="hidden" />
			<input name="type" value="{$type}" type="hidden" />
			<input placeholder="Grund der Ablehnung" name="decline_reason" class="textbox" type="text" />
            <button type="submit" name="accept" value="false" title="Ablehnen" class="button">Ablehnen</button><br>
            <button type="submit" name="accept" value="true" title="Annehmen" class="button">Annehmen</button>
		</form>
			</center>
		</div>
        {$footer}
        </body>
</html>'),
        'sid'        => '-2',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'editprofile_modcp_bit',
        'template'    => $db->escape_string('<tr>
    <td>{$character}</td>
    <td>{$reason}</td>
    <td>
        <form method="post" action="modcp.php?action=steckichanges">
            <input name="id" value="{$id}" type="hidden" />
            <input name="type" value="{$type}" type="hidden" />
            <input placeholder="Grund der Ablehnung" name="decline_reason" class="textbox" type="text" />
            <button type="submit" name="accept" value="false" title="Ablehnen" class="button">Ablehnen</button><br>
            <button type="submit" name="accept" value="true" title="Annehmen" class="button">Annehmen</button>
        </form>
    </td>
</tr>'),
        'sid'        => '-2',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'editprofile_unapprovededit',
        'template'    => $db->escape_string('<html>
        <head>
        <title>{$mybb->settings[\'bbname\']} - Änderungen bereits eingetragen</title>
        {$headerinclude}
        </head>
        <body>
        {$header}
        <div class="panel" id="panel">
			<div id="panel">$menu</div>
			<h1>Steckbriefänderungen</h1>
			<center>{$text}</center>
		</div>
        {$footer}
        </body>
</html>'),
        'sid'        => '-2',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'editprofile_postbit_edit',
        'template'    => $db->escape_string('<a href="editpost.php?pid={$post[\'pid\']}" id="edit_post_{$post[\'pid\']}" title="{$lang->postbit_edit}"><span>Bearbeiten</span></a>'),
        'sid'        => '-2',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    rebuild_settings();
}

function editprofile_is_installed()
{
    global $mybb;
    return isset($mybb->settings['editprofile_forum']) ? true : false;
}

function editprofile_uninstall()
{
    global $db;
    $db->delete_query('settings', "name LIKE 'editprofile_%'");
    $db->delete_query('settinggroups', "name = 'editprofile'");
    $db->delete_query("templategroups", 'prefix = "editprofile"');
    $db->delete_query("templates", "title LIKE 'editprofile%'");

    if($db->table_exists('editprofile')) $db->drop_table('editprofile');

    require_once MYBB_ADMIN_DIR."inc/functions_themes.php";
	$db->delete_query("themestylesheets", "name = 'editprofile.css'");
	$query = $db->simple_select("themes", "tid");
	while($theme = $db->fetch_array($query)) {
		update_theme_stylesheet_list($theme['tid']);
	}

    rebuild_settings();
}

function editprofile_activate()
{
    global $db, $cache;
    include MYBB_ROOT . "/inc/adminfunctions_templates.php";
    find_replace_templatesets("header", "#" . preg_quote('{$awaitingusers}') . "#i", '{$awaitingusers} {$editprofilebanner}');
    find_replace_templatesets("modcp_nav_users", "#" . preg_quote('{$nav_ipsearch}') . "#i", '{$nav_ipsearch}{$nav_editprofile}');

    if (function_exists('myalerts_is_activated') && myalerts_is_activated()) {
        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);

        $alertType = new MybbStuff_MyAlerts_Entity_AlertType();
        $alertType->setCanBeUserDisabled(false);
        $alertType->setCode("accept_steckichange");
        $alertType->setEnabled(true);

        $alertTypeManager->add($alertType);
    }
}

function editprofile_deactivate()
{
    global $db, $mybb;
    include MYBB_ROOT . "/inc/adminfunctions_templates.php";
    find_replace_templatesets("header", "#" . preg_quote('{$editprofilebanner}') . "#i", '', 0);
    find_replace_templatesets("modcp_nav_users", "#" . preg_quote('{$nav_editprofile}') . "#i", '', 0);

    if (function_exists('myalerts_is_activated') && myalerts_is_activated()) {
        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

        $alertTypeManager->deleteByCode('accept_steckichange');
    }
}

// 
// 
// 
$plugins->add_hook('postbit', 'editprofile_postbit');
function editprofile_postbit(&$post) {
    global $mybb, $templates;

    $areas = explode(',', $mybb->settings['editprofile_forum']);
    if (!in_array($post['fid'], $areas)) return;

    eval("\$post['button_edit'] = \"" . $templates->get('editprofile_postbit_edit') . "\";");
}

// 
// banner for teamies
// 
$plugins->add_hook('global_start', 'editprofile_global_start');
function editprofile_global_start()
{
    global $mybb, $templates, $db, $editprofilebanner;
    if ($mybb->usergroup['canmodcp'] == 0) return;

    $teamiesCanSeeOwn = intval($mybb->settings['editprofile_teamie']) == 0 ? 'not find_in_set(uid, "' . awayice_getUids($mybb->user['uid']) . '")' : '';
    $num_row = $db->num_rows($db->simple_select('editprofile', '*', $teamiesCanSeeOwn));
    if ($num_row > 0) {
        $type = 'warning';
        $text = 'Es gibt <a href="modcp.php?action=steckichanges">neue Anträge</a> auf Steckbriefänderungen';
        eval("\$editprofilebanner = \"" . $templates->get('editprofile_banner') . "\";");
    }
}

// 
// Mod CP
// 
$plugins->add_hook('modcp_nav', 'editprofile_modcp_nav');
function editprofile_modcp_nav()
{
    global $templates, $nav_editprofile;
    eval("\$nav_editprofile= \"" . $templates->get('editprofile_modcp_nav') . "\";");
}

$plugins->add_hook('modcp_start', 'editprofile_modcp');
function editprofile_modcp()
{
    global $mybb, $db, $lang, $templates, $headerinclude, $header, $footer, $modcp_nav, $theme, $changes;

    if ($mybb->get_input('action') != 'steckichanges') return;

    // save changes
    if ($_POST['accept'] == 'true') {
        $id = $db->escape_string($_POST['id']);
        $change = $db->fetch_array($db->simple_select('editprofile', '*', 'id = ' . $id));

        // alert stuff
        if (function_exists('myalerts_is_activated') && myalerts_is_activated()) {
            $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();
            $alertType = $alertTypeManager->getByCode('accept_steckichange');
            $alertTypeId = $alertType->getId();
            $fromUser = $mybb->user['uid'];
            $toUser = $change['uid'];
            $alertManager = MybbStuff_MyAlerts_AlertManager::getInstance();

            $alert = new MybbStuff_MyAlerts_Entity_Alert($toUser, $alertType, $change['id']);
            $alert->setExtraDetails(array(
                'characterID' => $toUser,
                'action' => 'accept'
            ));
            $alert->setFromUserId($fromUser);
            $alertManager->addAlert($alert);
        }

        $db->update_query('posts', array('message' => $db->escape_string($change['message'])), 'pid = ' . $change['pid']);
        $db->delete_query('editprofile', 'id = ' . $id);
        $type = 'success';
        $text = 'Die Änderungen wurden akzeptiert';
        eval("\$banner = \"" . $templates->get('editprofile_banner') . "\";");
    }

    if ($_POST['accept'] == 'false') {
        $id = $db->escape_string($_POST['id']);
        $change = $db->fetch_array($db->simple_select('editprofile', '*', 'id = ' . $id));
        $message = 'Hallo!
    
    Leider musste ich deine Anfrage auf Steckbriefänderung aus folgenden Grund ablehnen:
    
    ' . $db->escape_string($_POST['decline_reason']) . '
    
    Der geänderte Steckbrief:
    '.$change['message'];
        editprofile_send_pm('Ablehnung deiner Steckbriefänderung', $message, $change['uid'], $mybb->user['uid']);
        $type = 'success';
        $text = 'Die Steckbriefänderung wurde abgelehnt';
        eval("\$banner = \"" . $templates->get('editprofile_banner') . "\";");
        $db->delete_query('editprofile', 'id = ' . $id);
    }

    $teamiesCanSeeOwn = intval($mybb->settings['editprofile_teamie']) == 0 ? 'not find_in_set(uid, "' . awayice_getUids($mybb->user['uid']) . '")' : '';
    $query = $db->simple_select('editprofile', '*', $teamiesCanSeeOwn);
    while ($row = $db->fetch_array($query)) {
        $character = '<a href="misc.php?action=editstecki_overview&id=' . $row['id'] . '">' . get_user($row['uid'])['username'] . '</a>';
        $reason = $row['editreason'];
        $id = $row['id'];
        eval("\$changes .= \"" . $templates->get("editprofile_modcp_bit") . "\";");
    }

    eval("\$page = \"" . $templates->get('editprofile_modcp') . "\";");
    output_page($page);
}

// edit post
$plugins->add_hook('editpost_do_editpost_start', 'editprofile_do_editpost');
function editprofile_do_editpost()
{
    global $post, $mybb, $db;
    $applicationArea = explode(',', $mybb->settings['editprofile_forum']);

    if (!in_array($post['fid'], $applicationArea)) return;

    $db->insert_query('editprofile', array(
        'uid' => $post['uid'],
        'message' => $db->escape_string($mybb->get_input('message')),
        'editreason' => $mybb->get_input('editreason'),
        'pid' => $post['pid']
    ));
    redirect('misc.php?action=editstecki', 'Du wirst weitergeleitet');
    die();
}

$plugins->add_hook('editpost_start', 'editprofile_editpost_start');
function editprofile_editpost_start()
{
    global $post, $mybb, $db;
    $applicationArea = explode(',', $mybb->settings['editprofile_forum']);

    $post = get_post($mybb->get_input('pid'));
    if (!in_array($post['fid'], $applicationArea)) return;

    $num_row = $db->num_rows($db->simple_select('editprofile', 'id', 'pid = ' . $post['pid']));
    if ($num_row > 0) {
        redirect('misc.php?action=editstecki', 'Du wirst weitergeleitet');
        die();
    }
}

// misc
$plugins->add_hook('misc_start', 'editprofile_misc');
function editprofile_misc()
{
    global $lang, $db, $mybb, $templates, $theme, $headerinclude, $header, $footer;

    // show when stecki file has already unapproved submits
    if ($_GET['action'] == 'editstecki') {
        $text = 'Deine Änderungen wurden eingetragen und werden bald von einem Teammitglied freigeschaltet.';
        eval("\$page = \"" . $templates->get('editprofile_unapprovededit') . "\";");
        output_page($page);
    }

    // show when teammember want to see changes
    if ($_GET['action'] == 'editstecki_overview') {
        if ($mybb->usergroup['canmodcp'] == 0) error_no_permission();
        $change = $db->fetch_array($db->simple_select('editprofile', '*', 'id = ' . $mybb->get_input('id')));
        $character = get_user($change['uid'])['username'];
        $id = $mybb->get_input('id');
        $reason = $change['editreason'];
        $new_string = nl2br($change['message']);
        $old_string = nl2br(get_post($change['pid'])['message']);

        require_once 'inc/3rdparty/Diff.php';
        $output = Diff::toTable(Diff::compare($old_string, $new_string));

        eval("\$page = \"" . $templates->get('editprofile_misc_overview') . "\";");
        output_page($page);
    }
}

function editprofile_send_pm($subject, $msg, $uid, $from)
{
    require_once MYBB_ROOT . "inc/datahandlers/pm.php";
    $pmhandler = new PMDataHandler;

    $pmhandler->set_data(array(
        'subject'    =>    $subject,
        'message'    =>    $msg,
        'fromid'    =>    $from,
        'toid'        =>    $uid
    ));

    if (!$pmhandler->validate_pm()) {
        $pm_errors = $pmhandler->get_friendly_errors();
        return $pm_errors;
    } else {
        $pminfo = $pmhandler->insert_pm();
    }
}

$plugins->add_hook("global_start", "editprofile_myalerts");
function editprofile_myalerts()
{
    global $mybb, $lang;

    if (!$mybb->user['uid']) return;

    if (function_exists('myalerts_is_activated') && myalerts_is_activated()) {
        class EditProfile_AlertFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
        {

            public function init()
            {
            }

            public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
            {
                $alertContent = $alert->getExtraDetails();
                $username = get_user($alertContent['characterID'])['username'];

                return sprintf(
                    'Deine Steckbriefänderungen von ' . $username . ' wurden angenommen',
                    $outputAlert['dateline']
                );
            }

            public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
            {
                return get_profile_link($alert->getFromUserId());
            }
        }

        $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();
        $formatterManager->registerFormatter(new EditProfile_AlertFormatter($mybb, $lang, 'accept_steckichange'));
    }
}