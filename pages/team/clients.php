<?php
/**
 * User management start page (part of team center)
 * 
 * @Package ResourceSpace
 * @Subpackage Pages_Team
 */
include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php";if (!checkperm("u")) {exit ("Permission denied.");}
include_once "../../include/collections_functions.php";

$offset=getvalescaped("offset",0);
$find=getvalescaped("find","");
$order_by=getvalescaped("order_by","u.username");
$group=19;  // For Clients Specifically

# Pager
$per_page=getvalescaped("per_page_list",$default_perpage_list);rs_setcookie('per_page_list', $per_page);


if (array_key_exists("find",$_POST)) {$offset=0;} # reset page counter when posting

if (getval("newuser","")!="" && !hook("replace_create_user_save"))
	{
	$new=new_user(getvalescaped("newuser",""));
	if ($new===false)
		{
		$error=$lang["useralreadyexists"];
		}
	else
		{
		hook("afterusercreated");
		redirect($baseurl_short."pages/team/team_user_edit.php?ref=" . $new);
		}
	}

function show_team_user_filter_search(){
	global $baseurl_short,$lang,$group,$find;
	$groups=get_usergroups(true);
	?>
	<div class="BasicsBox">
		<form method="post" action="<?php echo $baseurl_short?>pages/team/team_user.php">
			<div class="Question">  
				<label for="group"><?php echo $lang["group"]; ?></label>
				<?php if (!hook('replaceusergroups')) { ?>
					<div class="tickset">
						<div class="Inline">
							<select name="group" id="group" onChange="this.form.submit();">
								<option value="0"<?php if ($group == 0) { echo " selected"; } ?>><?php echo $lang["all"]; ?></option>
								<?php
								for($n=0;$n<count($groups);$n++){
									?>
									<option value="<?php echo $groups[$n]["ref"]; ?>"<?php if ($group == $groups[$n]["ref"]) { echo " selected"; } ?>><?php echo $groups[$n]["name"]; ?></option>
									<?php
								}
								?>
							</select>
						</div>
					</div>
				<?php } ?>
				<div class="clearerleft"> </div>
			</div>
		</form>
	</div>

	<div class="BasicsBox">
		<form method="post" action="<?php echo $baseurl_short?>pages/team/team_user.php">
			<div class="Question">
				<label for="find"><?php echo $lang["searchusers"]?></label>
				<div class="tickset">
				 <div class="Inline"><input type=text name="find" id="find" value="<?php echo $find?>" maxlength="100" class="shrtwidth" /></div>
				 <div class="Inline"><input name="Submit" type="submit" value="&nbsp;&nbsp;<?php echo $lang["searchbutton"]?>&nbsp;&nbsp;" /></div>
				</div>
				<div class="clearerleft"> </div>
			</div>
		</form>
	</div>
	<?php
}

include "../../include/header.php";
?>
<div class="BasicsBox"> 
	<?php 
	$backlink=getvalescaped("backlink","");
	if($backlink!="")
		{
?>	<p>
		<a href='<?php echo rawurldecode($backlink); ?>'><?php echo LINK_CARET_BACK ?><?php echo $lang['back']; ?></a>
	</p>
<?php
		}
?><h1><?php echo $lang["manageusers"]?></h1>
  <p><?php echo text("introtext")?></p>

<?php if (isset($error)) { ?><div class="FormError">!! <?php echo $error?> !!</div><?php } ?>

<?php if($team_user_filter_top){show_team_user_filter_search();}?>

<?php 
hook('modifyusersearch');

# Fetch rows
$users=get_users($group,$find,$order_by,true,$offset+$per_page);
$groups=get_usergroups(true);
$results=count($users);
$totalpages=ceil($results/$per_page);
$curpage=floor($offset/$per_page)+1;

$url=$baseurl_short."pages/team/team_user.php?group=" . $group . "&order_by=" . $order_by . "&find=" . urlencode($find);
$jumpcount=1;

