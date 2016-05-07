<?php
/**
 * Awaiting Activation Message 1.8

 * Copyright 2014 Matthew Rogowski

 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at

 ** http://www.apache.org/licenses/LICENSE-2.0

 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
**/

if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

if(!defined("PLUGINLIBRARY"))
{
    define("PLUGINLIBRARY", MYBB_ROOT."inc/plugins/pluginlibrary.php");
}


$plugins->add_hook("global_start", "aktivasyonmesaji_global");
$plugins->add_hook("misc_start", "aktivasyon_sayfasi");

function sld_aktivasyonmesaji_info()
{
	global $mybb, $lang, $plugins_cache;
	
	$lang->load("aktivasyonmesaji");
		
    $info = array(
        "name"          => $lang->aktivasyonmesaji_plugin,
        "description"   => $lang->aktivasyonmesaji_plugin_aciklama,
        "website"       => "http://www.accesstr.net",
        "author"        => "ozanakkaya (sledgeab)",
        "authorsite"    => "mailto:info@accesstr.net",
        "version"       => $lang->aktivasyonmesaji_plugin_versiyon,
        "guid"          => "",
        "compatibility" => "18*"
        );

    if(sld_aktivasyonmesaji_is_installed() && $plugins_cache['active']['aktivasyonmesaji'])
    {
        global $PL, $lang;
        $PL or require_once PLUGINLIBRARY;
		$gid = aktivasyonmesaji_settings_gid();
        $eklentiurl = $PL->url_append("index.php?module=config-settings",
                                   array("action" => "change",
										"gid" => $gid,
                                        "my_post_key" => $mybb->post_code));
										
        $bagisbuton = "https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=JET4FK7Q2CPTS";
		
        $info["description"] .= "<br /><a href=\"{$eklentiurl}\">$lang->aktivasyonmesaji_plugin_ayarsayfasi</a>.";
		$info["description"] .= "    | <a href=\"{$bagisbuton}\" target=\"_blank\">$lang->aktivasyonmesaji_plugin_ayarbagisbuton</a>.";		
    }
    return $info;
}


function sld_aktivasyonmesaji_is_installed()
{
    global $settings;

    if(isset($settings['aktivasyonmesaji_aktif']))
    {
        return true;
    }
}

