<?

class Mutex
{
    private $id;
    private $sem_id;
    private $is_acquired = false;
    private $filename = '';
    private $filepointer;

    public function init($id)
    {
        $this->id = $id;

        if(!($this->sem_id = sem_get($this->id, 1))){
            print "Error getting semaphore";
            return false;
        }

        return true;
    }

    public function acquire()
    {
        if (! sem_acquire($this->sem_id)){
            print "error acquiring semaphore";
            return false;
        }

        $this->is_acquired = true;
        return true;
    }

    public function release()
    {
        if(!$this->is_acquired)
            return true;

        if (! sem_release($this->sem_id)){
            print "error releasing semaphore";
            return false;
        }

        $this->is_acquired = false;
        return true;
    }

    public function getId()
    {
        return $this->sem_id;
    }
}
