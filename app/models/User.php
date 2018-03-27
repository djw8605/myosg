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
 
      if($_SESSION["session_login"]==""){
	
        slog("session login is NOT:" + $_SESSION["session_login"]);
	
	$sql_sso = "select * from contact_authorization_type where email = \"$dn\" or email1 = \"$dn\" or email2 = \"$dn\" or email3 = \"$dn\" limit 1 ";
	
	$row_sso = db("sso")->fetchRow($sql_sso);
	
	
	$sql_select_contact = "select * from contact where primary_email = '".$dn."' or  secondary_email = '".$dn."'";
	
	slog("UserModule: There is Contact: $sql_select_contact");
	$contact_no_sso_id=0;
	$contact_id=0;
	
	$row_select_contact = db("oim")->fetchRow($sql_select_contact);
	if($row_select_contact) {
	  $contact_id = $row_select_contact->id;
	  $sql_select_contact_record = "select * from contact_authorization_type where contact_id= $contact_id";
	  
	  slog("UserModule: There is contact_authorization_type with contact_id # $contact_id: $sql_select_contact_record");
	  
	  $row_select_contact_record = db("sso")->fetchRow($sql_select_contact_record);
	  if($row_select_contact_record) {
	    $contact_no_sso_id=$row_select_contact_record->id;
	    $sso_id_last = $contact_no_sso_id;
	    $contact_flag=1;
	  }
	}
	$sql_select_dn = "select * from dn where dn_string='".$_SERVER["SSL_CLIENT_S_DN"]."'";
	
	slog($sql_select_dn);
	$row_select_dn = db("oim")->fetchRow($sql_select_dn);
	if($row_select_dn) {
	  $dn_string_id = $row_select_dn->id;
	  $contact_id = $row_select_dn->contact_id;
          $dn_contact_id = $row_select_dn->contact_id;
	  $sql_sso_dn = "select * from contact_authorization_type where contact_id=".$contact_id." ";
	  slog($sql_sso_dn);
	  
	  slog("UserModule: There is Contact: $sql_sso_dn");
	  
	  $row_select_sso_dn = db("sso")->fetchRow($sql_sso_dn);
	  if($row_select_sso_dn) {
	    
	    $contact_no_sso_id=$row_select_sso_dn->id;
	    $sso_id_last = $contact_no_sso_id;
	    $dn_flag=1;
	  }
	}
	
	
	if($row_sso) {
	  
	  $update_sso = "update  contact_authorization_type set given_name='".$_SESSION["given_name"]."', family_name ='".$_SESSION["family_name"]."', idp= '".$_SESSION["idp"]."', idp_name= '".$_SESSION["idp_name"]."',aud='".$_SESSION["aud"]."', name= '".$_SESSION["name"]."',  oidc= '".$_SESSION["oidc"]."', openid= '".$_SESSION["openid"]."', sub='".$_SESSION["sub"]."', access_token='".$_SESSION["access_token"]."', remote_user='".$_SESSION["remote_user"]."', last_login=now() where id = ".$row_sso->id."";
	  slog("UserModule- there SSO; ".$update_sso);
	  
	  //print $update_sso;                                                                                                                                                                           
	  
	  db("sso")->exec($update_sso);
	  $sso_idp = $row_sso->idp;
	  $this->person_id = $row_sso->id;
	  $this->person_name = "".$row_sso->given_name." ". $row_sso->family_name."";
	  $this->person_email = $row_sso->email;
	  $this->disable =  ($row_sso->disable || $row_sso->dn_disable);
	  $_SESSION["sso_contact_email"]=$row_sso->email;
	  $_SESSION["sso_dn_id"]=$row_sso->id;
	  $_SESSION["sso_contact_id"]=$row_sso->contact_id;
	  $_SESSION["sso_contact_name"]="".$row_sso->given_name." ". $row_sso->family_name."";
	  $_SESSION["sso_disable"]=($row_sso->disable || $row_sso->dn_disable);
	  $_SESSION["session_login"]="1";
	  
	  
	}else{
	  
	  
	  if ($contact_no_sso_id>0){
	    $update_sso_rec = "update  contact_authorization_type set email1='".$dn."', last_login=now() where id = ".$contact_no_sso_id."";
	    
	    slog("Found Record Updating Email:  ".$update_sso_rec);
	    $sso_id_last = $contact_no_sso_id;
	    db("sso")->exec($update_sso_rec);
	    
	    
	    if($contact_flag>0){
	      $dn_sql = "select dn.id as dn_id from dn left join dn_authorization_type on dn.id=dn_authorization_type.dn_id where contact_id=".$contact_id." and (disable=0 or disable is null) order by dn.id desc limit 1";
	      $select_dn_rec = db("oim")->fetchRow($dn_sql);
	      $dn_id= $select_dn_rec->dn_id;
	      if($dn_id!=""){
		$sql = "select * from dn_authorization_type where dn_id=".$dn_id."";
		foreach(db("oim")->fetchAll($sql) as $row) {
		  $authorization_type_id  = $row->authorization_type_id;
		  db("sso")->exec("delete from contact_authorization_type_index where contact_authorization_type_id=".$sso_id_last." and authorization_type_id=".$authorization_type_id."");
		  $insert_action = "INSERT INTO contact_authorization_type_index (contact_authorization_type_id, authorization_type_id) VALUES (".$sso_id_last.",".$authorization_type_id.")";
		  db("sso")->exec($insert_action);
		}
	      }else{
		$insert_1 = "INSERT INTO contact_authorization_type_index (contact_authorization_type_id, authorization_type_id) VALUES (".$sso_id_last.",1)";
		db("sso")->exec($insert_1);
	      }
	      
	    }elseif($dn_flag>0){
	      
	      $sql = "select * from dn_authorization_type where dn_id=".$dn_string_id."";
	      foreach(db("oim")->fetchAll($sql) as $row) {
		$authorization_type_id  = $row->authorization_type_id;
		db("sso")->exec("delete from contact_authorization_type_index where contact_authorization_type_id=".$sso_id_last." and authorization_type_id=".$authorization_type_id."");
		$insert_action = "INSERT INTO contact_authorization_type_index (contact_authorization_type_id, authorization_type_id) VALUES (".$sso_id_last.",".$authorization_type_id.")";
		db("sso")->exec($insert_action);
		
	      }
	      
	    }
	    $_SESSION["session_login"]="1";
	    
	    
	  }else{
	    try {
	      db("sso")->beginTransaction();
	      
	      if($dn_flag>0 && $contact_no_sso_id>0){
		  $update_sso_dn = "update  contact_authorization_type set contact_id='".$dn_contact_id."', email2='".$_SESSION["email"]."' where id = ".$contact_no_sso_id."";
		  
		  $sso_id_last= $contact_no_sso_id;
		  db("sso")->exec($update_sso_dn);
		  
		  $sql = "select * from dn_authorization_type where dn_id=".$dn_string_id."";
		  foreach(db("oim")->fetchAll($sql) as $row) {
		    $authorization_type_id  = $row->authorization_type_id;
		    db("sso")->exec("delete from contact_authorization_type_index where contact_authorization_type_id=".$sso_id_last." and authorization_type_id=".$authorization_type_id."");
		    $insert_action = "INSERT INTO contact_authorization_type_index (contact_authorization_type_id, authorization_type_id) VALUES (".$sso_id_last.",".$authorization_type_id.")";
		    db("sso")->exec($insert_action);
		    
		  }
		  $_SESSION["session_login"]="1";
	      }elseif($dn_flag>0 && $contact_no_sso_id==0){
		$insert_sso_dn_last = "insert into contact_authorization_type (given_name, family_name, email, idp, idp_name,aud, iat, name, iss, nonce, oidc, openid, sub, access_token, access_token_expires, remote_user,authorization_type_id, created,contact_id) values('".$_SESSION["given_name"]."', '".$_SESSION["family_name"]."','".$_SESSION["email"]."', '".$_SESSION["idp"]."', '".$_SESSION["idp_name"]."','".$_SESSION["aud"]."', '".$_SESSION["iat"]."', '".$_SESSION["name"]."', '".$_SESSION["iss"]."', '".$_SESSION["nonce"]."', '".$_SESSION["oidc"]."', '".$_SESSION["openid"]."', '".$_SESSION["sub"]."', '".$_SESSION["access_token"]."', '".$_SESSION["access_token_expires"]."', '".$_SESSION["remote_user"]."',1, now(),".$dn_contact_id.")";


		slog("insert new entry into contact_authorization_type: ".$insert_sso_dn_last);
                $insert_sso_dn=db("sso")->query($insert_sso_dn_last);
                $sso_id_last=db("sso")->lastInsertId();

		$sql = "select * from dn_authorization_type where dn_id=".$dn_string_id."";
		foreach(db("oim")->fetchAll($sql) as $row) {
		  $authorization_type_id  = $row->authorization_type_id;
		  db("sso")->exec("delete from contact_authorization_type_index where contact_authorization_type_id=".$sso_id_last." and authorization_type_id=".$authorization_type_id."");
		  $insert_action = "INSERT INTO contact_authorization_type_index (contact_authorization_type_id, authorization_type_id) VALUES (".$sso_id_last.",".$authorization_type_id.")";
		  db("sso")->exec($insert_action);

		}
		$_SESSION["session_login"]="1";

	      }else{
		$insert_sso_dn_last = "insert into contact_authorization_type (given_name, family_name, email, idp, idp_name,aud, iat, name, iss, nonce, oidc, openid, sub, access_token, access_token_expires, remote_user,authorization_type_id, created) values('".$_SESSION["given_name"]."', '".$_SESSION["family_name"]."','".$_SESSION["email"]."', '".$_SESSION["idp"]."', '".$_SESSION["idp_name"]."','".$_SESSION["aud"]."', '".$_SESSION["iat"]."', '".$_SESSION["name"]."', '".$_SESSION["iss"]."', '".$_SESSION["nonce"]."', '".$_SESSION["oidc"]."', '".$_SESSION["openid"]."', '".$_SESSION["sub"]."', '".$_SESSION["access_token"]."', '".$_SESSION["access_token_expires"]."', '".$_SESSION["remote_user"]."',1, now())";
		
		
		slog("insert new entry into contact_authorization_type: ".$insert_sso_dn_last);
		$insert_sso_dn=db("sso")->query($insert_sso_dn_last);
		$sso_id_last=db("sso")->lastInsertId();
		$insert_actions ="insert into contact_authorization_type_index (contact_authorization_type_id,authorization_type_id) values (".$sso_id_last.",1)";
		slog($insert_actions);
		db("sso")->exec($insert_actions);
		db("sso")->commit();
		$_SESSION["session_login"]="1";

	      }
	      
	      $_SESSION["session_login"]="1";
	      
	    } catch (Exception $e) {
	      db("sso")->rollBack();
	    }
	  }
	}
	
	
	
	if($sso_id_last!=""){
	  $sql_sso2 = "select * from contact_authorization_type where id=$sso_id_last ";
	  
	  slog("Get record:".$sql_sso2);
	  $row_sso2 = db("sso")->fetchRow($sql_sso2);
	  
	  if($row_sso2) {
	    $this->person_id = $row_sso2->id;
	    $this->person_name = "".$row_sso2->given_name." ". $row_sso2->family_name."";
	    $this->person_email = $row_sso2->email;
	    $this->disable =  ($row_sso2->disable || $row_sso2->dn_disable);
	    
	    $_SESSION["sso_contact_email"]=$row_sso2->email;
	    $_SESSION["sso_dn_id"]=$row_sso2->id;
	    $_SESSION["sso_contact_id"]=$row_sso2->contact_id;
	    $_SESSION["sso_contact_name"]="".$row_sso2->given_name." ". $row_sso2->family_name."";
	    $_SESSION["sso_disable"]=($row_sso2->disable || $row_sso2->dn_disable);
	    
	  }     
	}
             }else{
       
	    $this->person_id = $_SESSION["sso_contact_id"];
	    $this->person_name = $_SESSION["sso_contact_name"];
	    $this->person_email = $_SESSION["sso_contact_email"];
	    $this->disable = $_SESSION["sso_disable"];
	
      }
	
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
