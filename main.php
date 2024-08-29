<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
include('connection.php');

class MAIN
{
    private $conn;

    public function __construct()
    {
        $this->conn = DatabaseConnection::getInstance()->getConnection();
    }

    public function createUser($json)
    {
        $json = json_decode($json, true);

        try {
            if (isset($json["name"]) && $json["cdetails"] && $json["address"]) {
                $name = $json["name"];
                $cdetails = $json["cdetails"];
                $address = $json["address"];

                $checkUserSQL = "SELECT * FROM `owners` WHERE `Name` = :name";
                $checkUserStmt = $this->conn->prepare($checkUserSQL);
                $checkUserStmt->bindParam(":name", $name, PDO::PARAM_STR);
                $checkUserStmt->execute();

                if ($checkUserStmt->rowCount() > 0) {
                    return json_encode(array("error" => "The user " . $name . " is already existing in the database"));
                }

                $sql = "INSERT INTO `owners`(`Name`, `ContactDetails`, `Address`) VALUES (:name, :cdetails, :address)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(":name", $name, PDO::PARAM_STR);
                $stmt->bindParam(":cdetails", $cdetails, PDO::PARAM_STR);
                $stmt->bindParam(":address", $address, PDO::PARAM_STR);
                if ($stmt->execute()) {
                    return json_encode(array("success" => "User added!"));
                } else {
                    return json_encode(array("error" => $stmt->errorInfo()));
                }
            } else {
                return json_encode(array("error" => "Missing information"));
            }

        } catch (Exception $e) {
            return json_encode(array("error" => $e->getMessage()));
        } finally {
            unset($this->conn);

        }
    }

    public function createBreed($json)
    {
        $json = json_decode($json, true);
        try {
            if (isset($json["breedName"]) && isset($json["speciesID"])) {
                $breedName = $json["breedName"];
                $speciesID = $json["speciesID"];
                $checkSql = "SELECT * FROM `breeds` WHERE `BreedName` = :breedName";
                $checkStmt = $this->conn->prepare($checkSql);
                $checkStmt->bindParam(":breedName", $breedName, PDO::PARAM_STR);
                $checkStmt->execute();
                if ($checkStmt->rowCount() > 0) {
                    return json_encode(array("error" => "Breedname " . $breedName . " is already in the database"));
                }


                $sql = "INSERT INTO `breeds`(`BreedName`, `SpeciesID`) VALUES (:breedName, :speciesID) ";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(":breedName", $breedName, PDO::PARAM_STR);
                $stmt->bindParam(":speciesID", $speciesID);

                if ($stmt->execute()) {
                    return json_encode(array("success" => "Breedname `" . $breedName . "` is successfully created"));
                } else {
                    return json_encode(array("error" => $stmt->errorInfo()));
                }

            } else {
                return json_encode(array("error" => "Missing Information"));
            }

        } catch (Exception $e) {
            return json_encode(array("error" => $e->getMessage()));
        } finally {
            unset($this->conn);
        }
    }

    public function createSpecies($json)
    {
        $json = json_decode($json, true);
        try {
            if (isset($json["speciesName"])) {
                $speciesName = $json["speciesName"];
                $checkSql = "SELECT * FROM `species` WHERE `SpeciesName` = :speciesName";
                $checkStmt = $this->conn->prepare($checkSql);
                $checkStmt->bindParam(":speciesName", $speciesName, PDO::PARAM_STR);
                $checkStmt->execute();
                if ($checkStmt->rowCount() > 0) {
                    return json_encode(array("error" => "Species name `" . $speciesName . "` is already in the database"));
                }

                $sql = "INSERT INTO `species`(`SpeciesName`) VALUES (:speciesName)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(":speciesName", $speciesName, PDO::PARAM_STR);
                if ($stmt->execute()) {
                    return json_encode(array("success" => "Created the Species"));
                } else {
                    return json_encode(array("error" => $stmt->errorInfo()));
                }
            } else {
                return json_encode(array("error" => "Species name not provided"));
            }
        } catch (Exception $e) {
            return json_encode(array("error" => $e->getMessage()));
        } finally {
            unset($this->conn);
        }
    }

