<?php

namespace App\Presenters;

use Nette,
	Nette\Application\UI\Form;

/**
 * AdminPresenter
 *
 * @author Jiří Doušek <405245@mail.mini.cz>
 */
class AdminPresenter extends BasePresenter {
	
	/**
	 *
	 * @var \FileRepository
	 * @inject
	 */
	public $fileRepository;

	/**
	 *
	 * @var \AdminRepository
	 * @inject
	 */
	public $adminRepository;
	
	public function startup() {
		parent::startup();
		if (!$this->user->isLoggedIn()) {
			throw new Nette\Security\AuthenticationException("Musíte se přihlásit.");
		}
		if (!$this->user->isInRole('admin')) {
			throw new \Race\PermissionException("Nemáte oprávnění k této akci");
		}
	}

	public function createComponentAddAdminForm() {		
		$form = new Form();
		$form->addProtection();
		$users = $this->userRepository->loadNonAdminUsers();
		$items = array();
		foreach ($users as $user) {
			$items[$user->id] = $user->displayName;
		}
		$form->addGroup("Přídání nových administrátorů");
		$form->addMultiSelect('admin', 'Nový admin: ', $items)				
				->setAttribute('class', 'js-example-basic-multiple');				
		$form->addSubmit('send', "Odeslat");
		$form->onSuccess[] = array($this, 'addAdminFormSucceeded');
		return $form;		
	}
	
	public function addAdminFormSucceeded(Form $form, $values) {
		foreach ($values->admin as $admin) {
			$this->database->table('user')
					->where('id_user', $admin)
					->update(array('is_admin' => TRUE));
		}
		$this->flashMessage("Administrátoři byli nastaveni.");
		$this->redirect('this');
	}	
	
	public function createComponentWhiteListForm() {
		$form = new Form();	
		$form->addProtection();
		$form->addGroup("Přídání nového typu souboru");
		$form->addText('title', "Titulek");
		$form->addText('mime', "MIME type");
		$form->addUpload('icon', 'Ikona:');
		$form->addSubmit('send', "Odeslat");
		$form->onSuccess[] = array($this, 'whiteListFormSucceeded');
		return $form;
	}
	
	public function whiteListFormSucceeded(Form $form, $values) {
		$icon = $values->icon;
		if($icon->isOk() && $icon->getContentType() == "image/png") {			
			$path = str_replace("/", "-", $values->mime) . ".png";		
			$icon->move("./" . \File::ICONDIR . $path);
		} else {
			$path = "default.png";
		}
		$this->database->table('whitelist')
				->insert(array(
					'title' => $values->title,
					'mime' => $values->mime,
					'path' => $path
				));

		$this->flashMessage("Druh souboru byl přidán.");
		$this->redirect('this');
	}	
	
	public function createComponentAddCategoryForm() {
		$form = new Form();		
		$form->addProtection();
		$form->addGroup("Přídání nové kategorie");
		$form->addText('name', "Jméno: ");
		$form->addText('short', "Zkratka: ");
		$form->addCheckbox('article', " články");
		$form->addCheckbox('file', " soubory");
		$form->addCheckbox('question', " otázky");
		$form->addSubmit('send', "Odeslat");
		$form->onSuccess[] = array($this, 'addCategoryFormSucceeded');
		return $form;
	}
	
	public function addCategoryFormSucceeded(Form $form, $values) {
		$this->database->table('category')->insert($values);
	}	
	
	public function createComponentAddSeasonForm() {
		$form = new Form();		
		$form->addProtection();
		$form->addGroup("Založení nového ročníku");
		$form->addText('year', "Rok: ")
				->setType('number');
		
		$competitions = $this->adminRepository->getAllCompetitions();
		
		$items = array();
		foreach ($competitions as $competition) {
			$items[$competition->id] = $competition->name;
		}
		
		$form->addSelect('competition', "Soutěž: ", $items);
		$form->addText('runner_age', "Max. dat. nar. závodníků: ")
				->setType('date');
		$form->addText('guide_age', "Max. dat. nar. rádců: ")
				->setType('date');
		$form->addCheckbox('setDefault', " nastavit jako výchozí");
		$form->addSubmit('send', "Založit");
		$form->onSuccess[] = array($this, 'addSeasonFormSucceeded');
		return $form;
	}
	
	public function addSeasonFormSucceeded(Form $form, $values) {
		$setDefault = $values->setDefault;
		unset($values->setDefault);
		$row = $this->database->table('season')->insert($values);
		if ($setDefault) {
			$this->database->table('setting')
					->where('property', 'season')
					->update(array('value' => $row->id));
		}
	}
	
	public function renderDefault() {
		$this->template->admins = $this->userRepository->getAdmins();		
	}
	
	public function handleDeleteAdmin($id) {
		$user = $this->userRepository->getUser($id);
		$user->admin = FALSE;
		$user->save();
		$this->redrawControl();
	}
	
	public function renderWhitelist() {
		$this->template->filetypes = $this->fileRepository->getFileTypes();		
	}
	
	public function handleDeleteFileType($mime) {
		$this->fileRepository->deleteFileType($mime);
		$this->flashMessage("Druh souboru byl odebrán.");
		$this->redrawControl();
	}
	
	public function renderCategories() {
		$this->template->categories = $this->adminRepository->getAllCategories();		
	}
	
	public function handleDeleteCategory($id) {
		$this->adminRepository->deleteCategory($id);
		$this->flashMessage("Druh souboru byl odebrán.");
		$this->redrawControl();
	}
	
	public function renderSeason() {		
		$this->template->seasons = $this->adminRepository->getAllSeasons();
		$this->template->defaultSeason = $this->adminRepository->getDefaultSeason();		
	}
	
	/**
	 * Nastaví výchozí ročník v databázi
	 */
	public function handleMakeDefaultSeason($id) {
		$this->adminRepository->makeDefaultSeason($id);
		$this->redrawControl();
	}
}
