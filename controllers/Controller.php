<?php
require_once "/home/xstasova/public_html/strankaZ6/helpers/Database.php";

class Controller
{
    private PDO $conn;
    /**
     * Controller constructor.
     * @param $conn
     */
    public function __construct(){
        $database = new Database();
        $this->conn = $database->getConn();
    }

    public function getEvents($event, $country){
        $stmt = $this->conn->prepare("SELECT DEN.datum AS date, Z.hodnota AS name  FROM DEN  
                                        INNER JOIN KALENDAR K on DEN.id = K.den_id 
                                        INNER JOIN KRAJINA K2 on K.krajina_id = K2.id      
                                        INNER JOIN ZAZNAM Z on K.zaznam_id = Z.id
                                        WHERE K.typ = :event AND K2.skratka = :country");

        try {
            $stmt->bindValue(':event', $event, PDO::PARAM_STR);
            $stmt->bindValue(':country', $country, PDO::PARAM_STR);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            return array(($event == "sviatok"?"holidays":"memorials") =>$this->getEventsWithFormatedDate( $stmt->fetchAll()));        }
        catch (Exception $e){
            return array("error"=>$e->getMessage());
        }
    }

    public function getEventsWithFormatedDate($events){
        for($i = 0; $i<sizeof($events); $i++)
            $events[$i]["date"] = $this->getRequiredFormat(array($events[$i]["date"]));
        return $events;

    }

    public function getName($date, $country){
        $stmt = $this->conn->prepare("SELECT Z.hodnota FROM KALENDAR
INNER JOIN DEN D on KALENDAR.den_id = D.id
INNER JOIN KRAJINA K on KALENDAR.krajina_id = K.id
INNER JOIN ZAZNAM Z on KALENDAR.zaznam_id = Z.id
WHERE K.skratka = :country AND D.datum = :date AND KALENDAR.typ = 'meno'");

        try {
            $stmt->bindValue(':date', $this->getMysqlDateFormat($date), PDO::PARAM_STR);
            $stmt->bindValue(':country', $country, PDO::PARAM_STR);
            $stmt->execute();
            return array("name" => $stmt->fetchAll(PDO::FETCH_COLUMN));        }
        catch (Exception $e){
            return array("error"=>$e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function addNameDate($name, $date){
        try{
            $date = $this->getMysqlDateFormat($date);
            $id_date = $this->getDateId($date);
            if($this->getNameId($name) != false)
              throw  new Exception("Meno sa už v kalendári nachádza!");
            $id_name = $this->addName($name);
            return array("added-id"=>$this->addNameToCalendar($id_name,$id_date));
        }catch (Exception $e){
            return array("error"=>$e->getMessage());
        }

    }
    public function addNameToCalendar($id_name,$id_date){
        $stmt = $this->conn->prepare("INSERT INTO KALENDAR (den_id, krajina_id,zaznam_id,typ)
                                        VALUES (:id_date,1,:id_name,'meno' )");
        $stmt->bindValue(":id_name", $id_name, PDO::PARAM_INT);
        $stmt->bindValue(":id_date", $id_date, PDO::PARAM_INT);

        try{
            $stmt->execute();
            return $this->conn->lastInsertId();
        }catch (PDOException $e){
            throw $e;
        }
    }

    public function addName($name){
        $stmt = $this->conn->prepare("INSERT INTO ZAZNAM (hodnota)
                                    VALUES (:name)");
        $stmt->bindValue(":name", $name, PDO::PARAM_STR);

        try{
            $stmt->execute();
            return $this->conn->lastInsertId();
        }catch (PDOException $e){
            throw $e;
        }
    }

    public function getNameId($name){
        $stmt = $this->conn->prepare("SELECT id FROM ZAZNAM WHERE hodnota = :name");
        $stmt->bindValue(":name", $name, PDO::PARAM_STR);

        $stmt->execute();
        return  $stmt->fetchColumn();
    }

    public function getDateId($date){
        $stmt = $this->conn->prepare("SELECT id FROM DEN WHERE datum = :date");
        $stmt->bindValue(":date", $date, PDO::PARAM_STR);

        $stmt->execute();
        return  $stmt->fetchColumn();
    }

    public function getDate($name, $country){
        $stmt = $this->conn->prepare("SELECT datum FROM KALENDAR
INNER JOIN DEN D on KALENDAR.den_id = D.id
INNER JOIN KRAJINA K on KALENDAR.krajina_id = K.id
INNER JOIN ZAZNAM Z on KALENDAR.zaznam_id = Z.id
WHERE K.skratka = :country AND Z.hodnota = :name ");
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':country', $country, PDO::PARAM_STR);
        try {
            $stmt->execute();
            return array("date" => $this->getRequiredFormat($stmt->fetchAll(PDO::FETCH_COLUMN)));
        }
        catch (Exception $e){
            return array("error"=>$e->getMessage());
        }

    }

    public function getEventFromCountry($event, $country){

        return $this->getEvents($event, $country );
    }


    public function getNameFromCountry($date,$country ){

        return $this->getName( $date, $country);
    }

    public function getDateFromCountry($name, $country){

        return $this->getDate($name, $country);
    }

    /**
     * @throws Exception
     */
    public function getMysqlDateFormat($date): string
    {
        if (substr_count($date, '.') != 2)
            throw new Exception("Bad input date format");
        $dateArray = explode('.', $date);
        return "0004-" . $this->getMonthDayMysqlFormat($dateArray[1]) . '-' . $this->getMonthDayMysqlFormat($dateArray[0]);
    }
    public function getMonthDayMysqlFormat($format): string
    {
        if (strlen($format)==2)
            return $format;
        return '0'.$format;
    }

    public function getRequiredFormat($date): string|array
    {
        $date2 = array();
        foreach ($date as $day){
            $dateArray = explode('-', $day);
            array_push($date2, ltrim($dateArray[2], '0') . "." . ltrim($dateArray[1], '0') . ".");
        }
        return (sizeof($date2) == 1)?$date2[0]: $date2;
    }

    /**
     * @return mixed|PDO
     */
    public function getConn(): mixed
    {
        return $this->conn;
    }

    /**
     * @param mixed|PDO $conn
     */
    public function setConn(mixed $conn): void
    {
        $this->conn = $conn;
    }

}


