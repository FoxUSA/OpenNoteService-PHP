<?php
	namespace model;
	interface IModel{
		
	//Search
		/**
		 * @param search - the string to use to search
		 * @param userID - the userID to search with
		 * @return - the notes that match the search
		 */
		public function searchNotesTitles($search, $userID);
		
		/**
		 * @param search - the string to use to search
		 * @param userID - the userID to search with
		 * @return - the notes that match the search
		 */
		public function searchNotesNotes($search, $userID);
		
		/**
		 * @param search - the string to use to search
		 * @param userID - the userID to search with
		 * @return - the folders that match the search
		 */
		public function searchFolders($search, $userID);
		
	//Note
		/**
		 * Permanently deletes the note from the db
		 * @param noteID- the note ID to delete
		 * @return - the folderID the note was in
		 */
		public function removeNote($note);
		
		/**
		 * @param note - the note to save
		 * @return - saved note
		 */
		public function saveNote($note);
		
		/**
		 * @param id - the id of the note to get
		 */
		public function getNote($id);
	
	//Folder
		/**
         * delete a new folder
         * @param folder - the folder to delete.
         */
		public function removeFolder($folder);
		/**
		 * creates a new folder
		 * @param folder - the folder to save
         * @return - the new folder
		 */
		public function saveFolder($folder);
		
        /**
         * updated a folder
         * @param folder - the folder to update
         * @return - the updated folder
         */
        public function updateFolder($folder);
			
		/**
		 * @param folderID - the folder get
		 * @return - the folder object
		 */
		public function getFolder($folderID);
			 
		/**
		 * @param folderID - the folder to get the childen of
         * @param userID - the usersID
		 * @return - the sub folders
		 */
		public function getSubFolders($folderID, $userID);
			 
		/**
		 * @param folderID - the folder to get the notes from
		 * @param includeNotesNote - include note html field
		 * @return - the notes from the folder
		 */ 
		public function getNotesInFolder($folderID, $includeNotesHTML);
		
	//Authentication		
		/**
		 * Create a token
		 * @param userID - the userID who the token is for
		 * @param ip - the requesters ip
		 * @param token - the token string
		 * @param issueTime - the time the token is valid from
		 * @param expireTime - the time the token is valid to
		 */
		public function createToken($userID, $ip, $token, \DateTime $issueTime, \DateTime $expireTime);
			
		/**
		 * Get token
		 * @param token - token string
		 * @return - return object of \model\dataTypes\Token
		 */
		public function getToken($token);
			
		/**
		 * Invalidate token
		 * @param token - token string
		 */
		public function invalidateToken($token);
			
		/**
		 * Get token
		 * @param id - the token id to get
		 * @return - return object of \model\dataTypes\Token
		 */
		public function getTokenFromID($id);
		
		/**
		 * Get user
		 * @param userName - the username to get the record
		 * @return - return object of \model\dataTypes\User
		 */
		public function getUser($userName);
			
	//File
		/**
		 * @param id - the id to store the file under
		 * @param originalName - the original name and type
		 * @param diskName - the name of the file we stored
		 * @return - the id of the inserted record
		 */
		public function uploadFile($id, $originalName, $diskName, $userID);
			
		/**
		 * Get a uploaded File
		 * @param id - the id of the file to get
		 * @return  - the upload record to get
		 */
		public function getUploadFile($id);
	}	
?>