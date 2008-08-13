<?

//returns a unique id number for div element (only valid for each session - don't store!)
function getuid()
{
    if(isset($_SESSION["uid"])) {
        $next_uid = $_SESSION["uid"];
        $_SESSION["uid"] = $next_uid + 1;
        return $next_uid+rand(); //add random number to avoid case when 2 different sessions are used
    } else {
        $_SESSION["uid"] = 1000; //let's start from 1000
    }
}
