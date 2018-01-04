<?php 

namespace App\Model;

use Nette;
use Nette\Utils\Strings;
use Nette\Database\Table\Selection;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;

/**
 * Description of Repository
 *
 * @author
 */

abstract class Repository extends Nette\Object {
    
    /** @var Context */
    protected $database;

    /** @var string */
    protected $tableName;

    public function __construct( Context $database ) {
        $this->database = $database;
    }

    /**
     * Vrací objekt reprezentující databázovou tabulku.
     * @return Selection
     */
    protected function getTable() {
    	if( isset( $this->tableName ) ) {
    		return $this->database->table( $this->tableName );
    	} else {
    		// název tabulky odvodíme z názvu třídy
    		preg_match( '#(\w+)Repository$#', get_class( $this ), $m );
    		return $this->database->table( lcfirst( $m[1] ) );
    	}
    }

    public function getConnection() {
    	return $this->database;
    }

    /**
     * Vrací všechny řádky z tabulky.
     * @return Selection
     */
    public function findAll() {
    	return $this->getTable();
    }

    /**
     * Vrací řádky podle filtru, např. array('name' => 'Jon').
     * @return Selection
     */
    public function findBy( array $by ) {
    	return $this->getTable()->where( $by );
    }

    /**
     * Vracia selection podľa jednej podmienky.
     * @param type $columnName
     * @param type $value
     * @return Selection
     */
    public function findByValue( $columnName, $value ) {
    	$condition = array( $columnName => $value );
    	return $this->findBy( $condition );
    }

    /**
     * Vráti riadok podľa ID.
     * @param type $id identifikátor / primárny kľúč
     * @return ActiveRow
     */
    public function findById( $id ) {
    	return $this->getTable()->get( $id );
    }

    public function update( $id, $data ) {
    	$this->getTable()->wherePrimary( $id )->update( $data );
    }

    /**
     * Pridá dáta do tabuľky a vráti záznam
     * @param $data sú pridané dáta
     * @return ActiveRow
     */
    public function insert( $data ) {
    	return $this->getTable()->insert( $data );
    }

    public function remove( $id ) {
    	$this->getTable()->get( $id )->delete();
    }

    public function select( $data ) {
        return $this->getTable()->select( $data );
    }

    public function getAsArray( $arch_id = null ) {
        $rows = $this->findAll()->where( 'archive_id', $arch_id );
        $data = array();
        if (!$rows) {
            return null;
        } else {
            $i = 0;
            foreach($rows as $row) {
                $data[$i] = $row;
                $i++;
            }
        } 
        return $data;
    } 
}