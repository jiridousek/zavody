<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form;

/**
 * Description of WatchFormFactory
 *
 * @author Jiří Doušek <405245@mail.mini.cz>
 */
class WatchFormFactory extends BaseFormFactory {
	
	private $watchRepository;
	private $raceRepository;
	private $unitRepository;
	
	/**
	 *
	 * @var Nette\Security\LoggedUser
	 */
	private $user;	
	private $race;
	private $id;
	private $troop;
	private $session;
	
	/**
	 * 
	 * @param \Skautis\Skautis $skautIS
	 * @param \Nette\Database\Context $database
	 * @param \WatchRepository $watchRepository
	 * @param \RaceRepository $raceRepository
	 * @param \UnitRepository $unitRepository
	 * @param \Nette\Security\LoggedUser $loggedUser
	 * @param Nette\Http\Session $session
	 */
	public function __construct(\Skautis\Skautis $skautIS, \Nette\Database\Context $database, \WatchRepository $watchRepository, \RaceRepository $raceRepository, \UnitRepository $unitRepository, \Nette\Security\LoggedUser $loggedUser, Nette\Http\Session $session) {
		parent::__construct($skautIS, $database);
		$this->watchRepository = $watchRepository;
		$this->raceRepository = $raceRepository;
		$this->unitRepository = $unitRepository;
		$this->session = $session;
		$this->user = $loggedUser;		
	}
	
	public function setId($id) {
		$this->id = $id;
	}
	
	public function setRace($race) {
		$this->race = $race;
	}
	
	public function setTroop($troop) {
		$this->troop = $troop;
	}	
	

	/**
	 * 
	 * @return Form
	 */
	public function create() {
		$form = new Form;
		$form->addProtection();
		if(is_null($this->id)) {
			$form->addSelect("race", "Závod *: ", $this->loadRaces())
				->setPrompt("-- vyber závod --")
				->setDefaultValue($this->race)
				->setRequired('Vyber prosím závod.');
			$form->addSelect("troop", "Středisko *:", $this->loadTroops())
				->setPrompt('-- vyber středisko --')
				->setAttribute('class', 'js-example-basic-single')
				->setRequired('Vyber prosím středisko');
			$form->addSelect("group", "Oddíl *:", $this->loadGroups())
				->setPrompt('-- vyber oddíl --')
				->setAttribute('class', 'js-example-basic-single');			
		}
		$form->addHidden("author", $this->user->getUserDetail()->ID);
		$form->addText("name", "Název hlídky *:")
			->setRequired("Je nutné vyplni název hlídky.");
		$form->addText("town","Obec:");
		$form->addText("email_leader", "E-mail na vůdce oddílu *:")
			->setRequired("Je nutné vyplnit kontakt na vůdce oddílu.")
			->addRule(Form::EMAIL, "E-mailová adresa není platná");
		$form->addText("email_guide", "E-mail na rádce družiny:")
			->addRule(Form::EMAIL, "E-mailová adresa není platná");
		$form->addSubmit("send","Další krok >>");
		$form->addSubmit("save","Uložit změny");
		$form->addSubmit("cancel","Zrušit přihlašování")
			->setValidationScope(FALSE);
		
		$form->onSuccess[] = array($this, 'formSucceeded');
		$form->onValidate[] = array($this, 'validateGroup');
		return $form;
	}
	
	public function validateGroup($form) {
		if (!$this->id) {
			$values = $form->getHttpData();
			if (empty($values["group"])) {			
				$form->addError("Je nutné vyplnit oddíl.");
			}
		}
	}


	public function formSucceeded(Form $form) {	
		if ($form["cancel"]->isSubmittedBy()) {
			$this->session->getSection("watch")->remove();
			$form->getPresenter()->redirect("Race:");
		}
		$values = $form->getHttpData();
		unset($values["_token_"]);
		if ($this->id) {
			$row = $this->database->table('watch')
					->where('id', $this->id);
			unset($values["save"]);
			unset($values["do"]);
			unset($values["author"]);
			$row->update($values);			
		} else {			
			$section = $this->session->getSection('watch');
			$section->basic = $values;		
		}
	}

	public function loadRaces() {
		$tmp = $this->raceRepository->getRacesToLogIn($this->season);
		$races = array();
		foreach ($tmp as $race) {
			$races[$race->id] = $race->name;
		}
		return $races;
	}

	public function loadTroops() {
		$unit = $this->user->getUnit();
		if ($unit->unitType == "oddil") {
			$idParent = $unit->unitParent->id;
		} else {
			$idParent = $unit->id;
		}
		$units = $this->unitRepository->getUnits(array("stredisko"), $idParent);
		$troops = array();
		foreach ($units as $unit) {
			$troops[$unit->id] = "$unit->registrationNumber - $unit->displayName";
		}
		return $troops;
	}
	
	public function loadGroups() {		
		if (isset($this->troop)) {			
			$units = $this->troop->getSubordinateUnits();
			$groups = array();
			foreach ($units as $unit) {
				$groups[$unit->id] = "$unit->registrationNumber - $unit->displayName";
			}
			return $groups;
		} else {
			return NULL;
		}
	}
	
}
