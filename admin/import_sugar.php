<?php
require_once dirname(__FILE__).'/accesscheck.php';
if (!ALLOW_IMPORT) {
  print '<p>'.$GLOBALS['I18N']->get('import is not available').'</p>';
  return;
}

# import from a different PHPlist installation

if ($require_login && !isSuperUser()) {
  $access = accessLevel("import4");
  if ($access == "owner")
    $subselect = " where owner = ".$_SESSION["logindetails"]["id"];
  elseif ($access == "all")
    $subselect = "";
  elseif ($access == "none")
    $subselect = " where id = 0";
}

function connectLocal() {
  $GLOBALS["database_connection"] = Sql_Connect(
    $GLOBALS["database_host"],
    $GLOBALS["database_user"],
    $GLOBALS["database_password"],
    $GLOBALS["database_name"]);
   return $GLOBALS["database_connection"];
}
function connectRemote() {
  $GLOBALS["database_connection"] = Sql_Connect($_POST["remote_host"],
  $_POST["remote_user"],
  $_POST["remote_password"],
  $_POST["remote_database"]);
  return $GLOBALS["database_connection"];
}

$result = Sql_query("SELECT id,name FROM ".$tables["list"]." $subselect ORDER BY listorder");
while ($row = Sql_fetch_array($result)) {
  $available_lists[$row["id"]] = $row["name"];
  $some = 1;
}
if (!$some)
 # @@@@ not sure about this one:
 echo $GLOBALS['I18N']->get('No lists available').', '.PageLink2("editlist",$GLOBALS['I18N']->get('add_list'));
#foreach ($_POST as $key => $val) {
#  print "$key => $val<br/>";
#}

