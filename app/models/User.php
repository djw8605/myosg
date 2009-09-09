<?

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
        $this->timezone = "UTC";
        $this->dn = $dn;

        $this->guest = true;
        if($dn !== null) {
            $this->lookupUserID($dn);
            if($this->person_id !== null) {
                $this->guest = false;
                $this->lookupRoles($this->person_id);
            }
        }
    }

    private function lookupUserID($dn)
    {
        //make sure user DN exists and active
        $sql = "select p.* from dn c left join contact p on
                        (c.contact_id = p.id)
                    where
                        p.disable = 0 and dn_string = '$dn'";
        $row = db("oim")->fetchRow($sql);
        if($row) {
            $this->person_id = $row->id;
            $this->person_name = $row->name;
            $this->person_email = $row->primary_email;
            $this->person_phone = $row->primary_phone;
            $this->timezone = $row->timezone;
        }
    }

    private function lookupRoles($person_id)
    {
        //lookup auth_types that are associated with this person
        $sql = "select d.authorization_type_id as auth_type_id
            from
                dn c left join dn_authorization_type d on
                    (c.id = d.dn_id)
            where
                c.contact_id = $person_id";
        $auth_types = db("oim")->fetchAll($sql);
        //and add roles to roles list
        foreach($auth_types as $auth_type) {
            //merge new role sets
            $roles = config()->auth_metrics[$auth_type->auth_type_id];
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
    public function getPersonID() { return $this->person_id; }
    public function getPersonName() { return $this->person_name; }
    public function getPersonEmail() { return $this->person_email; }
    public function getPersonPhone() { return $this->person_phone; }
    public function getDN() { return $this->dn; }
    public function getTimeZone() { return $this->timezone; }
}
