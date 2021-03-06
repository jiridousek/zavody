<?php

/**
 * RaceRepository
 *
 * @author Jiří Doušek <405245@mail.mini.cz>
 */
class RaceRepository {
	
	private $dbMapperFactory;	

	/**
	 * @param $dbMapperFactory
	 */
	public function __construct($dbMapperFactory) {
		$this->dbMapperFactory = $dbMapperFactory;
	}
	
	/**
	 * 
	 * @return RaceDbMapper
	 */
	private function getDbMapper() {
		return call_user_func($this->dbMapperFactory);
	}
	
	public function getRace($id) {
		$race = $this->getDbMapper()->getRace($id);
		$race->repository = $this;
		return $race;
	}
	
	public function getNumRaces() {
		return $this->getDbMapper()->getNumRaces();
	}


	public function getStatewideRound($season) {
		$race = $this->getDbMapper()->getStatewideRound($this, $season);	
		return $race;
	}
	
	public function getRegions() {
		return $this->getDbMapper()->getRegions();
	}
	
	public function getRaces($season) {
		return $this->getDbMapper()->getRaces($this, $season);
	}
	
	public function getRacesByWatch($watchId) {
		return $this->getDbMapper()->getRacesByWatch($this, $watchId);
	}
	
	public function getRacesToLogIn($season) {
		return $this->getDbMapper()->getRacesToLogIn($this, $season);
	}

	public function getRound($id) {
		return $this->getDbMapper()->getRound($id);
	}
	
	public function getSeasonName($seasonId) {
		return $this->getDbMapper()->getSeasonName($seasonId);
	}
	
	public function getRegion($id) {
		return $this->getDbMapper()->getRegion($id);
	}
	
	public function getMembersRange($id) {
		return $this->getDbMapper()->getMembersRange($id);
	}
	
	/**
	 * Vrátí navazující závod, do kterého se postuje ze zadaného kola
	 * 
	 * @param int $id ID závodu, ze kterého se hledá postup
	 * @return \Race navazující závod
	 */
	public function getAdvance($id) {
		$advanceId = $this->getDbMapper()->getAdvance($id);
		if ($advanceId) {
			$advance = $this->getDbMapper()->getRace($advanceId);
			$advance->repository = $this;
			return $advance;
		}
		return null;
	}
	
	public function getKey($raceId) {
		return $this->getDbMapper()->getKey($raceId);
	}

	public function getAuthor($id) {
		return $this->getDbMapper()->getAuthor($id);
	}


	public function getEditors($id) {
		return $this->getDbMapper()->getEditors($id);
	}

	public function getOrganizer($id) {
		return $this->getDbMapper()->getOrganizer($id);
	}
	
	public function getDataForForm($id) {
		return $this->getDbMapper()->getDataForForm($id);
	}
	
	public function getGuideAge($season) {
		return $this->getDbMapper()->getGuideAge($season);
	}
	
	public function getRunnerAge($season) {
		return $this->getDbMapper()->getRunnerAge($season);
	}
	
	public function getNumWatchs($raceId, $category = NULL) {
		return $this->getDbMapper()->getNumWatchs($raceId, $category);
	}
	
	public function getNumAdvance($raceId, $category) {
		return $this->getDbMapper()->getNumAdvance($raceId, $category);
	}
	
	public function getToken($raceId) {
		return $this->getDbMapper()->getToken($raceId);
	}
	
	public function setToken($raceId, $token) {
		$this->getDbMapper()->setToken($raceId, $token);
	}
	
	public function confirm($raceId, $token) {
		return $this->getDbMapper()->confirm($raceId, $token);
	}
	
	public function getRacesByEditor($userId) {
		return $this->getDbMapper()->getRacesByEditor($this, $userId);
	}
	
	public function getRacesByOrganizer($unitId) {
		return $this->getDbMapper()->getRacesByOrganizer($this, $unitId);
	}
	
	public function getRacesByParticipant($personId) {
		return $this->getDbMapper()->getRacesByParticipant($this, $personId);
	}	
	
	public function getPrevRace($watchId, Race $race) {
		$prevRace =  $this->getDbMapper()->getPrevRace($watchId, $race);
		$prevRace->repository = $this;
		return $prevRace;
	}
	
	public function deleteAdvancedWatchs($prevId, $advanceId) {
		return $this->getDbMapper()->deleteAdvancedWatchs($prevId, $advanceId);
	}
}
