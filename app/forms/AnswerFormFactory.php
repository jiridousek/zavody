<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form;

/**
 * AnswerFormFactory
 * 
 * Továrna na formuláře na odpovědi k dotazům
 *
 * @author Jiří Doušek <405245@mail.mini.cz>
 */
class AnswerFormFactory extends BaseFormFactory {
	
	private $questionRepository;	
	
	private $question;
	
	/**
	 * 
	 * @param \Skautis\Skautis $skautIS
	 * @param \Nette\Database\Context $database
	 * @param \QuestionRepository $questionRepository
	 */
	public function __construct(\Skautis\Skautis $skautIS, \Nette\Database\Context $database, \QuestionRepository $questionRepository) {
		parent::__construct($skautIS, $database);
		$this->questionRepository = $questionRepository;
	}
	
	/**
	 * Nastaví otázku, ke které se odpověď vztahuje
	 * 
	 * @param int $questionId
	 */
	public function setQuestion($questionId) {
		$this->question = $questionId;
	}
	
	/**
	 * @return Form
	 */
	public function create()
	{			
		$form = new Form;	
		
		$form->addTextArea('text', '', 30, 2)
			->setRequired('Je nutné vyplnit text odpovědi.');	

		$form->addSubmit('send', 'Odpovědět');

		$form->onValidate[] = array($this, 'formSucceeded');
		
		$renderer = $form->getRenderer();
		$this->addBootstrapRendering($renderer, $form);
		
		return $form;
	}
	
	public function formSucceeded(Form $form, $values)
	{		
		if(is_null($this->question)) {
			throw new Nette\InvalidArgumentException("Odpověď musí být vedená k nějakému dotazu");
		}
		$user = $this->skautIS->usr->UserDetail()->ID;
		$data = array(
			'question' => $this->question,
			'text' => $values->text,
			'posted' => date("Y-m-d H:i:s"),
			'author' => $user
		);
		// aktualizace dotazu pro řazení podle poslední změny
		$this->database->table('answer')->insert($data);	
		$this->database->table('question')
				->where('id', $this->question)
				->update(array('changed' => date("Y-m-d H:i:s")));
	}
	
}
