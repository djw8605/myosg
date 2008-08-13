<?

//lookup person information as well as role information
class User
{
    public function __construct($dn)
    {
        if(!Zend_Registry::isRegistered("db")) {
            $this->db = connectdb();
        } else {
            $this->db = Zend_Registry::get("db");
        }

        $this->roles = config()->auth_metrics[authtype::$auth_guest];
        $this->person_id = null;
        $this->person_name = "Guest";

        $this->lookupUserID($dn);
        if($this->person_id !== null) {
            $this->lookupRoles($this->person_id);
        }
    }

    private function lookupUserID($dn)
    {
        //make sure user DN exists and active
        $sql = "select p.first_name, p.last_name, p.person_id
                    from oim.certificate_dn c left join oim.person p on
                        (c.person_id = p.person_id)
                    where 
                        c.active = 1 and 
                        c.disable = 0 and 
                        dn_string = '$dn'";
        $row = $this->db->fetchRow($sql);
        if($row) {
            $this->person_id = $row->person_id;
            $this->person_name = $row->first_name." ".$row->last_name;
        }
    } 

    private function lookupRoles($person_id)
    { 
        //lookup auth_types that are associated with this person
        $sql = "select
            d.auth_type_id as auth_type_id
            from
                oim.certificate_dn c left join oim.dn_auth_type d on
                    (c.dn_id = d.dn_id)
            where
                d.active = 1 and
                c.active = 1 and
                c.disable = 0 and
                c.person_id = $person_id";
        $auth_types = $this->db->fetchAll($sql);
        //and add roles to roles list
        foreach($auth_types as $auth_type) {
            //merge new role sets
            $roles = config()->auth_metrics[$auth_type->auth_type_id];
            foreach($roles as $role) {
                if(!in_array($role, $this->roles)) {
                    $this->roles[] = $role;
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
    public function getPersonID() 
    {
        return $this->person_id;
    }
    public function getPersonName()
    {
        return $this->person_name;
    }
}