    public function createPet($json)
    {
        $json = json_decode($json, true);
        try {
            $sql = "INSERT INTO `pets`(`Name`, `SpeciesID`, `BreedID`, `DateOfBirth`, `OwnerID`)
                     VALUES (:name, :speciesID, :breedID, :dob, :oid)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":name", $json["name"], PDO::PARAM_STR);
            $stmt->bindParam(":speciesID", $json["speciesID"]);
            $stmt->bindParam(":breedID", $json["breedID"]);
            $stmt->bindParam(":dob", $json["dob"]);
            $stmt->bindParam(":oid", $json["oid"]);

            if ($stmt->execute()) {
                return json_encode(array("success" => "The pet `" . $json["name"] . "` was added successfully!"));
            } else {
                return json_encode(array("error" => $stmt->errorInfo()));
            }
        } catch (Exception $e) {
            return json_encode(array("error" => $e->getMessage()));
        } finally {
            unset($this->conn);
        }
    }

    public function getBreeds()
    {
        $sql = "SELECT * FROM `breeds` ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return json_encode(array("success" => $result));
    }

    public function getSpecies()
    {
        $sql = "SELECT * FROM `species` ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return json_encode(array("success" => $result));
    }

    public function getUser($json)
    {
        $json = json_decode($json, true);

        if (!isset($json["name"]) || empty($json["name"])) {
            return json_encode(array("error" => "Name parameter is missing or empty"));
        }

        try {
            $sql = "SELECT * FROM `owners` WHERE `Name` = :name";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":name", $json["name"], PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return json_encode(array("success" => $result));
            } else {
                return json_encode(array("error" => "No such user found"));
            }
        } catch (Exception $e) {
            return json_encode(array("error" => $e->getMessage()));
        } finally {
            unset($this->conn);
        }
    }

    public function getPets($json)
    {
        $json = json_decode($json, true);

        try {
            $sql = "SELECT pets.*, owners.OwnerID, owners.Name as OwnerName, species.SpeciesName, breeds.BreedName
                    FROM pets
                    INNER JOIN owners ON pets.OwnerID = owners.OwnerID
                    INNER JOIN species ON pets.SpeciesID = species.SpeciesID
                    INNER JOIN breeds ON pets.BreedID = breeds.BreedID";

            $conditions = [];
            $params = [];

            if (isset($json["breed"])) {
                $conditions[] = "breeds.BreedName = :breed";
                $params[':breed'] = $json["breed"];
            }

            if (isset($json["specie"])) {
                $conditions[] = "species.SpeciesName = :specie";
                $params[':specie'] = $json["specie"];
            }

            if (isset($json["owner"])) {
                $conditions[] = "owners.Name = :owner";
                $params[':owner'] = $json["owner"];
            }

            if (count($conditions) > 0) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            $stmt = $this->conn->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindParam($key, $value);
            }
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return json_encode(array("success" => $stmt->fetchAll(PDO::FETCH_ASSOC)));
            } else {
                return json_encode(array("error" => "Nothing here!"));
            }
        } catch (Exception $e) {
            return json_encode(array("error" => $e->getMessage()));
        } finally {
            unset($this->conn);
        }
    }

    public function getUserProfile($json)
    {
        $json = json_decode($json, true);

        if (!isset($json["name"]) || empty($json["name"])) {
            return json_encode(array("error" => "Name parameter is missing or empty"));
        }

        try {

            $sql = "SELECT * FROM `owners` WHERE `Name` = :name";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":name", $json["name"], PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                $sqlCount = "SELECT COUNT(*) as pet_count FROM `pets` WHERE `OwnerID` = :owner_id";
                $stmtCount = $this->conn->prepare($sqlCount);
                $stmtCount->bindParam(":owner_id", $result["OwnerID"], PDO::PARAM_INT);
                $stmtCount->execute();
                $petCount = $stmtCount->fetchColumn();

                return json_encode(array(
                    "success" => array(
                        "user" => $result,
                        "pet_count" => $petCount
                    )
                ));
            } else {
                return json_encode(array("error" => "No such user found"));
            }
        } catch (Exception $e) {
            return json_encode(array("error" => $e->getMessage()));
        } finally {
            unset($this->conn);
        }
    }



}

$main = new MAIN();

if ($_SERVER["REQUEST_METHOD"] == "GET" || $_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_REQUEST['operation']) && isset($_REQUEST['json'])) {
        $operation = $_REQUEST['operation'];
        $json = $_REQUEST['json'];

        switch ($operation) {
            case 'createUser':
                echo $main->createUser($json);
                break;

            case 'createBreed':
                echo $main->createBreed($json);
                break;

            case 'createSpecies':
                echo $main->createSpecies($json);
                break;

            case 'createPet':
                echo $main->createPet($json);
                break;

            case "getBreeds":
                echo $main->getBreeds();
                break;

            case "getSpecies":
                echo $main->getSpecies();
                break;

            case "getPets":
                echo $main->getPets($json);
                break;

            case "getUser":
                echo $main->getUser($json);
                break;

            case "getUserProfile":
                echo $main->getUserProfile($json);
                break;

            default:
                echo json_encode(["error" => "Invalid operation"]);
                break;
        }
    } else {
        echo json_encode(["error" => "Missing parameters"]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}

?>