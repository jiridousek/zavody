<?php

namespace App\Forms;

use Nette\Forms\Controls,
	Nette\Application\UI\Form;
	

/**
 * QuestionFormFactory
 * 
 * Továrna na formuláře pro pokládání otázek
 *
 * @author Jiří Doušek <405245@mail.mini.cz>
 */
class QuestionFormFactory extends BaseFormFactory {
	
	private $questionRepository;
	
	private $adminOnly;
	private $race;
	private $id;
	
	/**
	 * 
	 * @param \Skautis\Skautis $skautIS
	 * @param \Nette\Database\Context $database
	 * @param \QuestionRepository $questionRepository
	 */
	public function __construct(\Skautis\Skautis $skautIS, \Nette\Database\Context $database, \QuestionRepository $questionRepository) {
		parent::__construct($skautIS, $database);
		$this->questionRepository = $questionRepository;
		$this->race = NULL;
		$this->adminOnly = 0;
	}
	
	public function setAdminOnly($adminOnly) {
		$this->adminOnly = $adminOnly;
	}
	
	public function setRace($race) {
		$this->race = $race;
	}
	
	/**
	 * @return Form
	 */
	public function create()
	{		
		//vytvoření seznamu kategorií jako vlastní komponenta
		$categories = $this->questionRepository->getAllCategories('question');		
		$items = array();
		foreach ($categories as $category) {
			$items[$category->id] = $category->name;
		}
		$checkboxList = new Controls\MyCheckboxList();
		$checkboxList->setItems($items);
		
		$form = new Form;		
		$form->addGroup('Nová otázka');
		
		$form->addComponent($checkboxList, 'categories');		
		$form->addTextArea('text', 'Otázka:')
			->setRequired('Je nutné vyplnit text otázky.');
		$form->addHidden('admin_only', $this->adminOnly);
		$form->addHidden('race', $this->race);
		$form->addSubmit('send', 'Uložit');

		$form->onSuccess[] = array($this, 'formSucceeded');
		
		$renderer = $form->getRenderer();
		$this->addBootstrapRendering($renderer, $form);
		
		return $form;
	}
	
	public function formSucceeded(Form $form, $values)
	{		
		$values->race = $values->race ?: NULL;		
		$user = $this->skautIS->usr->UserDetail()->ID;
		$data = array(
			'season' => $this->season,
			'text' => $values->text,
			'posted' => date("Y-m-d H:i:s"),
			'changed' => date("Y-m-d H:i:s"),
			'author' => $user,
			'admin_only' => $values->admin_only,
			'race' => $values->race,
		);
		$question = $this->database->table('question')->insert($data);
		$this->updateCategories($values->categories, $question);					
	}	
	
	/**
	 * Smaže případné staré kategorie otázky a uloží nové
	 * 
	 * @param array $categories
	 * @param Question $question
	 */
	private function updateCategories($categories, $question) {
		//případné smazání
		if (!is_null($this->id)) {
			$this->database->table('category_question')
				->where('question_id',  $this->id)
				->delete();
		}
		//uložení nových kategorií
		foreach ($categories as $category) {
			$this->database->table('category_question')
				->insert(array(
					'category_id' => $category,
					'question_id' => $question->id
				));
		}
	}
}
