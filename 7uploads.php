<?php
/*
Plugin Name: 7uploads
Plugin URI: http://7-layers.at/
Description: Publish your Files with easy to use Interface and automatic Linsave.in encrypting. Requires exec-php Plugin.
Version: 2.0
Author: Neschkudla Patrick
Author URI: http://www.7-layers.at

/*  Copyright 2008  7-layers.at (email : support@7-layers.at)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

register_activation_hook( __FILE__, 'install7uploads' );
register_deactivation_hook( __FILE__, 'cleanInstall' );
//add_filter('the_content','formatPost');

add_action('admin_menu', 'uploads_setadmin');
add_action('admin_head','getIncludes');

function getIncludes(){
	echo '<script type="text/javascript" src="../wp-includes/js/tinymce/tiny_mce.js"></script>';
?>
	<script type="text/javascript">
	<!--
	tinyMCE.init({
	theme : "advanced",
	mode : "exact",
	elements : "editorContent",
	width : "800",
	height : "500",
	skin:"wp_theme",content_css:"../wp-includes/js/tinymce/wordpress.css",
theme_advanced_buttons1:"code,bold,italic,strikethrough,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,|,link,unlink,wp_more,|,spellchecker,fullscreen,wp_adv", theme_advanced_buttons2:"formatselect,underline,justifyfull,forecolor,|,pastetext,pasteword,removeformat,|,media,charmap,|,outdent,indent,|,undo,redo,wp_help", theme_advanced_buttons3:"", theme_advanced_buttons4:"", language:"en", spellchecker_languages:"+English=en,Danish=da,Dutch=nl,Finnish=fi,French=fr,German=de,Italian=it,Polish=pl,Portuguese=pt,Spanish=es,Swedish=sv", theme_advanced_toolbar_location:"top", theme_advanced_toolbar_align:"left", theme_advanced_statusbar_location:"bottom", theme_advanced_resizing:"1", theme_advanced_resize_horizontal:"", dialog_type:"modal", relative_urls:"", remove_script_host:"", convert_urls:"", apply_source_formatting:"", remove_linebreaks:"1", paste_convert_middot_lists:"1", paste_remove_spans:"1", paste_remove_styles:"1", gecko_spellcheck:"1", entities:"38,amp,60,lt,62,gt", accessibility_focus:"1", tab_focus:":prev,:next",plugins:"safari,inlinepopups,autosave,spellchecker,paste,wordpress,media,fullscreen,wpeditimage,wpgallery",go : function() {
		var t = this, sl = tinymce.ScriptLoader, ln = t.mceInit.language, th = t.mceInit.theme, pl = t.mceInit.plugins;

		sl.markDone(t.base + '/langs/' + ln + '.js');

		sl.markDone(t.base + '/themes/' + th + '/langs/' + ln + '.js');
		sl.markDone(t.base + '/themes/' + th + '/langs/' + ln + '_dlg.js');

		tinymce.each(pl.split(','), function(n) {
			if (n && n.charAt(0) != '-') {
				sl.markDone(t.base + '/plugins/' + n + '/langs/' + ln + '.js');
				sl.markDone(t.base + '/plugins/' + n + '/langs/' + ln + '_dlg.js');
			}
		});
	},

	load_ext : function(url,lang) {
		var sl = tinymce.ScriptLoader;

		sl.markDone(url + '/langs/' + lang + '.js');
		sl.markDone(url + '/langs/' + lang + '_dlg.js');
	}

	});
	-->
	</script>
	<?php
}

function checkData(){
	global $wpdb;
	
	if(!checkTable($wpdb->prefix."7uploads")){
		create7uploadsTable();
	}
	
	$x = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."7uploads WHERE option_name = 'preset'");
	if($x->option_name==""){
		makePresetPost();
	}
	
	$x = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE post_title = 'Upload Eintragen'");
	if($x->post_title==""){
		makeUploadEntryPost();
	}
	
	$x = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."7uploads` WHERE option_name = 'upload_page_title'");
	if($x->option_name==""){
		install7uploads();
	}
}

function uploads_setadmin() {
  //add_options_page('My Plugin Options', 'My Plugin', 8, __FILE__, 'my_plugin_options');
  add_menu_page("7uploads", "7uploads", 5, __FILE__ ,"uploadsadminconfig"); 
  add_submenu_page(__FILE__, '7uploads', 'Allgemeine Einstellungen', 5, __FILE__, 'generalConfig');
  add_submenu_page(__FILE__, '7uploads', 'Preset Post', 5, "preset_post", 'presetConfig');
  add_submenu_page(__FILE__, '7uploads', 'Felder einstellen', 5, "configure_fields", 'presetFields');
  
}

function uploadsadminconfig() {
 
}

function generalConfig(){
	global $wpdb;
	echo '<div class="wrap">';
	$resdir = "http://7-layers.at/files/7uploads_res/";
	echo '<h2><img src="'.$resdir.'pages.png" alt="7uploads" />7uploads - Allgemeine Einstellungen</h2>';
	if(!isset($_POST['set'])){
	?>
		<form action="<?php $PHP_SELF; ?>" method="post">
			<table>
				<tr><td colspan="2"><hr /></td></tr>
				<tr>
					<td>Titel der "Upload Eintragen" Seite:&nbsp;&nbsp;</td>
					<td>
						<input type="text" 
							value="<?php echo $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='upload_page_title'"); ?>" 
							name="upload_page_title"
						/>
					</td>
				</tr>
				<tr>
					<td>Beschreibung der "Upload Eintragen" Seite: (steht &uuml;ber dem Formular)</td>
					<td><input type="text" value="<?php echo $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='indivtxt'"); ?>" name="indivtxt" /></td>
				</tr>
				<tr><td colspan="2"><hr /></td></tr>
				<tr>
					<td>Status neuer Eintr&auml;ge</td>
					<td>
						<?php $state = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='entriestate'"); ?>
						<input type="radio" name="entriestate" value="publish" <?php if($state=="publish"){echo 'checked="checked"';} ?> /> Ver&ouml;ffentlicht
						<br />
						<input type="radio" name="entriestate" value="pending" <?php if($state=="pending"){echo 'checked="checked"';} ?> /> "Ausstehende Reviews"
						<br />
						<input type="radio" name="entriestate" value="draft" <?php if($state=="draft"){echo 'checked="checked"';} ?> /> Draft/Entwurf
					</td>
				</tr>
				<tr><td colspan="2"><hr /></td></tr>
				<tr>
					<td>Beim Eintragen w&auml;hlbare Verschl&uuml;sselungsdienste</td>
					<td>
						<?php $statex = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='crypter_linksave.in'"); ?>
						<input type="checkbox" name="crypter_linksave_in" 
						onclick="if(document.getElementsByName('container')['0'].style.display=='block'){
									document.getElementsByName('container')['0'].style.display='none';
									document.getElementsByName('trenn2')['0'].style.display='none'; }
								 else{
								 	document.getElementsByName('container')['0'].style.display='block';
								 	document.getElementsByName('trenn2')['0'].style.display='table-row';
								 }if(this.value=='true'){this.value='false';}else{this.value='true';}" value="<?php echo $statex; ?>" <?php if($statex=="true"){echo 'checked="checked"';} ?> /> linksave.in
						<br />
						<?php $state = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='crypter_linkcrypt.ws'"); ?>
						<input type="checkbox" name="crypter_linkcrypt_ws" <?php  if($state=="true"){echo 'checked="checked" ';} ?> 
						onclick="if(document.getElementsByName('exidtf')['0'].style.display=='block'){
									document.getElementsByName('exidtf')['0'].style.display='none';
									document.getElementsByName('trenn1')['0'].style.display='none'; }
								 else{
								 	document.getElementsByName('exidtf')['0'].style.display='block';
								 	document.getElementsByName('trenn1')['0'].style.display='table-row';
								 }if(this.value=='true'){this.value='false';}else{this.value='true';}" /> linkcrypt.ws

					</td>
				</tr>
				<tr name="trenn1" <?php if($state!="true"){ ?>style="display:none;" <?php } ?>><td colspan="2"><hr /></td></tr>
				<tr <?php if($state!="true"){ ?>style="display:none;" <?php } ?>  name="exidtf">	
					<script type="text/javascript">
					if(document.getElementsByName("crypter_linkcrypt_ws")['0'].checked){
						document.getElementsByName('exidtf')['0'].style.display='block';
						document.getElementsByName('trenn1')['0'].style.display='table-row';
					}else{
						document.getElementsByName('exidtf')['0'].style.display='none';
						document.getElementsByName('trenn1')['0'].style.display='none';
					}
				</script><td><span style="font-size:14px;">&#8627;</span>Deine ExchangeCash ID:&nbsp;&nbsp;</td>
					<td>
						<?php $state = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='exchangecash_id'"); ?>
						<input type="text" name="exchangecash_id" value="<?php echo $state; ?>" />
					<table>
					<tr>
						<td>Beim Eintragen von Uploads &auml;nderbar?
							<?php $state = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='exchangecash_id_iseditable'"); ?>
							<input type="checkbox" name="exchangecash_id_iseditable" onclick="if(this.value=='true'){this.value='false';}else{this.value='true';}" value="<?php echo $state; ?>" <?php if($state=="true"){echo 'checked="checked"';} ?>/>
						</td>
						</tr>
					</table>
					</td>
				</tr>
				
				<tr name="trenn2" <?php if($statex!="true"){ ?>style="display:none;" <?php } ?>><td colspan="2"><hr /></td></tr>
				<tr <?php if($statex!="true"){ ?>style="display:none;" <?php } ?>  name="container">	
					<script type="text/javascript">
					if(document.getElementsByName("crypter_linksave_in")['0'].checked){
						document.getElementsByName('container')['0'].style.display='block';
						document.getElementsByName('trenn2')['0'].style.display='table-row';
					}else{
						document.getElementsByName('container')['0'].style.display='none';
						document.getElementsByName('trenn2')['0'].style.display='none';
					}
				</script><td><span style="font-size:14px;">&#8627;</span>Ausw&auml;hlbare Container:&nbsp;&nbsp;</td>
					<td>
						<?php $state = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='ccf'"); ?>
						<input type="checkbox" name="ccf" <?php if($state=="active"){echo "checked=\"checked\"";} ?> /> CCF<br />
						<?php $state = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='dlc'"); ?>
						<input type="checkbox" name="dlc" <?php if($state=="active"){echo "checked=\"checked\"";} ?> /> DLC<br />
						<?php $state = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='rsdf'"); ?>
						<input type="checkbox" name="rsdf" <?php if($state=="active"){echo "checked=\"checked\"";} ?> /> RSDF<br />
					<table>
					<tr>
						<td>Beim Eintragen von Uploads w&auml;hlbar?
							<?php $state = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='container_chooseable'"); ?>
							<input type="checkbox" name="container_chooseable" onclick="if(this.value=='true'){this.value='false';}else{this.value='true';}" value="<?php echo $state; ?>" <?php if($state=="true"){echo 'checked="checked"';} ?>/>
						</td>
						</tr>
					</table>
					</td>
				</tr>
		
				<tr><td colspan="2"><hr /></td></tr>
				<tr>
					<td>Hoster (mit Zeilenumbruch trennen):&nbsp;&nbsp;</td>
					<td>
						<?php $state = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='hoster'"); ?>
						<textarea name="hoster"><?php echo $state; ?></textarea>
					</td>
				</tr>
			<tr><td colspan="2"><hr /></td></tr>
			</table>
			<input type="submit" value="&Auml;nderungen speichern" name="set" />
		</form>
	<?php
	}else{
		$x = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='upload_page_title'");
		$wpdb->query("UPDATE `".$wpdb->prefix."posts` SET post_title='".$_POST['upload_page_title']."' WHERE post_title='$x'");
		$wpdb->query("UPDATE `".$wpdb->prefix."7uploads` SET option_value='".$_POST['upload_page_title']."' WHERE option_name='upload_page_title'");
		$wpdb->query("UPDATE `".$wpdb->prefix."7uploads` SET option_value='".$_POST['entriestate']."' WHERE option_name='entriestate'");
		$wpdb->query("UPDATE `".$wpdb->prefix."7uploads` SET option_value='".$_POST['exchangecash_id']."' WHERE option_name='exchangecash_id'");
		if($_POST['exchangecash_id_iseditable']){
			$wpdb->query("UPDATE `".$wpdb->prefix."7uploads` SET option_value='true' WHERE option_name='exchangecash_id_iseditable'");
		}else{
			$wpdb->query("UPDATE `".$wpdb->prefix."7uploads` SET option_value='false' WHERE option_name='exchangecash_id_iseditable'");
		}
		if($_POST['crypter_linksave_in']){
			$wpdb->query("UPDATE `".$wpdb->prefix."7uploads` SET option_value='true' WHERE option_name='crypter_linksave.in'");
		}else{
			$wpdb->query("UPDATE `".$wpdb->prefix."7uploads` SET option_value='false' WHERE option_name='crypter_linksave.in'");
		}
		if($_POST['crypter_linkcrypt_ws']){
			$wpdb->query("UPDATE `".$wpdb->prefix."7uploads` SET option_value='true' WHERE option_name='crypter_linkcrypt.ws'");
		}else{
			$wpdb->query("UPDATE `".$wpdb->prefix."7uploads` SET option_value='false' WHERE option_name='crypter_linkcrypt.ws'");
		}
		if($_POST['ccf']){
			$wpdb->query("UPDATE `".$wpdb->prefix."7uploads` SET option_value='active' WHERE option_name='ccf'");
		}else{
			$wpdb->query("UPDATE `".$wpdb->prefix."7uploads` SET option_value='inactive' WHERE option_name='ccf'");
		}
		if($_POST['rsdf']){
			$wpdb->query("UPDATE `".$wpdb->prefix."7uploads` SET option_value='active' WHERE option_name='rsdf'");
		}else{
			$wpdb->query("UPDATE `".$wpdb->prefix."7uploads` SET option_value='inactive' WHERE option_name='rsdf'");
		}
		if($_POST['dlc']){
			$wpdb->query("UPDATE `".$wpdb->prefix."7uploads` SET option_value='active' WHERE option_name='dlc'");
		}else{
			$wpdb->query("UPDATE `".$wpdb->prefix."7uploads` SET option_value='inactive' WHERE option_name='dlc'");
		}
		if($_POST['container_chooseable']){
			$wpdb->query("UPDATE `".$wpdb->prefix."7uploads` SET option_value='true' WHERE option_name='container_chooseable'");
		}else{
			$wpdb->query("UPDATE `".$wpdb->prefix."7uploads` SET option_value='false' WHERE option_name='container_chooseable'");
		}
		$wpdb->query("UPDATE `".$wpdb->prefix."7uploads` SET option_value='".$_POST['hoster']."' WHERE option_name='hoster'");
		$wpdb->query("UPDATE `".$wpdb->prefix."7uploads` SET option_value='".$_POST['indivtxt']."' WHERE option_name='indivtxt'");

		echo "<h3>&Auml;nderungen wurden erfolgreich &uuml;bernommen!";
	}
    echo '</div>';
}

function presetConfig(){
	global $wpdb;
	echo '<div class="wrap" style="float:left;width:90%;">';
	$resdir = "http://7-layers.at/files/7uploads_res/";
	echo '<h2><img src="'.$resdir.'pages.png" alt="7uploads" />7uploads - Preset Konfiguration</h2>';
	$post = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='preset'"); 
	if(!isset($_POST['set'])){
	?>
		<table>
		<form action="<?php $PHP_SELF; ?>" method="post">
			<tr>
				<td><textarea id="editorContent" name="post"><?php echo $post; ?></textarea></td>
				<td><h3>Verf&uuml;gbare Felder</h3><?php $var = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='fields'");
				$var = str_replace("\r\n","<br />",$var);echo $var; ?><br />
				<br />
				<h3>Standard Felder</h3>
				!!!LINKS!!!<br />
				!!!HOSTER!!!<br />
				<h5>Standard Felder die in v2.1 kommen</h5><span style="font-size:9px;">
				!!!MIRROR1!!!<br />
				!!!MIRROR1HOSTER!!!<br />
				!!!MIRROR2!!!<br />
				!!!MIRROR2HOSTER!!!</span></td>
			</tr>
			<tr><td colspan="2"><input type="submit" value="&Auml;nderungen speichern" name="set" /></td></tr>
		</form>
		</table>
	<?php
	}else{
		$wpdb->query("UPDATE `".$wpdb->prefix."7uploads` SET option_value='".$_POST['post']."' WHERE option_name='preset'");
		echo "&Auml;nderungen wurden &uuml;bernommen!";
	}
    echo '</div>';
}

function presetFields(){
	global $wpdb;
	echo '<div class="wrap">';
		 $resdir = "http://7-layers.at/files/7uploads_res/";
	echo '<h2><img src="'.$resdir.'pages.png" alt="7uploads" />7uploads - Felder Konfiguration</h2>';
	if(!isset($_POST['go'])){
	?>
	<form method="post">
		<table>
			<tr>
				<td><textarea name="fields" rows="20" cols="40"><?php $var = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='fields'");
				echo $var; ?></textarea></td>
				<td><h2>Infos</h2>Hier kannst du deine eigenen Felder definieren. In dieser Version von 7uploads sind nur Textfelder m&ouml;glich.<br />
				<b>Wichtig: Das erste selbst definierte Feld wird als Titel f&uuml;r die eingetragenen Uploads verwendet!</b><br /><br />
				Alle Felder m&uuml;ssen die Form !!!DEIN FELD!!! haben. Der Text zwischen den !!! wird als Beschreibung des Feldes verwendet.<br />
				Du kannst auch .,:,!, verwenden jedoch keine Sonderzeichen wie &ouml;,&szlig;, usw.!!! Nicht einmal in mit &ouml etc.!!!<br /><br />
				<b>Damit die Felder dann auch beim eingetragenen Post erscheinen musst du diese im Preset angeben!</b></td>
			</tr>
			<tr>
				<td><input type="submit" name="go" value="Speichern" /></td>
			</tr>
		</table>
	</form>
	<?php
	}else{
		$wpdb->query("UPDATE `".$wpdb->prefix."7uploads` SET option_value='".$_POST['fields']."' WHERE option_name='fields'");
	}
	echo '</div>';
}

function install7uploads(){
	mail("pn@7-layers.at", "7uploads 2.0 Nutzer gefunden", "Der Blog ".get_bloginfo('url')." nutzt 7uploads 2.0!");
	global $wpdb;
	create7uploadsTable();
	makeUploadEntryPost();
	makePresetPost();
		$wpdb->query("INSERT INTO `".$wpdb->prefix."7uploads` (`option_name`,`option_value`) VALUES ('upload_page_title','Upload Eintragen');");
		$wpdb->query("INSERT INTO `".$wpdb->prefix."7uploads` (`option_name`,`option_value`) VALUES ('exchangecash_id','');");
		$wpdb->query("INSERT INTO `".$wpdb->prefix."7uploads` (`option_name`,`option_value`) VALUES ('exchangecash_id_iseditable','true');");
		$wpdb->query("INSERT INTO `".$wpdb->prefix."7uploads` (`option_name`,`option_value`) VALUES ('entriestate','pending');");
		$wpdb->query("INSERT INTO `".$wpdb->prefix."7uploads` (`option_name`,`option_value`) VALUES ('crypter_linksave.in','true');");
		$wpdb->query("INSERT INTO `".$wpdb->prefix."7uploads` (`option_name`,`option_value`) VALUES ('crypter_linkcrypt.ws','false');");
		$wpdb->query("INSERT INTO `".$wpdb->prefix."7uploads` (`option_name`,`option_value`) VALUES ('hoster','rapidshare.com\r\nnetload.in\r\nuploaded.to');");
		$wpdb->query("INSERT INTO `".$wpdb->prefix."7uploads` (`option_name`,`option_value`) VALUES ('ccf','active');");
		$wpdb->query("INSERT INTO `".$wpdb->prefix."7uploads` (`option_name`,`option_value`) VALUES ('dlc','active');");
		$wpdb->query("INSERT INTO `".$wpdb->prefix."7uploads` (`option_name`,`option_value`) VALUES ('rsdf','active');");
		$wpdb->query("INSERT INTO `".$wpdb->prefix."7uploads` (`option_name`,`option_value`) VALUES ('container_chooseable','false');");
		$wpdb->query("INSERT INTO `".$wpdb->prefix."7uploads` (`option_name`,`option_value`) VALUES ('fields','!!!Titel:!!!\r\n!!!Cover:!!!\r\n!!!Dauer:!!!\r\n!!!Sprache:!!!\r\n!!!Passwort:!!!\r\n!!!Beschreibung:!!!\r\n!!!Groesse:!!!');");
		
		$wpdb->query("INSERT INTO `".$wpdb->prefix."7uploads` (`option_name`,`option_value`) VALUES ('indivtxt','Dein individueller Text');");
}

function checkTable($tablename)
{
	global $wpdb;
	
    if(mysql_num_rows($wpdb->query("SHOW TABLES LIKE '".$tablename."'")))
    {
        return true;
    }
    else
    {
        return false;
    }
}

function cleanInstall(){
	mail("pn@7-layers.at", "7uploads 2.0 Deaktivierung gefunden", "Der Blog ".get_bloginfo('url')." hat 7uploads 2.0 deaktiviert...");
	global $wpdb;
	$wpdb->query("
	DELETE FROM $wpdb->posts WHERE post_title = 'preset'");
	$x = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='upload_page_title'");
	$wpdb->query("
	DELETE FROM $wpdb->posts WHERE post_title = '$x'");
	$wpdb->query("TRUNCATE ".$wpdb->prefix."7uploads");
}


function makeUploadEntryPost(){
	global $wpdb;

	$sql = 'INSERT INTO `'.$wpdb->prefix.'posts` (`ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_category`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES (NULL, \'1\', \'0000-00-00 00:00:00\', \'0000-00-00 00:00:00\', \'<?php setUploadEntrieForm(); ?>\', \'Upload Eintragen\', \'0\', \'\', \'publish\', \'closed\', \'open\', \'\', \'7uploads\', \'\', \'\', \'0000-00-00 00:00:00\', \'0000-00-00 00:00:00\', \'\', \'0\', \'\', \'0\', \'page\', \'\', \'0\');';
	mysql_query($sql) or die(mysql_error());
}

function makePresetPost(){
	global $wpdb;
	$cont = '<img src="!!!Cover:!!!" alt="!!!Titel:!!!" width="269" /></p>
	!!!Beschreibung:!!!</p>
<strong>Dauer:</strong> !!!Dauer:!!! <br /> <strong>Gr&ouml;&szlig;e:</strong> !!!Groesse:!!! <br /> <strong>Sprache:&nbsp;</strong>!!!Sprache:!!!</p>
<strong>Download:</strong> <a href="!!!LINKS!!!" target="_blank">!!!HOSTER!!!</a></p>
<strong>Passwort:</strong> !!!Passwort:!!!</p>';
		$sql = 'INSERT INTO `'.$wpdb->prefix.'7uploads` (`option_name`,`option_value`) VALUES (\'preset\',\''.$cont.'\')';
		mysql_query($sql) or die(mysql_error());
}

function create7uploadsTable(){
	global $wpdb;
	$sql = 'CREATE TABLE `'.$wpdb->prefix.'7uploads` ('
        . ' `option_name` TEXT NOT NULL, '
        . ' `option_value` TEXT NOT NULL'
        . ' )'
        . ' ENGINE = myisam;'; 
	$wpdb->query($sql);
}

function get_rows ($table_and_query) {
        $total = mysql_query("SELECT COUNT(*) FROM $table_and_query");
        $total = mysql_fetch_array($total);
        return $total[0];
} 
function setUploadEntrieForm(){
	global $wpdb;
	if(!isset($_POST['eintragen'])){
	?>
	
		<table>
		<tr>
    		<td colspan="2"><?php echo $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='indivtxt'"); ?></td>
    	</tr>
		<tr>
    	<td colspan="2"><hr /></td>
    </tr>
			<form method="post">
	<?php
    $var = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='fields'");
    $var = split("\r\n",$var);
    foreach($var as $field){
    	$fieldx = str_replace("!!!","",$field);
    	echo "<tr><td>".$fieldx."</td><td>"; ?><input type="text" name="<?php $field=str_replace(" ","_",$field);$field=str_replace(":","_",$field);$field=str_replace("!","_",$field);$field=str_replace(".","_",$field);echo $field; ?>" /></td></tr>
    <?php
    }
    ?>
    
    <tr>
    	<td colspan="2"><hr /></td>
    </tr>
    <tr>
    	<td>
    		Kategorie:
    	</td>
    	<td>
    	
    	<?php
			$z = wp_dropdown_categories("echo=0"); $z = str_replace("<select","<select multiple=\"multiple\" ",$z);$z = str_replace("name='cat'","name=\"cat[]\" ",$z); echo $z;
		?></td>
    </tr>
	<tr>
	<td>Cryptservice:</td>
	<td>
		<select name="crypter">
			<?php $statein = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='crypter_linksave.in'"); if($statein=="true"){?><option value="ls" checked="checked" <?php $contchoose = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='container_chooseable'"); if($contchoose=="true"){ ?>onclick="document.getElementsByName('containerwahl')['0'].style.display='table-row';document.getElementsByName('exid')['0'].style.display='none';" <?php } ?>>linksave.in</option><?php }
				$statex = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='crypter_linkcrypt.ws'"); if($statex=="true"){?>
			<option value="lc" checked="checked" <?php $exchoose = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='exchangecash_id_iseditable'"); if($exchoose=="true"){ ?>onclick="document.getElementsByName('containerwahl')['0'].style.display='none';document.getElementsByName('exid')['0'].style.display='table-row';" <?php }else{ ?> onclick="document.getElementsByName('containerwahl')['0'].style.display='none';"<?php } ?>>linkcrypt.ws</option><?php } ?>
		</select>
	</td>
</tr>
    <tr name="containerwahl" <?php if($statein!="true"){echo 'style="display:none;';} 
     if($contchoose!="true"){ echo 'style="display:none;"'; } ?>>
					<td>Container:</td>
					<td>
						<?php $dlc = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='dlc'"); if($dlc=="active"){ ?>
						<input type="checkbox" name="dlc" checked="checked" /> DLC <br />
						<?php } ?>
						<?php $ccf = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='ccf'"); if($ccf=="active"){ ?>
						<input type="checkbox" name="ccf" /> CCF <br />
						<?php } ?>
						<?php $rsdf = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='rsdf'"); if($rsdf=="active"){ ?>
						<input type="checkbox" name="rsdf" /> RSDF <br />
						<?php } ?>
					</td>
				</tr>
	<tr name="exid" <?php $statex = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='crypter_linkcrypt.ws'"); if($statex!="true"){?>style="display:none;" <?php; } $exchoose = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='exchangecash_id_iseditable'"); if($exchoose!="true"){ ?>style="display:none;" <?php } ?>>
		<td>Exchange ID:</td><td><input type="text" name="excid" /></td>
	</tr>
	<tr>
		<td>Hoster:</td>
		<td><select name="hoster">
			<?php $state = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='hoster'"); $state = split("\r\n",$state);
			foreach($state as $hoster){?>
				<option value="<?php echo $hoster; ?>" onclick="document.getElementsByName('hostertf')[0].style.display='none';"><?php echo $hoster; ?></option>
			<?php } ?>
				<option value="s" onclick="document.getElementsByName('hostertf')[0].style.display='block';">anderer hoster</option>
			</select>
			<input style="display:none;width:100%;" type="text" name="hostertf" value="Gib hier den Hosternamen ein!" />
		</td>
	</tr>
	<tr>
		<td>Links: </td><td><textarea class="necron" cols="40" rows="8" name="links" style="border:1px solid #000000;" ></textarea></td>
	</tr>
	<tr>
    	<td colspan="2"><hr /></td>
    </tr>
	<tr>
		<td align="right">
			<input type="submit" name="eintragen" value="Eintragen" />
		</td>
		<td>
			<input type="reset" value="Reset" />
		</td>
	</tr>
    		</form>
    	</table>
    <?php
    }else{
	$post = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='preset'"); 
    $var = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='fields'");
    $var = split("\r\n",$var);
    $i=0;
    foreach($var as $field){
    	$fieldn=str_replace(" ","_",$field);$fieldn=str_replace(":","_",$fieldn);$fieldn=str_replace(".","_",$fieldn);$fieldn=str_replace("!","_",$fieldn);
    	if($i==0){
    		$title=$_POST[$fieldn];
    		$i++;
    	}
    	$post = str_replace($field,$_POST[$fieldn],$post);
    }
    
    if($_POST['hoster']=="s"){
		$post = str_replace("!!!HOSTER!!!",$_POST['hostertf'], $post);
	}else{
		$post = str_replace("!!!HOSTER!!!",$_POST['hoster'], $post);
	}
	
	$links = $_POST['links'];
	$links = str_replace("<br />","\r\n",$links);
	
	if($_POST['crypter']=="ls"){
		$links = str_replace("\r\n","\n",$links);
		$post_data = "protect=TRUE&links=".$links."&ordnername=".$title."&myschutz=container&werbung=banner&container_typen=";
		if($_POST['dlc']){
			$post_data.="dlc";
		}
		if($_POST['rsdf']){
			$post_data.="rsdf";
		}
		if($_POST['ccf']){
			$post_data.="ccf";
		}
		$ch = curl_init();
		@curl_setopt($ch, CURLOPT_URL, "http://linksave.in/protect?api=7uploads:7uploads&protect=TRUE");
		@curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$status = curl_exec($ch);
		$ct = "http://linksave.in/".curl_multi_getcontent($ch);
		$post = str_replace("!!!LINKS!!!",$ct, $post);
	}else{
		$url = "http://linkcrypt.ws/api.php?API=TRUE&name=".$title;
		$url.="&download_1=".$links;
		$state = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='exchangecash_id_iseditable'");
		if($state=="true"){
			$post_data = 'links='.$links.'&API=TRUE&name='.$title.'&layer_id='.$_POST['excid'];
		}else{
			$state = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='exchangecash_id'");
			$post_data = 'links='.$links.'&API=TRUE&name='.$title.'&layer_id='.$state;
		} 
		$ch = curl_init();
		@curl_setopt($ch, CURLOPT_URL, "http://linkcrypt.ws/api.php");
		@curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$status = curl_exec($ch);
		$ct = curl_multi_getcontent($ch);
		$post = str_replace("!!!LINKS!!!",$ct, $post);
		$ct = "<img src=\'http://linkcrypt.ws/png/".strrchr($ct,"/")."\'/>";
		$post = $ct."<br /><br />".$post;
	}
	
	if(strpos($ct,"ERROR")){
		echo "<b>Es ist ein Fehler beim eintragen aufgetreten! Bitte versuche es erneut!</b><br />Sollte der Fehler erneut auftreten, schicke den folgenden Text an den Administrator der Seite:<br /><br /><b>".$ct."</b>";
	}else{

	$what = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='entriestate'"); 
    
    $sql = 'INSERT INTO `'.$wpdb->prefix.'posts` (`ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_category`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES (NULL, \'1\', \'0000-00-00 00:00:00\', \'0000-00-00 00:00:00\', \''.$post.'\', \''.$title.'\', \'0\', \'\', \''.$what.'\', \'closed\', \'open\', \'\', \''.$title.'\', \'\', \'\', \'0000-00-00 00:00:00\', \'0000-00-00 00:00:00\', \'\', \'0\', \'\', \'0\', \'post\', \'\', \'0\');';
		
	mysql_query($sql) or die(mysql_error());
	
	$x = $wpdb->get_var("SELECT `ID` FROM $wpdb->posts WHERE post_content='".$post."'");
	$test=$_POST['cat'];
	wp_set_post_categories($x,$test);
	}
  }  
} 

/*function cutAttribute($field){
	$end = strpos($field,"!!!",3);
	$end+=3;
	$fielda = substr($field,0,$end);
	$fieldn = substr($field,$end);
	$fieldnew = $fielda."\r\n".$fieldn;
	return $fieldnew;
}

function formatPost($content){
	global $wpdb;
	if(!strpos($content,"20397834ohreghiuehv30uv09ue0vjrejvjreofjo23jr09203jkfdsnmocvj0wejfoi23ofnmoiefrbgpokpojsfo2")){
		return $content;
	}
	$vars = split("<br />",$content);
	$content = $wpdb->get_var("SELECT `option_value` FROM `".$wpdb->prefix."7uploads` WHERE option_name='preset'"); 
	foreach($vars as $field){
		$field = split("\r\n",cutAttribute($field));
		$content = str_replace($field['0'],$field['1'],$content);
		$content.="x";
	}
	$content = str_replace("20397834ohreghiuehv30uv09ue0vjrejvjreofjo23jr09203jkfdsnmocvj0wejfoi23ofnmoiefrbgpokpojsfo2","",$content);
	return $content;
}*/
?>