function sld_aktivasyonmesaji_install()
{

    if(!file_exists(PLUGINLIBRARY))
    {
        flash_message("The selected plugin could not be installed because <a href=\"http://mods.mybb.com/view/pluginlibrary\">PluginLibrary</a> is missing.", "error");
        admin_redirect("index.php?module=config-plugins");
    }
	
	global $PL, $lang;
    $PL or require_once PLUGINLIBRARY;
	
    if($PL->version < 11)
    {
        flash_message($lang->aktivasyonmesaji_pluginlibrary_bulunamadi, "error");
        admin_redirect("index.php?module=config-plugins");
    }

	    $PL->settings($lang->aktivasyonmesaji, 
                  $lang->aktivasyonmesaji_ayarlar,
                  $lang->aktivasyonmesaji_ayarlar_aciklama,
                  array(
                      "aktif" => array(
                          "title" => $lang->aktivasyonmesaji_ayar_baslik,
                          "description" => $lang->aktivasyonmesaji_ayar_baslik_aciklama,
                          "value" => 1,
                          ),
                      )
        );

	$PL->templates("aktivasyonmesaji", 
                   "Aktivasyon Mesajý", 
                   array(
                       "aciklama" => "<table border=\"0\" cellspacing=\"{\$theme['borderwidth']}\" cellpadding=\"{\$theme['tablespace']}\" class=\"tborder\">
<tr><td class=\"thead\"><strong>{\$aktivasyonmesaji_title}</strong></td></tr><tr><td class=\"trow1\">{\$aktivasyonmesaji_message}</td></tr></table>",
                       "bar" => "<br /><div class=\"red_alert\">{\$aktivasyonmesaji_bar}</div>",
                       "sayfa" => "<html>
<head>
<title>{\$mybb->settings['bbname']} - {\$lang->aktivasyonmesaji_sayfa_etiket}</title>
{\$headerinclude}
</head>
<body>
{\$header}
{\$aktivasyonmesaji}
{\$search}
{\$footer}
</body>
</html>",));	
}


function sld_aktivasyonmesaji_uninstall()
{

	 global $PL;
    $PL or require_once PLUGINLIBRARY;

    $PL->settings_delete("aktivasyonmesaji");
	$PL->templates_delete("aktivasyonmesaji");
	require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
	find_replace_templatesets('header', '#' . preg_quote('{$aktivasyonmesaji}') . '#i', '');

}

function sld_aktivasyonmesaji_activate() 
{
	require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';	
	find_replace_templatesets("header", "#".preg_quote('{$unreadreports}')."#i", '{$unreadreports}{$aktivasyonmesaji}');
}

function sld_aktivasyonmesaji_deactivate()
{
  global $PL;
    $PL or require_once PLUGINLIBRARY;
	
	    $PL->cache_delete("aktivasyonmesaji");
	
	require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
	find_replace_templatesets('header', '#' . preg_quote('{$aktivasyonmesaji}') . '#i', '');	
}

function aktivasyon_sayfasi()
{
    global $mybb, $templates, $lang, $header, $headerinclude, $footer;

	$lang->load("aktivasyonmesaji");
	
	add_breadcrumb($lang->aktivasyonmesaji_sayfa_etiket, "misc.php?action=aktivasyon_sayfasi");
	
    if($mybb->get_input('action') == 'aktivasyon_sayfasi')
    {
       	if(($mybb->user['usergroup'] == 5) && ($mybb->settings['aktivasyonmesaji_aktif']))
		{
		switch($mybb->settings['regtype'])
		{
			case 'admin':
				$aktivasyonmesaji_title = $lang->aktivasyonmesaji_title_admin;
				$aktivasyonmesaji_message = $lang->sprintf($lang->aktivasyonmesaji_message_admin, $mybb->user['username'], $mybb->settings['bbname'], $mybb->settings['bburl']);
				break;
			case 'verify': 			
				$aktivasyonmesaji_title = $lang->aktivasyonmesaji_title_verify;
				$aktivasyonmesaji_message = $lang->sprintf($lang->aktivasyonmesaji_message_verify, $mybb->user['username'],$mybb->user['email'], $mybb->settings['bbname'], $mybb->settings['bburl']);
				break;
			case 'both':
				$aktivasyonmesaji_title = $lang->aktivasyonmesaji_title_both;
				$aktivasyonmesaji_message = $lang->sprintf($lang->aktivasyonmesaji_message_both, $mybb->user['username'], $mybb->user['email'], $mybb->settings['bbname'], $mybb->settings['bburl']);
				break;
			default: 
				$aktivasyonmesaji_title = $lang->aktivasyonmesaji_title_default;
				$aktivasyonmesaji_message = $lang->sprintf($lang->aktivasyonmesaji_message_default, $mybb->user['username'],$mybb->user['email'], $mybb->settings['bbname'], $mybb->settings['bburl']);
				break;
		}
	
		eval('$aktivasyonmesaji  = "' . $templates->get('aktivasyonmesaji_aciklama') . '";');
        eval("\$page = \"".$templates->get("aktivasyonmesaji_sayfa")."\";");
        output_page($page);
	
		} else {
		redirect("index.php", $lang->aktivasyonmesaji_yonlendirme);

    }
}
}

function aktivasyonmesaji_global()
{
	global $mybb, $lang, $templates, $theme, $aktivasyonmesaji, $aktivasyonmesaji_bar;
	
	$lang->load("aktivasyonmesaji");
	
    if(isset($templatelist))
   {
       $templatelist .= '';
   }
 
	if(THIS_SCRIPT== 'misc.php')
		{
		$templatelist .= 'aktivasyonmesaji_sayfa, aktivasyonmesaji_aciklama';
		}
	if (THIS_SCRIPT == "aktivasyonmesaji.php") {
		$templatelist .= 'aktivasyonmesaji_bar';
	}

	if(($mybb->user['usergroup'] == 5) && ($mybb->settings['aktivasyonmesaji_aktif']))
	{
		switch($mybb->settings['regtype'])
		{
			case 'admin': 
				$aktivasyonmesaji_bar = $lang->sprintf($lang->aktivasyonmesaji_bar_admin, $mybb->user['username']);
				break;
			case 'verify': 
				$aktivasyonmesaji_bar = $lang->sprintf($lang->aktivasyonmesaji_bar_verify, $mybb->user['username'],$mybb->user['email'], $mybb->settings['bburl']);
				break;
			case 'both': 
				$aktivasyonmesaji_bar = $lang->sprintf($lang->aktivasyonmesaji_bar_both, $mybb->user['username'],$mybb->user['email'], $mybb->settings['bburl']);
				break;
			default: 
				$aktivasyonmesaji_bar = $lang->sprintf($lang->aktivasyonmesaji_bar_default, $mybb->user['username'],$mybb->user['email'], $mybb->settings['bburl']);
				break;
		}
			if(THIS_SCRIPT== 'misc.php')
			{
			return;
			}
		eval("\$aktivasyonmesaji = \"".$templates->get('aktivasyonmesaji_bar')."\";");
	}
}

function aktivasyonmesaji_settings_gid()
{
	global $db;
	
	$query = $db->simple_select("settinggroups", "gid", "name = 'aktivasyonmesaji'", array(
		"limit" => 1
	));
	$gid   = (int) $db->fetch_field($query, "gid");
	
	return $gid;
}

?>