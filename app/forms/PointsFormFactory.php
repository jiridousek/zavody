<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form,
	Nette\Security\User;

/**
 * Description of PointsFormFactory
 *
 * @author Jiří Doušek <405245@mail.mini.cz>
 */
class PointsFormFactory extends BaseFormFactory {
	
	private $race;
	private $watchs;
	
	private $watchRepository;
	private $raceRepository;
	
	/**
	 * 
	 * @param \Skautis\Skautis $skautIS
	 * @param Nette\Database\Context $database
	 * @param WatchRepository $watchRepository
	 * @param WRaceRepository $raceRepository
	 */
	public function __construct(\Skautis\Skautis $skautIS, Nette\Database\Context $database, \WatchRepository $watchRepository, \RaceRepository $raceRepository) {
		parent::__construct($skautIS, $database);
		$this->watchRepository = $watchRepository;
		$this->raceRepository = $raceRepository;
	}
	
	public function setRace($race) {
		$this->race = $race;
	}	
	
	public function setWatchs($watchs) {
		$this->watchs = $watchs;
	}

	/**
	 * @return Form
	 */
	public function create()
	{	
		
		$form = new Nette\Application\UI\Form();
		$form->addSubmit('send', 'Uložit a odeslat ke schválení veliteli');
		$form->onSuccess[] = array($this, 'formSucceeded');

		$watchs = ($this->watchs) ? $this->watchs : array();
		foreach ($watchs as $watch) {
			$form->addText($watch->id)
				->addRule(\Nette\Forms\Form::FLOAT, 'Musí být číselná hodnota')
				->setAttribute('size', 5)
				->setDefaultValue($watch->getPoints($this->race));
			if ($this->watchRepository->getAdvance($watch->id, $this->race) === 0) {
				$noAdvance = TRUE;
			} else {
				$noAdvance = FALSE;
			}
			$form->addCheckbox("noAdvance" . $watch->id)
				->setDefaultValue($noAdvance);
			$form->addTextArea("note" . $watch->id)
				->setDefaultValue($watch->getNote($this->race));
			
		}
		
		$renderer = $form->getRenderer();
		$this->addBootstrapRendering($renderer, $form);
		
		return $form;
	}

	public function formSucceeded(Form $form)
	{
		$values = $form->getHttpData();			
		$race = $this->raceRepository->getRace($this->race);
		$race->deleteAdvancedWatchs();		
		unset($values["send"]);
		unset($values["do"]);		
		$femaleOrder=0;
		$maleOrder=0;
		$advancedMale=0;
		$advancedFemale=0;
		$data = array();
		$points = array();
		foreach ($values as $key => $value) {
			if (substr($key, 0, 1) === '_') {
				$index = substr($key, 1);
				$data[$index]["index"] = $index;
				$data[$index]["points"] = $value;
				$data[$index]["note"] = $values["note" . $index];
				if (isset($values["noAdvance" . $index])) {
					$data[$index]["noAdvance"] = TRUE;
				} else {
					$data[$index]["noAdvance"] = FALSE;
				}
				$points[$index] = $value;
			}
		}	
		array_multisort($points, SORT_DESC, $data);
		foreach ($data as $result) {			 
			 $watch = $this->watchRepository->getWatch($result["index"]);
			 $category = $watch->getCategory();
			 $order = null;
			 $advance = NULL;
			 if ($category == \Watch::CATEGORY_FEMALE) {
				 $femaleOrder++;
				 $order = $femaleOrder;				 
				 if ($advancedFemale < $race->getNumAdvance(\Watch::CATEGORY_FEMALE) && !($result["noAdvance"])) {
					 $advance = TRUE;
					 $advancedFemale++;
				 }
				
			}
			if ($category == \Watch::CATEGORY_MALE) {
				 $maleOrder++;
				 $order = $maleOrder;
				 if ($advancedMale < $race->getNumAdvance(\Watch::CATEGORY_MALE) && !($result["noAdvance"])) {
					 $advance = TRUE;
					 $advancedMale++;
				 }
			 }
			 $watch->fixCategory();
			 if ($advance) {				 
				 $watch->processAdvance($race);
			 }
			 if($result["noAdvance"])	{
				 $advance = FALSE;
			 }
			 $this->database->table('race_watch')
					 ->where('race_id', $this->race)
					 ->where('watch_id', $result["index"])
					 ->update(array(
						 "points" => $result["points"],
						 "order" => $order,
						 "advance" => $advance,
						 "note" => $result["note"]
						));			 
		}		
	}	
}
