<?php
	namespace model\dataTypes;
	class Folder{
		public $id;
		public $parrentFolderID;
		public $name;
		public $userID;
        
        public $foldersInside;
        public $notesInside;
	}
?>