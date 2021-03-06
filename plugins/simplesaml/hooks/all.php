<?php

include dirname(__FILE__) . "/../include/simplesaml_functions.php";

function HookSimplesamlAllPreheaderoutput()
    {
    if(!(file_exists(dirname(__FILE__) . '/../lib/config/config.php')))
        {
        debug("simplesaml: plugin not configured.");
        return false;
        }
        
	global $simplesaml_site_block, $simplesaml_allow_public_shares, $simplesaml_allowedpaths;
    
	if(simplesaml_is_authenticated())
		{
		// Need to make sure we don't ask the user to type in a password, since we don't have it!
		global $delete_requires_password;
		$delete_requires_password=false;
		return true;
		}

	// If authenticated do nothing and return
	if (isset($_COOKIE["user"])) {return true;}

	// If not blocking site do nothing and return
	if (!$simplesaml_site_block){return true;}

	// Check for exclusions
	if ($simplesaml_allow_public_shares && getvalescaped("k","")!="") 
		{
		return true;
		}
	$url=str_replace("\\","/", $_SERVER["PHP_SELF"]);

	foreach ($simplesaml_allowedpaths as $simplesaml_allowedpath)
		{
		$samlexempturl=strpos($url,$simplesaml_allowedpath);
		if ($samlexempturl!==false && $samlexempturl==0)
			{
			return true;
			}
		}

	$as=simplesaml_authenticate();
	return true;
	}