# Create an a-z index
$atoz="<div class=\"InpageNavLeftBlock\">";
if ($find=="") {$atoz.="<span class='Selected'>";}
$atoz.="<a href=\"" . $baseurl . "/pages/team/team_user.php?order_by=u.username&group=" . $group . "&find=\" onClick=\"return CentralSpaceLoad(this);\">" . $lang["viewall"] . "</a>";
if ($find=="") {$atoz.="</span>";}
$atoz.="&nbsp;&nbsp;";
for ($n=ord("A");$n<=ord("Z");$n++)
	{
	if ($find==chr($n)) {$atoz.="<span class='Selected'>";}
	$atoz.="<a href=\"" . $baseurl . "/pages/team/team_user.php?order_by=u.username&group=" . $group . "&find=" . chr($n) . "\" onClick=\"return CentralSpaceLoad(this);\">&nbsp;" . chr($n) . "&nbsp;</a> ";
	if ($find==chr($n)) {$atoz.="</span>";}
	$atoz.=" ";
	}
$atoz.="</div>";

?>

<div class="TopInpageNav"><div class="TopInpageNavLeft"><?php echo $atoz?>	<div class="InpageNavLeftBlock"><?php echo $lang["resultsdisplay"]?>:
	<?php 
	for($n=0;$n<count($list_display_array);$n++){?>
	<?php if ($per_page==$list_display_array[$n]){?><span class="Selected"><?php echo $list_display_array[$n]?></span><?php } else { ?><a href="<?php echo $url; ?>&per_page_list=<?php echo $list_display_array[$n]?>" onClick="return CentralSpaceLoad(this);"><?php echo $list_display_array[$n]?></a><?php } ?>&nbsp;|
	<?php } ?>
	<?php if ($per_page==99999){?><span class="Selected"><?php echo $lang["all"]?></span><?php } else { ?><a href="<?php echo $url; ?>&per_page_list=99999" onClick="return CentralSpaceLoad(this);"><?php echo $lang["all"]?></a><?php } ?>
	</div></div> <?php pager(false); ?><div class="clearerleft"></div></div>

<div class="Listview">
<?php if(!hook('overrideuserlist')):

function addColumnHeader($orderName, $labelKey)
{
	global $baseurl, $group, $order_by, $find, $lang;

	if ($order_by == $orderName)
		$image = '<span class="ASC"></span>';
	else if ($order_by == $orderName . ' desc')
		$image = '<span class="DESC"></span>';
	else
		$image = '';

	?><td><a href="<?php echo $baseurl ?>/pages/team/team_user.php?offset=0&group=<?php
			echo $group; ?>&order_by=<?php echo $orderName . ($order_by==$orderName ? '+desc' : '');
			?>&find=<?php echo urlencode($find)?>" onClick="return CentralSpaceLoad(this);"><?php
			echo $lang[$labelKey] . $image ?></a></td>
	<?php
}

?>
<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
<tr class="ListviewTitleStyle">
<?php
	# addColumnHeader('u.username', 'username');
	if (!hook("replacefullnameheader"))
		addColumnHeader('u.fullname', 'Client');
	if (!hook("replaceemailheader"))
		addColumnHeader('email', 'email');
	addColumnHeader('type', 'type');
	hook("additional_user_column_header");
?>
<td><div class="ListTools"><?php echo $lang["tools"]?></div></td>
</tr>

