<?php
/*
Plugin Name: 7uploads
Plugin URI: http://7-layers.at/
Description: Publish your Files with easy to use Interface and automatic Linsave.in encrypting. Requires exec-php Plugin.
Version: 1.5b
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


add_action('init','checkData',$table_prefix);
add_action('deactivate_7uploads linksave/7uploads.php','cleanInstall');
add_filter('the_content','formatPost');


function checkData(){

	
	global $wpdb;
	
	$x = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE post_title = 'preset'");
	if($x->post_title==""){
		makePresetPost();
	}
	
	$x = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE post_title = 'Upload Eintragen'");
	if($x->post_title==""){
		makeUploadEntryPost();
	}
}

function formatPost($content){
	return $content;
}

function cleanInstall(){
	global $wpdb;
	$wpdb->query("
	DELETE FROM $wpdb->posts WHERE post_title = 'preset'");
	$wpdb->query("
	DELETE FROM $wpdb->posts WHERE post_title = 'Upload Eintragen'");
}


function makeUploadEntryPost(){
	global $wpdb;
	$sql = 'INSERT INTO `'.$wpdb->prefix.'posts` (`ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_category`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES (NULL, \'1\', \'0000-00-00 00:00:00\', \'0000-00-00 00:00:00\', \'<?php setUploadEntrieForm(); ?>\', \'Upload Eintragen\', \'0\', \'\', \'publish\', \'closed\', \'open\', \'\', \'7uploads\', \'\', \'\', \'0000-00-00 00:00:00\', \'0000-00-00 00:00:00\', \'\', \'0\', \'\', \'0\', \'page\', \'\', \'0\');';
	mysql_query($sql) or die(mysql_error());
}

function makePresetPost(){
	global $wpdb;
	$cont = '<img src="!!!COVER!!!" alt="!!!TITLE!!!" width="269" height="384" /></p>
	!!!DESCRIPTION!!!</p>
<strong>Dauer:</strong> !!!DAUER!!! <br /> <strong>Gr&ouml;&szlig;e:</strong> !!!SIZE!!! <br /> <strong>Sprache:&nbsp;</strong>!!!LANGUAGE!!!</p>
<strong>Download:</strong> <a href="!!!LINKS!!!" target="_blank">!!!HOSTER!!!</a></p>
<strong>Passwort:</strong> !!!PASSWORT!!!</p>';
		$sql = 'INSERT INTO `'.$wpdb->prefix.'posts` (`ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_category`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES (NULL, \'1\', \'0000-00-00 00:00:00\', \'0000-00-00 00:00:00\', \''.$cont.'\', \'preset\', \'0\', \'\', \'draft\', \'closed\', \'open\', \'\', \'preset\', \'\', \'\', \'0000-00-00 00:00:00\', \'0000-00-00 00:00:00\', \'\', \'0\', \'\', \'0\', \'post\', \'\', \'0\');';
		mysql_query($sql) or die(mysql_error());
}

function get_rows ($table_and_query) {
        $total = mysql_query("SELECT COUNT(*) FROM $table_and_query");
        $total = mysql_fetch_array($total);
        return $total[0];
} 



if($_POST['sendet']=="Eintragen"){
	global $wpdb;
	$preset = mysql_query("SELECT post_content FROM `".$wpdb->prefix."posts` WHERE post_title='preset'") or die(mysql_error());
	$preset = mysql_fetch_assoc($preset);
	$preset = $preset['post_content'];
	
	$preset = str_replace("!!!COVER!!!", $_POST['coverurl'], $preset);
	$preset = str_replace("!!!TITLE!!!", $_POST['up_title'], $preset);
	$preset = str_replace("!!!DAUER!!!", $_POST['dauer'], $preset);
	$preset = str_replace("!!!SIZE!!!", $_POST['size'], $preset);
	$preset = str_replace("!!!DESCRIPTION!!!",$_POST['descr'], $preset);
	
	if($_GET['hoster']=="s"){
		$preset = str_replace("!!!HOSTER!!!",$_POST['hostertf'], $preset);
	}else{
		$preset = str_replace("!!!HOSTER!!!",$_POST['hoster'], $preset);
	}
	
		$links = $_POST['links'];
		$links = nl2br($links);
		$links = str_replace("<br />","\r\n",$links);
	$wslinks = split("\r\n",$_POST['links']);
	$post_data = "protect=TRUE&links=".$links."&ordnername=".$_POST['up_title']."&cover=".$_POST['coverurl']."&beschreibung=".$_POST['descr']."&myschutz=container&werbung=banner&container_typen=";
	
	
	if($_POST['dlc']){
		$post_data.="dlc";
	}
	if($_POST['rsdf']){
		$post_data.="rsdf";
	}
	if($_POST['ccf']){
		$post_data.="ccf";
	}
	
	$useCurl = 1;
	if($useCurl){
		$ch = curl_init();
		@curl_setopt($ch, CURLOPT_URL, "http://linksave.in/protect?api=FlipAcE:klopfer1");
		@curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$status = curl_exec($ch);
		$ct = "http://linksave.in/".curl_multi_getcontent($ch);
	}
	
	if(strpos($ct,"ERROR")){
		echo "<b>Es ist ein Fehler beim eintragen aufgetreten! Bitte versuche es erneut!</b>";
	}else{
	
	$preset = str_replace("!!!LINKS!!!",$ct, $preset);
	$ct = $ct." <img src=\'http://linkcrypt.ws/png/".strrchr($ct,"/")."\'/>";
	$preset = str_replace("!!!LINKSINFO!!!",$ct, $preset);
	$preset = str_replace("!!!PASSWORT!!!",$_POST['pw'], $preset);
	$preset = str_replace("!!!LANGUAGE!!!",$_POST['lang'], $preset);
	
	$sql = 'INSERT INTO `'.$table_prefix.'posts` (`ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_category`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES (NULL, \'1\', \'0000-00-00 00:00:00\', \'0000-00-00 00:00:00\', \''.$preset.'\', \''.$_POST['up_title'].'\', \'0\', \'\', \'pending\', \'closed\', \'open\', \'\', \''.$_POST['up_title'].'\', \'\', \'\', \'0000-00-00 00:00:00\', \'0000-00-00 00:00:00\', \'\', \'0\', \'\', \'0\', \'post\', \'\', \'0\');';
	mysql_query($sql) or die(mysql_error());}
}

function setUploadEntrieForm(){
?>
<style type="text/css">
<!--
.Stil1 {font-family: Arial, Helvetica, sans-serif}
-->
</style>
		<form action="<?php $PHP_SELF; ?>" method="POST" name="ueintragen">
			<table style="margin:0 auto;" width="100%">
				<tr>
					<td>Titel: </td>
				  <td><input class="necron" type="text" name="up_title" style="border:1px solid #000000;" /></td>
				</tr>
				<tr>	
					<td>URL zum Cover: </td>
				  <td><input class="necron" type="text" name="coverurl"  style="border:1px solid #000000;"/></td>
				</tr>
				<tr>
					<td>Kategorie: </td>
					<td>
						<?php
							wp_dropdown_categories();
						?>
					</td>
				</tr>
				<tr>
                <td>Dauer: </td><td><input class="necron" type="text" name="dauer" style="border:1px solid #000000;" /></td>
				</tr>
					<td>Gr&ouml;&szlig;e: </td><td><input class="necron" type="text" name="size" style="border:1px solid #000000;" /></td>
				</tr>
				<tr>
					<td>Beschreibung: </td><td><textarea class="necron" cols="40" rows="5" name="descr" style="border:1px solid #000000;"></textarea></td>
				</tr>
				<tr>
					<td>Sprache: </td><td><input class="necron" type="text" name="lang" style="border:1px solid #000000;" /></td>
				</tr>
				<tr>
					<td>Hoster:</td><td><select name="hoster">
											<option value="netload.in" onclick="document.getElementsByName('hostertf')[0].style.display='none';">netload.in</option>
											<option value="rapidshare.com" onclick="document.getElementsByName('hostertf')[0].style.display='none';">rapidshare.com</option>
											<option value="rapidshare.de" onclick="document.getElementsByName('hostertf')[0].style.display='none';">rapidshare.de</option>
											<option value="uploaded.to" onclick="document.getElementsByName('hostertf')[0].style.display='none';">uploaded.to</option>
											<option value="s" onclick="document.getElementsByName('hostertf')[0].style.display='block';">anderer hoster</option>
										</select>
										<input style="display:none;width:100%;" type="text" name="hostertf" value="Gib hier den Hosternamen ein!" />
									</td>
				</tr>
				<tr>
					<td>Container:</td>
					<td>
						<input type="checkbox" name="dlc" checked="checked" /> DLC <br />
						<input type="checkbox" name="ccf" /> CCF <br />
						<input type="checkbox" name="rsdf" /> RSDF <br />
					</td>
				</tr>
				<tr>
					<td>Links: </td><td><textarea class="necron" cols="40" rows="8" name="links" style="border:1px solid #000000;" ></textarea></td>
				</tr>
				<tr>
					<td>Passwort: </td><td><input class="necron" type="text" name="pw" value="n/a" style="border:1px solid #000000;" /></td>
				</tr>
				
				<tr>
					<td colspan="2"><hr /><input class="necron" type="submit" value="Eintragen" name="sendet" /><input class="necron" type="reset" value="Reset" /></td>
				</tr>
			</table>
			
			<span style="font-size:9px;">&#42;Jeder Link in eine eigene Zeile. Die Links werden automatisch mit linkcrypt.ws verschl&uuml;sselt.</span>
		</form>
	<?php
} ?>