function HookSimplesamlAllProvideusercredentials()
        {
        if(!(file_exists(dirname(__FILE__) . '/../lib/config/config.php')))
            {
            debug("simplesaml: plugin not configured.");
            return false;
            }
		global $pagename, $simplesaml_allow_standard_login, $simplesaml_prefer_standard_login, $baseurl, $path, $default_res_types, $scramble_key,
        $simplesaml_username_suffix, $simplesaml_username_attribute, $simplesaml_fullname_attribute, $simplesaml_email_attribute, $simplesaml_group_attribute,
        $simplesaml_fallback_group, $simplesaml_groupmap, $user_select_sql, $session_hash,$simplesaml_fullname_separator,$simplesaml_username_separator;
        // Use standard authentication if available
		if (isset($_COOKIE["user"])) {return true;}
		
		// Redirect to login page if not already authenticated and local login option is preferred
		if(!simplesaml_is_authenticated() && $simplesaml_allow_standard_login  && $simplesaml_prefer_standard_login && getval("usesso","")=="" )
			{
			?>
			<script>
			top.location.href="<?php echo $baseurl?>/login.php?url=<?php echo urlencode($path)?>";
			</script>	
			<?php
			exit;
			}
		
		if(!simplesaml_is_authenticated())
			{
			simplesaml_authenticate();
			}
		$attributes = simplesaml_getattributes();

	
		$usernamesuffix = $simplesaml_username_suffix;
        
        if(strpos($simplesaml_username_attribute,",")!==false) // Do we have to join two fields together?
		    {
		    $username_attributes=explode(",",$simplesaml_username_attribute);
		    $username ="";
		    foreach ($username_attributes as $username_attribute)
                {
                if($username!=""){$username.=$simplesaml_username_separator;}   
                $username.=  $attributes[$username_attribute][0]; 				
                }
		    $username= $username . $simplesaml_username_suffix;
		    }
		else
		    {
            if(!isset($attributes[$simplesaml_username_attribute][0]) )
                {
                $samlusername = simplesaml_getauthdata("saml:sp:NameID");
                debug("simplesaml: username attribute not found. Setting to default user id " . $samlusername);
                $username= $samlusername . $simplesaml_username_suffix;
                }
            else
                {
                $username=$attributes[$simplesaml_username_attribute][0] . $simplesaml_username_suffix;
                }
		    }
        
        if(strpos($simplesaml_fullname_attribute,",")!==false) // Do we have to join two fields together?
		    {
		    $fullname_attributes=explode(",",$simplesaml_fullname_attribute);		   
		    }
		else // Previous version used semi-colons
		    { 
	            $fullname_attributes=explode(";",$simplesaml_fullname_attribute);
		    }
        
        $displayname ="";
        foreach ($fullname_attributes as $fullname_attribute)
            {
            if($displayname!=""){$displayname.=$simplesaml_fullname_separator;}
            debug("simplesaml: constructing fullname from attribute " . $fullname_attribute . ": "  . $attributes[$fullname_attribute][0]);
            $displayname.=  $attributes[$fullname_attribute][0]; 				
            }
        
		$displayname=trim($displayname);
		debug("simplesaml: constructed fullname : "  . $displayname);

		if(isset($attributes[$simplesaml_email_attribute][0])){$email=$attributes[$simplesaml_email_attribute][0];}
		$groups=$attributes[$simplesaml_group_attribute];

		$password_hash= md5("RSSAML" . $scramble_key . $username);

		$userid = sql_value("select ref value from user where username='" . $username . "'",0);

		debug ("SimpleSAML - got user details username=" . $username . ", email: " . (isset($email)?$email:""));

		// figure out group
		$group = $simplesaml_fallback_group;
		$currentpriority=0;
		if (count($simplesaml_groupmap)>0){
			for ($i = 0; $i < count($simplesaml_groupmap); $i++)
				{
				for($g = 0; $g < count($groups); $g++)
					{
					if (($groups[$g] == $simplesaml_groupmap[$i]['samlgroup']) && is_numeric($simplesaml_groupmap[$i]['rsgroup']) && $simplesaml_groupmap[$i]['priority']>$currentpriority)
						{
						$group = $simplesaml_groupmap[$i]['rsgroup'];
						$currentpriority=$simplesaml_groupmap[$i]['priority'];
						}
					}
				}
			}

		if ($userid > 0)
			{
			if(!isset($email) || $email==""){$email=sql_value("select email value from user where ref='$userid'","");} // Allows accounts without an email address to have one set by the admin without it getting overwritten
			// user exists, so update info
			global $simplesaml_update_group;
			if($simplesaml_update_group)
				{
				sql_query("update user set password = '$password_hash', usergroup = '$group', fullname='$displayname', email='$email' where ref = '$userid'");
				}
			else
				{
				sql_query("update user set password = '$password_hash', fullname='$displayname',  email='$email' where ref = '$userid'");
				}

			$user_select_sql="and u.username='$username'";
			return true;
			} 
		else
			{
			// user authenticated, but does not exist, so create if necessary
			// Create the user
			$userref=new_user($username);
			 if (!$userref) { echo "returning false!";  return false;} // this shouldn't ever happen

			sql_query("update user set password='$password_hash', fullname='$displayname', email='$email',usergroup='$group',comments='Auto created by SimpleSAML.' where ref='$userref'");
			$user_select_sql="and u.username='$username'";
            
            # Generate a new session hash.
            $session_hash=generate_session_hash($password_hash);
            
            # Set user cookie, setting secure only flag if a HTTPS site, and also setting the HTTPOnly flag so this cookie cannot be probed by scripts (mitigating potential XSS vuln.)
            rs_setcookie("user", $session_hash, intval($simplesaml_login_expiry), "", "", substr($baseurl,0,5)=="https", true);
            return true;
			}
		return false;
        }

function HookSimplesamlLoginLoginformlink()
        {
        if(!(file_exists(dirname(__FILE__) . '/../lib/config/config.php')))
            {
            debug("simplesaml: plugin not configured.");
            return false;
            }
		// Add a link to login.php, as this page may still be seen if $simplesaml_allow_standard_login is set to true
		global $baseurl, $lang;
        ?>
		<br/><a href="<?php echo $baseurl . "/?usesso=true\">&gt; " . $lang["simplesaml_use_sso"];?></a>
		<?php
        }



function HookSimplesamlLoginPostlogout()
        {
        simplesaml_signout();
        }

function HookSimplesamlLoginPostlogout2()
        {
		global $baseurl;
		if (getval("logout","")!="")
			{
			simplesaml_signout();
			header( 'Location: '.$baseurl ) ;
			}
        }



function HookSimplesamlAllCheckuserloggedin()
	{
	return simplesaml_is_authenticated();
	}





	
	


