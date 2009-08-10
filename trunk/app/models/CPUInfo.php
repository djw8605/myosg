<?

class CPUInfo extends CachedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        return "SELECT * from cpu_info order by name";
    }
}
