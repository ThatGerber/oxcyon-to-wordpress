<?php


/** Table column conversion */
$oxc_importBrand = 'BDX'; // Three letter code for brand

$oxc_pdoUsername    = 'Dev_User';
$oxc_pdoPassword    = 'Welcome2013';
try {
    $oxc_bevOld = new PDO(
        "mysql:host=127.0.0.1;dbname=beverage_old",
        $oxc_pdoUsername,
        $oxc_pdoPassword);
} catch ( Exception $oxc_e ) {
    echo 'Connection failed: ' . $oxc_e->getMessage();
}
// query database
$oxc_authors = new oxc_selectQuery( $oxc_bevOld ); // new select query
$oxc_authorSql = 'SELECT ModuleSID, `First Name`, Bio
    FROM ' . $oxc_importBrand . '_authors_old';
$oxc_authorData = $oxc_authors->queryResults( $oxc_authorSql ); // query results
// update database
$updatePDO = new oxc_selectQuery( $oxc_bevOld );
$updateSQL = 'UPDATE ' . $oxc_importBrand . '_authors_old
        SET Bio = :encodedBio
        WHERE ModuleSID = :moduleSID';
// set new values
$i=1;
foreach ( $oxc_authorData as $author ) {
    //$encodedBio = mb_convert_encoding( $author['Bio'], 'UTF-8' );
    $moduleSID = $author['ModuleSID'];
    $pdoValues = array(
        ":encodedBio" => $encodedBio,
        ":moduleSID" => $moduleSID
    );
    $updatePDO->queryResults($updateSQL, $pdoValues);
    if ( $updatePDO->updated() ) {
        echo "<h3> Row " . $i++ . " Updated</h3>";
    } else {
        echo "<h3> Row " . $i++ . " Not Updated</h3>";
    }
    print $encodedBio;
}

$blobToLongtext = new oxc_selectQuery( $oxc_bevOld );
$formatSql = 'ALTER TABLE ' . $oxc_importBrand . '_authors_old CHANGE Bio Bio LONGTEXT CHARACTER SET utf8';
echo $formatSql;
$conversion = $blobToLongtext->queryResults( $formatSql );
echo $conversion;

/**
 * Import Functions Page
 *
 * This will hold functions and classes related to the project import.
 *
 * @category    PHP
 * @copyright   2014
 * @license     WTFPL
 * @version     1.0.0
 * @since       2/18/2014
 */

/**
 * SIM Database Transfer Script
 * The process will be: Categories, Tags, Pages, Posts. This data will be used to
 * fill in the different sections of the WordPress default database.
 *
 * Order of transfer:
 *
 * Categories/Tags
 *
 *
 */
class wpToOxcfyon {
    /**
     * Database connection info
     *
     * @var string
     */
    protected $databaseInfo;
    protected $PdoDatabase      = '';
    protected $PdoHost          = '';
    protected $PdoUsername      = '';
    protected $PdoPassword      = '';
    protected $PdoConnection;
    /**
     * Sets connection variables
     * @param $information
     */
    protected function setDbCredentials ( $information ) {
        extract( $information );
    }
    /**
     * Create database connection
     * @return PDO
     */

    public function databaseConnection( $array ) {
        // run submitted array through extract to set variables
        $this->databaseInfo = $array;
        // set database credentials
        $this->setDbCredentials( $this->databaseInfo );
        echo $this->PdoHost;
        try {
            $PdoConnection = new PDO(
                'mysql:host=' . $this->PdoHost . ';dbname=' . $this->PdoDatabase,
                'Dev_User',
                'Welcome2013'
            );
        } catch ( Exception $e ) {
            echo 'Connection failed: ' . $e->getMessage();
        }
        return $PdoConnection;
    }
    /**
     * Get Data from database
     *
     * @param $selections
     * @param $selectionTable
     * @param $searchColumn
     * @param $searchValue
     * @return array
     */
    public function getData( $pdo, $sql, $value ) {
        // establish database connection
        $database   = $pdo;
        // prepare database call
        $stmt  = $database->prepare( $sql );
        if (!$stmt) {
            echo "\nPDO::errorInfo():\n";
            print_r($database->errorInfo());
        }
        // execute the database call
        $stmt->execute();
        // set object
        $stmt->setFetchMode( PDO::FETCH_OBJ );
        // return row data
        return $stmt->fetchAll();
    }
    public function rowCount() {
        $this->
    }



    /******************************************************************************
     *
     *
     * Updating data
     *
     *
     ******************************************************************************/
    /**
     * Categories/Tags Table (WP-Taxonomy)
     * We'll begin by moving the Taxonomy table to the Categories and Tags
     *
     * Dates are unimportant. Important parts are ModuleSID, Title, Parent.  Name
     * can be used in place of Title.
     */
}
/***
 * Data hold
 *
 *
 *
 *
// Showing the results
while ( $row = $STH->fetch() )
{
echo '<tr>';
echo '<td>' . $row->ModuleSID . '</td>';
echo '<td>' . $row->Title . '</td>';
echo '<td>' . $row->Name . '</td>';
echo '<td>' . $row->Parent . '</td>';
echo '</tr>';
}
 *
 *
 *
 *
// connect to old data
$oldData = Array(
"PdoHost"       => "localhost",
"PdoDatabase"   => "beverage_old",
"PdoUsername"   => "root",
"PdoPassword"   => ''
);
$selectionsArray = Array('ModuleSID', 'Title', 'Name', 'Parent');

// select data
$bevOxcTable = new wp2ox();
$bevOXCTable->selectQuery($selectionsArray, 'BDX_Taxonomy_old', '"%AAAAAAAAAAAAAAAA%"', 'Parent');
 */