if (!$_POST["remote_host"] ||
  !$_POST["remote_user"] ||
  !$_POST["remote_password"] || !$_POST["remote_database"]) {
  printf( '
  <p>'.$GLOBALS['I18N']->get('remote_server').'</p>
  <form method=post>
  <table>
  <tr><td>'.$GLOBALS['I18N']->get('server').'</td><td><input type=text name="remote_host" value="%s" size=30></td></tr>
  <tr><td>'.$GLOBALS['I18N']->get('user').'</td><td><input type=text name="remote_user" value="%s" size=30></td></tr>
  <tr><td>'.$GLOBALS['I18N']->get('passwd').'</td><td><input type=text name="remote_password" value="%s" size=30></td></tr>
  <tr><td>'.$GLOBALS['I18N']->get('database').'</td><td><input type=text name="remote_database" value="%s" size=30></td></tr>
  ',$_POST["remote_host"],$_POST["remote_user"],$_POST["remote_password"],
  $_POST["remote_database"]);
  $c = 0;
  print '<tr><td colspan=2>';
  if (sizeof($available_lists) > 1)
    print $GLOBALS['I18N']->get('select_lists').'<br/>';
  print '<ul>';
  foreach ($available_lists as $index => $name) {
    printf('<li><input type=checkbox name="lists[%d]" value="%d" %s>%s</li>',
      $c,$index,is_array($_POST["lists"]) && in_array($index,array_values($_POST["lists"]))?"checked":"",$name);
    $c++;
  }
  printf('
  <li><input type=checkbox name="copyremotelists" value="yes" %s>'.$GLOBALS['I18N']->get('copy_lists').'</li>
  </ul></td></tr>
<tr><td>'.$GLOBALS['I18N']->get('users_as_html').'</td><td><input type="checkbox" name="markhtml" value="yes" %s></td></tr>
<tr><td colspan=2>'.$GLOBALS['I18N']->get('info_overwrite_existing').'</td></tr>
<tr><td>'.$GLOBALS['I18N']->get('overwrite_existing').'</td><td><input type="checkbox" name="overwrite" value="yes" %s></td></tr>
  <tr><td colspan=2><input type=submit value="'.$GLOBALS['I18N']->get('continue').'"></td></tr>
  </table></form>
  ',$_POST["copyremotelists"] == "yes"?"checked":"",$_POST["markhtml"] == "yes"?"checked":"",$_POST["overwrite"] == "yes"?"checked":""
  );

} else if (!$_POST["prospect_list_id"] ) {
  printf( '
  <p>'.$GLOBALS['I18N']->get('remote_server').'</p>
  <form method=post>
  <table>
  <tr><td>'.$GLOBALS['I18N']->get('server').'</td><td><input type=text name="remote_host" value="%s" size=30></td></tr>
  <tr><td>'.$GLOBALS['I18N']->get('user').'</td><td><input type=text name="remote_user" value="%s" size=30></td></tr>
  <tr><td>'.$GLOBALS['I18N']->get('passwd').'</td><td><input type=text name="remote_password" value="%s" size=30></td></tr>
  <tr><td>'.$GLOBALS['I18N']->get('database').'</td><td><input type=text name="remote_database" value="%s" size=30></td></tr>
  ',$_POST["remote_host"],
  $_POST["remote_user"],
  $_POST["remote_password"],
  $_POST["remote_database"]);
  $c = 0;
  print '<tr><td colspan=2>';
#   if (sizeof($available_lists) > 1)
#     print $GLOBALS['I18N']->get('select_lists').'<br/>';
#   print '<ul>';
#   foreach ($available_lists as $index => $name) {
#     printf('<li><input type=checkbox name="lists[%d]" value="%d" %s>%s</li>',
#      $c,$index,is_array($_POST["lists"]) && in_array($index,array_values($_POST["lists"]))?"checked":"",$name);
#    $c++;
#  }

  connectRemote();
  $r = Sql_Query("select id, name from prospect_lists where deleted = 0");
  while ($p = Sql_Fetch_Array($r)) {
    printf("<li><input type='radio' name='prospect_list_id' value='%s'>%s</li>",$p[0], $p[1]);
  }

  printf('
  <li><input type=checkbox name="copyremotelists" value="yes" %s>'.$GLOBALS['I18N']->get('copy_lists').'</li>
  </ul></td></tr>
<tr><td>'.$GLOBALS['I18N']->get('users_as_html').'</td><td><input type="checkbox" name="markhtml" value="yes" %s></td></tr>
<tr><td colspan=2>'.$GLOBALS['I18N']->get('info_overwrite_existing').'</td></tr>
<tr><td>'.$GLOBALS['I18N']->get('overwrite_existing').'</td><td><input type="checkbox" name="overwrite" value="yes" %s></td></tr>
  <tr><td colspan=2><input type=submit value="'.$GLOBALS['I18N']->get('continue').'"></td></tr>
  </table></form>
  ',$_POST["copyremotelists"] == "yes"?"checked":"",$_POST["markhtml"] == "yes"?"checked":"",$_POST["overwrite"] == "yes"?"checked":""
  );

/* = = */
} else {
  set_time_limit(600);
  ob_end_flush();
  include_once("structure.php");
  print $GLOBALS['I18N']->get('connecting_remote')."<br/>";
  flush();
  
  $remote = connectRemote();
  if (!$remote) {
    Fatal_Error($GLOBALS['I18N']->get('cant_connect'));
    return;
  }
  print $GLOBALS['I18N']->get('getting_data').' '.$_POST["remote_database"]."@".$_POST["remote_host"]."<br/>";

  $prospect_list_id = $_POST['prospect_list_id'];
  $select = <<<SQL
SELECT
	prospect_lists_prospects.related_id foreignkey,
	email_addresses.email_address       email 
SQL;
  $from = <<<SQL
FROM
	email_addresses
INNER JOIN
	email_addr_bean_rel
ON
	email_addr_bean_rel.email_address_id = email_addresses.id
INNER JOIN
	prospect_lists_prospects
ON
	prospect_lists_prospects.related_type = email_addr_bean_rel.bean_module
AND
	prospect_lists_prospects.related_id = email_addr_bean_rel.bean_id
WHERE
	email_addr_bean_rel.primary_address = 1
AND
	email_addresses.opt_out = 0
AND
	email_addresses.deleted = 0
AND
	email_addr_bean_rel.deleted = 0
AND
	prospect_lists_prospects.deleted = 0	
AND
	prospect_lists.id = '$prospect_list_id'
SQL;
  $sql = $select.$from;

  connectRemote();
  $usercnt = Sql_Fetch_Row_Query("select count(*) $from");
  print $GLOBALS['I18N']->get('remote_has')." $usercnt[0] ".$GLOBALS['I18N']->get('users')."<br/>";
  if (!$usercnt[0]) {
   Fatal_Error($GLOBALS['I18N']->get('no_users_to_copy').'<br/>'.$sql);
    return;
  }
  $totalusers = $usercnt[0];

  flush();

  print '<h1>'.$GLOBALS['I18N']->get('copying_users').'</h1>';
  # copy the users
  $usercnt = 0;
  $existcnt = 0;
  $newcnt = 0;
  while ($usercnt < $totalusers) {
    set_time_limit(60);
    connectRemote();
    $req = Sql_Query("$sql limit $usercnt,1");
    $user = Sql_Fetch_Array($req);
    $usercnt++;
    $new = 0;
    if ($usercnt % 20 == 0) {
      print "$usercnt / $totalusers<br/>";
      flush();
    }
    connectLocal();
    $query = "";
    $exists = Sql_Fetch_Row_Query(sprintf('select id from %s where email = "%s"',$tables["user"],$user["email"]));
    if ($exists[0]) {
      $existcnt++;
  #    print $user["email"] .$GLOBALS['I18N']->get('exists_locally')." ..";
      if ($_POST["overwrite"]) {
  #      print " .. ".$GLOBALS['I18N']->get('overwrite_local')."<br/>";
        $query = "replace into ".$tables["user"] . " set id = ".$exists[0].", ";
      } else {
  #      print " .. ".$GLOBALS['I18N']->get('keep_local')."<br/>";
      }
      $userid = $exists[0];
    } else {
      $newcnt++;
      $new = 1;
  #    print $user["email"] .$GLOBALS['I18N']->get('new_user')."<br/>";
      $query = "insert into ".$tables["user"]. " set ";
    }
    if ($query) {
      foreach ($DBstruct["user"] as $colname => $colspec) {
        // see structure.php
        if ($colname != "id" && 
            $colname != "index_1" && 
            $colname != "index_2" && 
            $colname != "index_3" && 
            $colname != "index_4" && 
            $colname != "unique" && 
            $colname != "unique_1" && 
            $colname != "primary key") {
          $query .= sprintf('%s = "%s",',$colname,addslashes($user[$colname]));
         }
       }
      $query = substr($query,0,-1);
      #print $query . "<br/>";
      Sql_Query("$query");
      $userid = Sql_Insert_id();
    }
    if ($userid && $_POST["markhtml"]) {
      Sql_Query("update {$tables["user"]} set htmlemail = 1 where id = $userid");
    }

  }
  print "$totalusers / $totalusers<br/>";
  flush();
 # @@@@ Not sure about this one:
   printf('%s %d %s %s %d %s<br/>',$GLOBALS['I18N']->get('Done'),$newcnt,
   $GLOBALS['I18N']->get('new users'),
   $GLOBALS['I18N']->get('and'),
   $existcnt,$GLOBALS['I18N']->get('existing users'));
}
?>


