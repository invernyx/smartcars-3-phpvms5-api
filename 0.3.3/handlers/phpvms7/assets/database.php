<?php
// Database library
// PHP 7
class Database {
    private $connection = null;

    function __construct(string $databaseName, string $databaseHost, string $databaseUsername, string $databasePassword) {
        $connection = new PDO('mysql:dbname=' . $databaseName . ';host=' . $databaseHost . ';charset=utf8', $databaseUsername, $databasePassword);
        $connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection = $connection;
    }

    public function execute(string $userQuery, array $data=array()) {
        // execute - This function is used if the query is not present within this database driver. This function will not return any data, but will return true if successful.
        // $userQuery - The PDO query that will be ran to the database. If parsing user data, use question mark as the user fields, and pass their data through the $data variable
        // $data - The user data that will be parsed through PDO. This is an empty array by default.
        if ($this->connection != null ) {
            $query = $this->connection->prepare($userQuery);
            try {
                $query->execute($data);
            } catch (Exception $e ) {
                return $e;
            }
            $query->closeCursor();
            return true;
        }
        return null;
    }

    public function fetch(string $userQuery, array $array=array()) {
        // fetch - This function is similar to execute, however, the data received by the query will be returned by the user in array form, each array being a row.
        // $userQuery - The PDO query that will be ran to the database. If parsing user data, use question mark as the user fields, and pass their data through the $data variable
        // $data - The user data that will be parsed through PDO. This is an empty array by default.
        if ($this->connection != null ) {
            $query = $this->connection->prepare($userQuery);
            $results = array();
            try {
                if ($query->execute($array))
                {
                    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                        array_push($results, $row);
                    }
                }
            } catch (Exception $e ) {
                return null;
            }
            $query->closeCursor();
            return $results;
        }
        return null;
    }

    public function createTable(string $tbl, string $vars) {
        // createTable - Create a table if it does not exist
        // $tbl - The name of the table
        // $vars - The variables and their type definitions
        if ($this->connection != null) {
            $query = $this->connection->prepare('CREATE TABLE IF NOT EXISTS ' . $tbl . ' (' . $vars . ')');
            try {
                $query->execute();
            } catch (Exception $e) {
                return null;
            }
            $query->closeCursor();
            return true;
        }
        return null;
    }
    
    public function deleteTable(string $tbl) {
        // deleteTable - Delete a table if it does exist
        // $tbl - The name of the table
        if ($this->connection != null) {
            $query = $this->connection->prepare('DROP TABLE IF EXISTS ' . $tbl);
            try {
                $query->execute();
            } catch (Exception $e) {
                return null;
            }
            $query->closeCursor();
            return true;
        }
        return null;
    }
    
    public function insert(string $tbl, array $data, string $extra='') {
        // insert - Insert a value in a table
        // $tbl - The name of the table
        // $data - A dictionary of data to be inserted into the database, the key being the field and the value being the data inserted
        // $extra - Extra fields to add to the query
        if ($this->connection != null) {
            $sql = 'INSERT INTO ' . $tbl . ' (';
            $count = 0;
            $newData = array();
            foreach($data as $key=>$value) {
                if ($count != 0) {
                    $sql .= ', ';   
                }
                $sql .= $key;
                $count++;
            }
            $sql .= ') VALUES (';
            $count = 0;
            foreach($data as $key=>$value) {
                if ($count != 0) {
                    $sql .= ', ';   
                }
                $sql .= ':' . $key;
                $newData[':' . $key] = $value;
                $count++;
            }
            $sql .= ') ' . $extra;
            $query = $this->connection->prepare($sql);
            try {
                $query->execute($newData);
            } catch (Exception $e) {
                return null;
            }
            $query->closeCursor();
            return true;
        }
        return null;
    }

    public function replace(string $tbl, array $data, string $extra='') {
        // replace - Replace a value from a table
        // $tbl - The name of the table
        // $data - A dictionary of data to be inserted into the database, the key being the field and the value being the data inserted
        // $extra - Extra fields to add to the query
        if($this->connection != null) {
            $sql = 'REPLACE INTO ' . $tbl . '(';
            $count = 0;
            $newData = array();
            foreach($data as $key=>$value) {
                if ($count != 0) {
                    $sql .= ', ';
                }
                $sql .= $key;
                $count++;
            }
            $sql .= ') VALUES (';
            $count = 0;
            foreach($data as $key=>$value) {
                if ($count != 0) {
                    $sql .= ', ';   
                }
                $sql .= ':' . $key;
                $newData[':' . $key] = $value;
                $count++;
            }
            $sql .= ') ' . $extra;
            $query = $this->connection->prepare($sql);
            try {
                $query->execute($newData);
            } catch (Exception $e) {
                return null;
            }
            $query->closeCursor();
            return true;
        }
        return null;
    }
    
    public function select(string $tbl, string $fields='*', string $extra='') {
        // select - Select values from a table
        // $tbl - The name of the table
        // $fields - The fields to be selected, '*' by default
        // $extra - Extra fields to add to the query
        if ($this->connection != null) {
            $query = $this->connection->prepare('SELECT ' . $fields . ' FROM ' . $tbl . ' ' . $extra);
            $results = array();
            try {
                if ($query->execute())
                {
                    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                        array_push($results, $row);
                    }
                }
            } catch (Exception $e) {
                return null;
            }
            $query->closeCursor();
            return $results;
        }
        return null;
    }

    public function getLastInsertID(string $sequence = null) {
        return $this->connection->lastInsertId($sequence);
    }
}
?>