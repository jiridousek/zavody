<?php

/**
 * PersonRepository
 *
 * @author Jiří Doušek <405245@mail.mini.cz>
 */
class PersonRepository {
		
	private $isMapper;
	private $dbMapperFactory;
	
	/**
	 * 
	 * @param PersonIsMapper $isMapper
	 * @param $dbMapperFactory
	 */
	public function __construct(PersonIsMapper $isMapper, $dbMapperFactory) {
		$this->isMapper = $isMapper;
		$this->dbMapperFactory = $dbMapperFactory;
	}
	
	/**
	 * 
	 * @return PersonDbMapper
	 */
	private function getDbMapper() {
		return call_user_func($this->dbMapperFactory);
	}
	
	/**
	 * Vrátí účastníka podle id
	 * 
	 * @param int $personId
	 */
	public function getPerson($personId) {
		try {
			$person = $this->isMapper->getPerson($personId);
		} catch (Exception $ex) {
			$person = $this->getDbMapper()->getPerson($personId);
		}
		$person->repository = $this;
		return $person;
	}
	
	public function getParticipant($systemId) {
		$person = $this->getDbMapper()->getParticipant($systemId);
		$person->repository = $this;
		return $person;
	}

	public function getPersonsByUnit($idUnit) {
		try {			
			return $this->isMapper->getPersonsByUnit($this, $idUnit);
		} catch (Skautis\Wsdl\PermissionException $ex) {			
			throw new \Race\PermissionException("Nemáte oprávnění zobrazit členy v této jednotce.", NULL, $ex);
		}
	}
	
	public function getRole($systemId, $raceId) {
		return $this->getDbMapper()->getRole($systemId, $raceId);
	}
	
	public function getRoleId($systemId, $raceId) {
		return $this->getDbMapper()->getRoleId($systemId, $raceId);
	}
	
	public function getRoles() {
		return $this->getDbMapper()->getRoles();
	}
	
	public function getRoleName($id) {
		return $this->getDbMapper()->getRoleName($id);
	}
	
	public function getPersonsByWatch($watchId, $raceId) {
		return $this->getDbMapper()->getPersonsByWatch($this, $watchId, $raceId);
	}
	
	public function getSexName($sexId) {
		return $this->getDbMapper()->getSexName($sexId);
	}
	
}
