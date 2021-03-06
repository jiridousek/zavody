<?php

/**
 * AdminDbMapper
 *
 * @author Jiří Doušek <405245@mail.mini.cz>
 */
class AdminDbMapper extends BaseDbMapper {
	
	/**
	 * Vrátí tabulku soutěží
	 * 
	 * @return \Nette\Database\Selection
	 */
	public function getAllCompetitions() {
		return $this->database->table('competition');
	}
	
	/**
	 * Vrátí pole všech ročníky v databázi
	 * 
	 * @return \Season[]
	 */
	public function getAllSeasons() {
		$table = $this->database->table('season')
				->order('id DESC');
		$seasons = array();
		foreach ($table as $row) {
			$season = new Season($row->id);
			$season->year = $row->year;
			$season->competition = $this->getCompetition($row->competition);
			$season->runnerAge = $row->runner_age;
			$season->guideAge = $row->guide_age;
			$seasons[] = $season;
		}
		return $seasons;
	}
	
	/**
	 * Vrátí objekt soutěže podle zadaného ID
	 * 
	 * @param int $id
	 * @return \Competition
	 * @throws Race\DbNotStoredException
	 */
	public function getCompetition($id) {
		$row = $this->database->table('competition')
				->get($id);
		if(!$row) {
			throw new Race\DbNotStoredException("Soutěž $id neexistuje");
		}
		$competition = new Competition($id);
		$competition->name = $row->name;
		$competition->short = $row->short;
		return $competition;
	}
	
	/**
	 * 
	 * @return int ID výchozího ročníku
	 */
	public function getDefaultSeason() {
		return $this->database->table('setting')
				->get('season')
				->value;
	}
	
	/**
	 * Nastaví výchozí ročník v databázi
	 */
	public function makeDefaultSeason($id) {
		$this->database->table('setting')
				->where('property', 'season')
				->update(array("value" => $id));
	}
}
