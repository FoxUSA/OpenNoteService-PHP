<?php
	namespace model\dataTypes;
	class Folder{
		public $id;
		public $parentFolderID;
		public $name;
		public $userID;
        
        public $foldersInside;
        public $notesInside;
	}
?>