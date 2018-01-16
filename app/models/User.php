<?php
/**************************************************************************************************

Copyright 2009 The Trustees of Indiana University

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in
compliance with the License. You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License
is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
implied. See the License for the specific language governing permissions and limitations under the
License.

**************************************************************************************************/

//lookup person information as well as role information
class User
{
    public function __construct($dn)
    {
        $this->roles = config()->auth_metrics[authtype::$auth_guest];
        $this->person_id = null;
        $this->person_fullname = "Guest";
        $this->person_email = "";
        $this->person_phone = "";
        //$this->timezone = "UTC";
        $this->dn = $_SESSION["email"];//$dn;
        $this->disable = true;

        $this->guest = true;
        if($_SESSION["email"] !== null) {
            $this->lookupUserID($_SESSION["email"]);
	    
            if($this->person_id !== null) {
	     
                $this->guest = false;
                $this->lookupRoles($this->person_id);
            }
        }
    }

    private function lookupUserID($dn)
    {
        //make sure user DN exists and active

      $sql_sso = "select * from contact_authorization_type where email = \"$dn\" or email1 = \"$dn\" or email2 = \"$dn\" or email3 = \"$dn\"  ";
     
      //   print  $sql_sso ;
      $row_sso = db("sso")->fetchRow($sql_sso);
   
      if($row_sso) {
	//  $insert_sso = "insert into contact_authorization_type (given_name, family_name, email, idp, idp_name,aud, iat, name, iss, nonce, oidc, openid, sub, access_token, access_token_expires, remote_user,authorization_type_id, created) values('".$_SESSION["given_name"]."', '".$_SESSION["family_name"]."','".$_SESSION["email"]."', '".$_SESSION["idp"]."', '".$_SESSION["idp_name"]."','".$_SESSION["aud"]."', '".$_SESSION["iat"]."', '".$_SESSION["name"]."', '".$_SESSION["iss"]."', '".$_SESSION["nonce"]."', '".$_SESSION["oidc"]."', '".$_SESSION["openid"]."', '".$_SESSION["sub"]."', '".$_SESSION["access_token"]."', '".$_SESSION["access_token_expires"]."', '".$_SESSION["remote_user"]."',1, now())";
	$update_sso = "update  contact_authorization_type set given_name='".$_SESSION["given_name"]."', family_name ='".$_SESSION["family_name"]."', idp= '".$_SESSION["idp"]."', idp_name= '".$_SESSION["idp_name"]."',aud='".$_SESSION["aud"]."', name= '".$_SESSION["name"]."',  oidc= '".$_SESSION["oidc"]."', openid= '".$_SESSION["openid"]."', sub='".$_SESSION["sub"]."', access_token='".$_SESSION["access_token"]."', remote_user='".$_SESSION["remote_user"]."', last_login=now()  where email = \"$dn\" ";
	//print "<br>".$update_sso;
	db("sso")->exec($update_sso);
	$sso_idp = $row_sso->idp;
	$this->dn_id = $row_sso->id;
	$this->contact_id = $row_sso->contact_id;
	$this->contact_name = "".$row_sso->given_name." ". $row_sso->family_name."";
	//print "".$row_sso->given_name." ". $row_sso->family_name."<br>";                                                                                                                    
	$this->contact_email = $row_sso->email;
	$this->disable = ($row_sso->disable || $row_sso->dn_disable);
      }else{
	// check if the email is in the contacts database 
	$sql_select_contact = "select * from contact where primary_email = '".$dn."' or  secondary_email = '".$dn."'";
	$row_select_contact = db("oim")->fetchRow($sql_select_contact);
	if($row_select_contact) {
	  $contact_id = $row_select_contact->id;
	    $insert_sso = "insert into contact_authorization_type (given_name, family_name, email, idp, idp_name,aud, iat, name, iss, nonce, oidc, openid, sub, access_token, access_token_expires, remote_user,authorization_type_id, created, contact_id) values('".$_SESSION["given_name"]."', '".$_SESSION["family_name"]."','".$_SESSION["email"]."', '".$_SESSION["idp"]."', '".$_SESSION["idp_name"]."','".$_SESSION["aud"]."', '".$_SESSION["iat"]."', '".$_SESSION["name"]."', '".$_SESSION["iss"]."', '".$_SESSION["nonce"]."', '".$_SESSION["oidc"]."', '".$_SESSION["openid"]."', '".$_SESSION["sub"]."', '".$_SESSION["access_token"]."', '".$_SESSION["access_token_expires"]."', '".$_SESSION["remote_user"]."',1, now(),'".$contact_id."')";                                                                                   
 
	    $insert_sso_contact = db("sso")->fetchRow($insert_sso);
	      
	    $this->dn_id = $insert_sso_contact->getLastId();
	    $contact_authorization_type_id = $insert_sso_contact->getLastId();
	    $this->contact_name = $_SESSION["given_name"]."' '".$_SESSION["family_name"];
	    $this->contact_email = $_SESSION["email"];
	    // add actions
	    // get last DN
	    $dn_sql = "select dn.id as dn_id from dn left join dn_authorization_type on dn.id=dn_authorization_type.dn_id where contact_id=".$contact_id." order by dn.id desc limit 1";
	    $select_dn_rec = db("oim")->fetchRow($dn_sql);
	    $dn_id= $select_dn_rec->dn_id;
	    if($dn_id!=""){
	      $sql = "select * from dn_authorization_type where dn_id=".$dn_id."";
	      foreach(db("oim")->fetchAll($sql) as $row) {
		$authorization_type_id  = $row->authorization_type_id;
		db("sso")->exec("delete from contact_authorization_type_index where contact_authorization_type_id=".$contact_authorization_type_id." and au\
thorization_type_id=".$authorization_type_id."");
		$insert_action = "INSERT INTO contact_authorization_type_index (contact_authorization_type_id, authorization_type_id) VALUES (".$contact_authorization_type_id.",".$authorization_type_id.")";
		db("sso")->exec($sql);
	      }
	    }
	}else{

	  if($_SERVER["SSL_CLIENT_S_DN"]!=""){
	    $sql_select_dn = "select * from dn where dn_string='".$_SERVER["SSL_CLIENT_S_DN"]."'";
	    $row_select_dn = db("oim")->fetchRow($sql_select_dn);
	    if($row_select_dn) {
	      $dn_contact_id = $row_select_dn->contact_id;
	      $insert_sso_dn = "insert into contact_authorization_type (given_name, family_name, email, idp, idp_name,aud, iat, name, iss, nonce, oidc, openid, sub, access_token, access_token_expires, remote_user,authorization_type_id, created, contact_id) values('".$_SESSION["given_name"]."', '".$_SESSION["family_name"]."','".$_SESSION["email"]."', '".$_SESSION["idp"]."', '".$_SESSION["idp_name"]."','".$_SESSION["aud"]."', '".$_SESSION["iat"]."', '".$_SESSION["name"]."', '".$_SESSION["iss"]."', '".$_SESSION["nonce"]."', '".$_SESSION["oidc"]."', '".$_SESSION["openid"]."', '".$_SESSION["sub"]."', '".$_SESSION["access_token"]."', '".$_SESSION["access_token_expires"]."', '".$_SESSION["remote_user"]."',1, now(),'".$dn_contact_id."')";
	      $dn_id=$row_select_dn->id;
	      if($dn_id!=""){
		$sql = "select * from dn_authorization_type where dn_id=".$dn_id."";
		foreach(db("oim")->fetchAll($sql) as $row) {
		  $authorization_type_id  = $row->authorization_type_id;
		  db("sso")->exec("delete from contact_authorization_type_index where contact_authorization_type_id=".$contact_authorization_type_id." and authorization_type_id=".$authorization_type_id."");
		  $insert_action = "INSERT INTO contact_authorization_type_index (contact_authorization_type_id, authorization_type_id) VALUES (".$contact_authorization_type_id.",".$authorization_type_id.")";
		  db("sso")->exec($sql);
		    
		}
	      }
	    }
	  }else{ // there is no DN
	    $insert_sso_dn_last = "insert into contact_authorization_type (given_name, family_name, email, idp, idp_name,aud, iat, name, iss, nonce, oidc, openid, sub, access_token, access_token_expires, remote_user,authorization_type_id, created) values('".$_SESSION["given_name"]."', '".$_SESSION["family_name"]."','".$_SESSION["email"]."', '".$_SESSION["idp"]."', '".$_SESSION["idp_name"]."','".$_SESSION["aud"]."', '".$_SESSION["iat"]."', '".$_SESSION["name"]."', '".$_SESSION["iss"]."', '".$_SESSION["nonce"]."', '".$_SESSION["oidc"]."', '".$_SESSION["openid"]."', '".$_SESSION["sub"]."', '".$_SESSION["access_token"]."', '".$_SESSION["access_token_expires"]."', '".$_SESSION["remote_user"]."',1, now())";
	    $insert_sso_dn=db("sso")->fetchRow($insert_sso_dn_last);
	    $last_inserted_dnid=$insert_sso_dn->getLastId();
	    $insert_actions ="insert into contact_authorization_type_index (contact_authorization_type,authorization_type_id) values (".$last_inserted_dnid.",1)";
	    $insert_sso_index = db("sso")->fetchRow($insert_actions);
	  }
	}
      }
      //  slog("DN: $dn doesn't exist in oim");
        
      $sql_sso2 = "select * from contact_authorization_type where email = \"$dn\" or email1 = \"$dn\" or email2 = \"$dn\" or email3 = \"$dn\"  ";
      $row_sso2 = db("sso")->fetchRow($sql_sso2);

      if($row_sso2) {
                                                                                                                                                                
	$this->person_id = $row_sso2->contact_id;
        	
	$this->person_name = $row_sso2->given_name." ".$row_sso2->family_name;                                                                                                 
                                       
	$this->person_email = $row_sso2->email;                                                                                                                              
	//$this->person_phone = $row->primary_phone;                                                                                                                              
	//$this->timezone = $row->timezone;                                                                                                                                       
	$this->disable = $row_sso2->disable;                                                                           
                                        
      }     

      /* $sql = "select p.*,c.disable as dn_disable from dn c left join contact p on (c.contact_id = p.id)
                    where dn_string = \"$dn\"";
        $row = db("oim")->fetchRow($sql);
        if($row) {
            $this->person_id = $row->id;
            $this->person_name = $row->name;
            $this->person_email = $row->primary_email;
            $this->person_phone = $row->primary_phone;
            $this->timezone = $row->timezone;
            $this->disable = ($row->dn_disable || $row->disable);
        }
      */

    }

    private function lookupRoles($person_id)
    {
        //lookup auth_types that are associated with this person
        $sql = "select d.authorization_type_id as auth_type_id
            from dn c left join dn_authorization_type d on (c.id = d.dn_id)
            where c.disable = 0 and c.contact_id = $person_id";
        $auth_types = db("oim")->fetchAll($sql);
        //and add roles to roles list
        foreach($auth_types as $auth_type) {
            //merge new role sets
            $roles = @config()->auth_metrics[$auth_type->auth_type_id];
            if(is_array($roles)) {
                foreach($roles as $role) {
                    if(!in_array($role, $this->roles)) {
                        $this->roles[] = $role;
                    }
                }
            }
        }
    }

    public function getRoles()
    {
        return $this->roles;
    }
    public function hasRole($role)
    {
        return in_array($role, $this->roles);
    }
    public function isGuest() { return $this->guest; }
    public function isDisabled() { return $this->disable; }
    public function getPersonID() { return $this->person_id; }
    public function getPersonName() { return $this->person_name; }
    public function getPersonEmail() { return $this->person_email; }
    public function getPersonPhone() { return $this->person_phone; }
    public function getDN() { return $this->dn; }
    //public function getTimeZone() { return $this->timezone; }
}
