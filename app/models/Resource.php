<?

class Resource
{
    public function __construct()
    {
        if(!Zend_Registry::isRegistered("db")) {
            $this->db = connectdb();
        } else {
            $this->db = Zend_Registry::get("db");
        }
    }

    public function fetchAll_sql($service_type = null, $grid_type = null, $orderby="name", $dir = "ASC", $offset = null, $limit = null)
    {
        $schema = config()->db_oim_schema;
        $join = "($schema.resource r left join $schema.resource_contact rc on 
                (r.resource_id = rc.resource_id and rc.type_id = 3 and rc.rank_id = 1)
            ) left join $schema.person p on (rc.person_id = p.person_id)";

        if($service_type !== null) {
            $join = "($join) join $schema.resource_service s on 
                (r.resource_id = s.resource_id and s.service_id = $service_type)";
        }

        $grid_type_where = "";
        if($grid_type !== null) {
            $resource_groups = "select resource_group_id from $schema.resource_group RG where osg_grid_type_id = $grid_type";
            $resource_ids = "select resource_id from $schema.resource_resource_group RRG where resource_group_id IN ($resource_groups)";
            $grid_type_where = "and r.resource_id IN ($resource_ids)";
        }

        //filter by service type (per Arvind's request)
        $join = "($join) join oim.resource_service rs on r.resource_id = rs.resource_id";

        $sql = "select 
                `r`.`name` AS `name`,
                `r`.`resource_id` AS `id`,
                `r`.`fqdn` AS `uri`,
                `r`.`url` AS `url`,
                `p`.`first_name` AS `first_name`,
                `p`.`middle_name` AS `middle_name`,
                `p`.`last_name` AS `last_name`,
                `p`.`primary_email` AS `primary_email`
        from
            $join
        where 
            r.active = 1 and r.disable = 0 $grid_type_where
                and
            rs.service_id in (SELECT service_id FROM oim.service_service_group WHERE service_group_id=1) 
                and
            rs.service_id not in (SELECT DISTINCT PS.parent_service_id psid FROM oim.service PS WHERE PS.parent_service_id IS NOT NULL) 
        order by r.$orderby $dir";

        if($offset !== null and $limit !== null) {
            $sql .= " limit $offset,$limit";
        }    
        dlog($sql);
        return $sql;
    }

    public function fetchAll_count($service_type = null, $grid_type = null)
    {
        $sql = $this->fetchAll_sql($service_type, $grid_type);
        $sql = "select count(*) from ($sql) a";
        dlog($sql);
        return $this->db->fetchOne($sql);
    }

    public function fetchAll($service_type = null, $grid_type = null, $orderby="name", $dir = "ASC", $offset = null, $limit = null)
    {
        $sql = $this->fetchAll_sql($service_type, $grid_type, $orderby, $dir, $offset, $limit);
        return $this->db->fetchAll($sql);
    }

    public function lookupID($uri)
    {
        if(!isset($this->uri2id)) {
            $this->uri2id = array();
            $resources = $this->fetchAll();
            foreach($resources as $resource) {
                $this->uri2id[$resource->uri] = $resource->id;
            }
        }
        if(!isset($this->uri2id[$uri])) return null;
        return $this->uri2id[$uri]; 
    }
}
