<?php

/**
 * FileRepository
 *
 * @author Jiří Doušek <405245@mail.mini.cz>
 */
class FileRepository {
	
	const BASEDIR = "../files/";
	
	private $dbMapper;	
	
	/**
	 * 
	 * @param FileDbMapper $dbMapper
	 */
	public function __construct(FileDbMapper $dbMapper) {
		$this->dbMapper = $dbMapper;
	}
	
	public function getFile($id) {	
		$file = $this->dbMapper->getFile($id);
		$file->repository = $this;
		return $file;
	}
	
	public function getFiles($paginator, $category = NULL) {
		return $this->dbMapper->getFiles($this, $paginator, $category);
	}
	
	public function getFilesByAuthor($paginator, $userId) {
		return $this->dbMapper->getFilesByAuthor($this, $paginator, $userId);
	}
	
	public function getWhiteList() {
		return $this->dbMapper->getWhiteList();
	}
	
	public function getFileTypes() {
		return $this->dbMapper->getFileTypes();
	}

	public function getAllCategories($type) {
		return $this->dbMapper->getAllCategories($type);
	}
	
	public function deleteFileType($mime) {
		$this->dbMapper->deleteFileType($mime);
	}
	
	public function getCategoriesByFile($id) {
		return $this->dbMapper->getCatogoriesByFile($id);
	}	
		
	public function deleteFile($id) {
		//smaže soubor fyzicky v souborovém systému
		unlink($this->getPath($id));
		//vymaže záznam z databáze
		$this->dbMapper->deleteFile($id);
	}
	
	public function countAll($category = NULL) {
		return $this->dbMapper->countAll($category);
	}
	
	public function countAllAuthor($userId) {
		return $this->dbMapper->countAllAuthor($userId);
	}
	
	public function getPath($id) {
		$path = $this->getFile($id)->getPath();
		return self::BASEDIR . $path;
	}
	
}