<?php
for ($n=$offset;(($n<count($users)) && ($n<($offset+$per_page)));$n++)
	{
	?>
	<tr>
	<?php if (!hook("replacefullnamerow")){?>
	<td><?php echo htmlspecialchars($users[$n]["fullname"])?></td>
	<?php } ?>
	<?php if (!hook("replaceemailrow")){?>
	<td><?php echo htmlentities($users[$n]["email"])?></td>
	<?php } ?>
	<td><?php echo $users[$n]["comments"]?></td>
	<?php hook("additional_user_column");?>
	<td><?php if (($usergroup==3) || ($users[$n]["usergroup"]!=3)) { ?><div class="ListTools">
	<a href="<?php echo $baseurl ?>/pages/admin/admin_system_log.php?actasuser=<?php echo $users[$n]["ref"]?>&backurl=<?php echo urlencode($url . "&offset=" . $offset)?>" onClick="return CentralSpaceLoad(this,true);"><?php echo LINK_CARET ?><?php echo $lang["log"]?></a>
	&nbsp;
	<a href="<?php echo $baseurl ?>/pages/team/team_user_edit.php?ref=<?php echo $users[$n]["ref"]?>&backurl=<?php echo urlencode($url . "&offset=" . $offset)?>" onClick="return CentralSpaceLoad(this,true);"><?php echo LINK_CARET ?><?php echo $lang["action-edit"]?></a>
	<?php hook("usertool")?>
	</div><?php } ?>
	</td>
	</tr>
	<?php
	}
?>

</table>
<?php endif; // hook overrideuserlist ?>
</div>
<div class="BottomInpageNav">
<div class="BottomInpageNavLeft">
<strong><?php echo $lang["total"] . ": " . count($users); ?> </strong><?php echo $lang["users"]; ?>
</div>

<?php pager(false); ?></div>
</div>




<?php if(!$team_user_filter_top){show_team_user_filter_search();}?>

<?php
if(!hook("replace_create_user"))
    {
    ?>
    <div class="BasicsBox">
        <form method="post" action="<?php echo $baseurl_short?>pages/team/team_user.php">
    		<div class="Question">
    			<label for="newuser"><?php echo $lang["createuserwithusername"]?></label>
    			<div class="tickset">
    			 <div class="Inline"><input type=text name="newuser" id="newuser" maxlength="100" class="shrtwidth" /></div>
    			 <div class="Inline"><input name="Submit" type="submit" value="&nbsp;&nbsp;<?php echo $lang["create"]?>&nbsp;&nbsp;" /></div>
    			</div>
    			<div class="clearerleft"> </div>
    		</div>
    	</form>
    </div>
    <?php
    }

    hook('render_options_to_create_users');
    
if ($user_purge)
	{
	?>
	<div class="BasicsBox">
	<div class="Question"><label><?php echo $lang["purgeusers"]?></label>
	<div class="Fixed"><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl ?>/pages/team/team_user_purge.php"><?php echo LINK_CARET ?><?php echo $lang["purgeusers"]?></a></div>
	<div class="clearerleft"> </div></div>
	</div>
	<?php
	}
?>

<?php if (!hook("replaceusersonline")) { ?>
<div class="BasicsBox">
<div class="Question"><label><?php echo $lang["usersonline"]?></label>
<div class="Fixed">
<?php
$active=get_active_users();
for ($n=0;$n<count($active);$n++) {if($n>0) {echo", ";}echo "<b>" . $active[$n]["username"] . "</b> (" . $active[$n]["t"] . ")";}
?>
</div><div class="clearerleft"> </div></div></div>	
<?php } // end hook("replaceusersonline")
?>

<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<script>
    $( "#finds" ).autocomplete({
		 width:448,
		 delimiter: /(,|;)\s*/,
         lookup: <?php echo "'autocomplete(#find)'" ?>
    });
</script>

<?php 

function autocomplete($inputName){
	$searchTerm = $_GET[$inputName];
	$query = $db->query("SELECT * FROM user WHERE fullname LIKE '%".$searchTerm."%' ORDER BY fullname ASC");
    while ($row = $query->fetch_assoc()) {
        $data[] = $row['username'];
    }
    //return json data
    echo json_encode($data);
}
   /* //get search term
    $searchTerm = $_GET['finds'];
    
    //get matched data from skills table
    $query = $db->query("SELECT * FROM user WHERE fullname LIKE '%".$searchTerm."%' ORDER BY fullname ASC");
    while ($row = $query->fetch_assoc()) {
        $data[] = $row['username'];
    }
    
    //return json data
    echo json_encode($data);
    */
include "../../include/footer.php";
?>
