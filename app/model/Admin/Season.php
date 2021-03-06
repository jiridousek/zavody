<?php

/**
 * Description of Season
 *
 * @author Jiří Doušek <405245@mail.mini.cz>
 */
class Season extends Nette\Object {
	
	/**
	 *
	 * @var int
	 */
	private $id;
	
	/**
	 *
	 * @var int
	 */
	private $year;
	
	/**
	 *
	 * @var \Competition
	 */
	private $competition;
	
	/**
	 *
	 * @var DateTime
	 */
	private $runnerAge;
	
	/**
	 *
	 * @var DateTime
	 */
	private $guideAge;
	
	public function __construct($id) {
		$this->id = $id;
	}
	
	public function getId() {
		return $this->id;
	}

	public function getYear() {
		return $this->year;
	}
	
	public function setYear($year) {
		if (is_int($year)) {
			$this->year = $year;
		} else {
			throw new \Nette\InvalidArgumentException("Rok musí být číslo");
		}
	}
	
	public function getCompetition() {
		return $this->competition;
	}
	
	public function setCompetition(Competition $competition) {		
		$this->competition = $competition;		
	}
	
	public function getRunnerAge() {
		return $this->runnerAge;
	}
	
	public function setRunnerAge(DateTime $age) {		
		$this->runnerAge = $age;		
	}
	
	public function getGuideAge() {
		return $this->guideAge;
	}
	
	public function setGuideAge(DateTime $age) {		
		$this->guideAge = $age;		
	}
}
