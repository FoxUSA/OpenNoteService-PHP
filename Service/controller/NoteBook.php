<?php
	/**
	 *	Project name: OpenNote
	 * 	Author: Jacob Liscom
	 *	Version: 13.2.0
	**/
	
	//OO code
	namespace controller;
	abstract class NoteBook{
		//Note
			/**
			 * Get a note
	         * @param model - model to use
			 * @param token - server token to use
			 * @param id - note to retrieve
			 */
			public static function getNote(\model\IModel $model, \model\dataTypes\Token $token, $id){
				$note = $model->getNote($id);
				
				if(!self::tokenOwnsOriginNote($model, $token, $note))
					throw new \controller\ServiceException("Not authorized",401);
					
				return $note;
			}
	        
	        /**
	         * Save a note
	         * @param model - the model to use
	         * @param token - the server token
	         * @param note - the note to save
	         */
	        public static function saveNote(\model\IModel $model, \model\dataTypes\Token $token, $note){
	            $note->id = null; //ignore the id if its passed in
	            $note->userID = $token->userID;//force it to be saved under the current user
	            
	            if($note->folderID==null)//notes have to be in folder
	                throw new \controller\ServiceException("FolderID cant be null",412);
	            
	            $folder=$model->getFolder($note->folderID);
	            
	            if(!self::tokenOwnsOriginNote($model, $token, $note)|| !self::tokenOwnsFolder($model, $token, $folder)) //they have to own the origin note and the folder
	                throw new \controller\ServiceException("Unauthorized",401);
	            
	            //TODO move note logic
	            //see if origin note and new note have the same folder id
	            //if they dont chang the note history to the new folder
	            
	            $note=$model->saveNote($note);//save the note
	            
	            return $note;
	        }
	        
	        /**
	         * Delete a note and history
	         * @param model - the model to use
	         * @param token - the server token
	         * @param note - the note to delete
	         */
	        public static function removeNote(\model\IModel $model, \model\dataTypes\Token $token, $note){
	            if(!self::tokenOwnsNote($model, $token, $note)) //they have to own the note
	                throw new \controller\ServiceException("Unauthorized",401);
	            
	            if($note->originNoteID==null&&$note->id==null)
	                throw new \controller\ServiceException("Must have a vaid note",422);
	            
	            $model->removeNote($note);
	        }
	        
	        /**
	         * Make sure user owns both the origin note and the note
	         * @param model - the model to use
	         * @param token - the server token to use
	         * @param note - the note to see if the token owns
	         * @return - true if users owns the notes
	         */
	        private static function tokenOwnsNote(\model\IModel $model, \model\dataTypes\Token $token, $note){
	            $serverNote=$model->getNote($note->id);//just make sure we can get it and no exception is thrown
	            
	            if($token->userID!=$serverNote->userID&&!!self::tokenOwnsOriginNote($model,$token,$note)) //they have to own the note
	                return fale;
	            return true;
	        }
	        
	        /**
	         * Make sure user owns the origin note
	         * @param model - the model to use
	         * @param token - the server token to use
	         * @param note - the note to see if the token owns
	         * @return - true if users owns the note
	         */
	        private static function tokenOwnsOriginNote(\model\IModel $model, \model\dataTypes\Token $token, $note){
	            if($note->originNoteID==null)//everyone owns null
	                return true;
	            
	            $originNote=$model->getNote($note->originNoteID);//just make sure we can get it and no exception is thrown
	            
	            if($token->userID!=$originNote->userID) //they have to own the note
	                return fale;
	            return true;
	        }
	        
	        
		//Folder
		   /**
	         * Make sure user owns the folder
	         * @param model - the model to use
	         * @param token - the server token to use
	         * @param folder - the folder to see if the token owns
	         * @return - true if users owns the folder
	         */
	        private static function tokenOwnsFolder(\model\IModel $model, \model\dataTypes\Token $token, $folder){
	            if($folder->id==null)//everyone can have a folder in null
	                return true;
	            
	            $serverFolder=$model->getFolder($folder->id, $token->userID);
	        
	            if($token->userID!=$serverFolder->userID || !self::tokenOwnsParrentFolder($model, $token, $folder)) //the have to own the folder
	                return false;
	            return true;            
	        }
	        
	        /**
	         * Make sure user owns the folder
	         * @param model - the model to use
	         * @param token - the server token to use
	         * @param folder - the folder to see if the token owns
	         * @return - true if users owns the folder
	         */
	        private static function tokenOwnsParrentFolder(\model\IModel $model, \model\dataTypes\Token $token, $folder){
	            if($folder->parrentFolderID==null)//everyone can have a folder in null
	                return true;
	            
	            $serverFolder=$model->getFolder($folder->parrentFolderID, $token->userID);
	        
	            if($token->userID!=$serverFolder->userID) //the have to own the folder
	                return false;
	            return true;            
	        }
	        
	        /**
	         * @param model - the model to use
	         * @param token - server token to use
	         * @param id - the id of the folder to get
	         * @param recersiveLevels - number of levels to fetch
	         * @param includeNotes - include notes in tree
	         * @param includeNotesNote - include note html in note
	         */
	        public static function getFolder(\model\IModel $model, \model\dataTypes\Token $token, $id = null, $recersiveLevels=1, $includeNotes =false , $includeNotesHTML = true){
	            if($id!=null){//get the root
	                $folder=$model->getFolder($id);
	                
	                if(!self::tokenOwnsFolder($model, $token, $folder)) //they have to own the origin note and the folder
	                    throw new \controller\ServiceException("Unauthorized",401);          
	            }
	            else{//create a folder to use as the root
	                $folder = new \model\dataTypes\Folder();
	                $folder->name = "Home";
	                $folder->uerID = $token->userID;
	            }
	                
	            if($recersiveLevels>=1){//get stuff in our folder
	                $folder->foldersInside = $model->getSubFolders($folder->id, $token->userID);//get whats in our folder
	                if($includeNotes)
	                	$folder->notesInside = $model->getNotesInFolder($folder->id, $includeNotesHTML);
	            }
	            
	            if($recersiveLevels>=2)//get stuff in our folders folders
	                for($i=0;$i<count($folder->foldersInside);$i++)
	                    $folder->foldersInside[$i] = self::getFolder($model,$token, $folder->foldersInside[$i]->id, $recersiveLevels-1, $includeNotes, $includeNotesHTML); //get sub content
	                
	            return $folder;
	         
	        }
	        
	        /**
	         * Create a new folder
	         * @param model - the model to use
	         * @param token - server token to use
	         * @param folder - the folder to save
	         * @return - the folder record
	         */
	        public static function saveFolder(\model\IModel $model, \model\dataTypes\Token $token, $folder){
	            $folder->id = null;
	            $folder->userID = $token->userID;//force it to be saved under the current user
	            $folder->foldersInside = null;//do not allow saving of a tree
	            $folder->notesInside = null;
	            
	            if(!self::tokenOwnsParrentFolder($model, $token, $folder))
	                throw new \controller\ServiceException("Unauthorized",401);
	            
	            $folder = $model->saveFolder($folder);
	            
	            return $folder;
	        }
	        
	        /**
	         * Create a new folder
	         * @param model - the model to use
	         * @param token - server token to use
	         * @param folder - the folder to update
	         * @return - the folder record
	         */
	        public static function updateFolder(\model\IModel $model, \model\dataTypes\Token $token, $folder){
	            $folder->userID = $token->userID;//force it to be saved under the current user
	            $folder->foldersInside = null;//do not allow saving of a tree
	            $folder->notesInside = null;
	            
	            if($folder->id==null)
	                throw new \controller\ServiceException("id cannot be null");
	            
	            if(!self::tokenOwnsFolder($model, $token, $folder))
	                throw new \controller\ServiceException("Unauthorized",401);
	            
	            $folder = $model->updateFolder($folder);
	            
	            return $folder;
	        }
	        
	        /**
	         * Delete the folder
	         * @param model - the model to use
	         * @param token - server token to use
	         * @param folder - the folder to delete
	         */
	        public static function removeFolder(\model\IModel $model, \model\dataTypes\Token $token, $folder){     
	             if($folder->id==null)
	                throw new \controller\ServiceException("id cannot be null");
	            
	            if(!self::tokenOwnsFolder($model, $token, $folder))
	                throw new \controller\ServiceException("Unauthorized",401);
	            
	            $model->removeFolder($folder);
	        }	
	}
?>
