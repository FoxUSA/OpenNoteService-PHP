<?php	
	namespace model\pdo;
	class Model implements \model\IModel{		

	//Helper methods
		/**
		 * Take a result set and map it to note classes
		 * @param $result - results from notes query
		 * @param includeNotesHTML - flag to include the html decode
		 * @return - array of notes
		 */
		private function noteListFactory($results, $includeNotesHTML){
			$notes = array();
			
			foreach($results as $result){
				$note = new \model\dataTypes\Note();
				$note->folderID=$result["folderID"];;
				$note->id=$result["id"];
				$note->title=html_entity_decode($result["title"], ENT_QUOTES);
					
				if($includeNotesHTML)
					$note->note=html_entity_decode($result["note"]);//de-scape note
					
				$note->originNoteID=$result["originNoteID"];
				$note->userID = $result["userID"];
				$note->dateCreated =$result["dateCreated"];
					
				$notes[] = $note;
			}
			return $notes;
		}	
		
		/**
		 * Take a result set and map it to folder classes
		 * @param result - results from forder query
		 * @return - array of folders
		 */
		private function folderListFactory($results){
			$returnArray = array();
			
			foreach ($results as $result) {
				$folder = new \model\dataTypes\Folder();
				$folder->id=$result["id"];
				$folder->parrentFolderID=$result["parrentFolderID"];
				$folder->name=html_entity_decode($result["name"], ENT_QUOTES);
				$folder->userID=$result["userID"];
			
				$returnArray[] = $folder; //add item to return array
			}
			return $returnArray;
		}
		
	//Search
		/**
		 * @param search - the string to use to search
		 * @param userID - the userID to search with
		 * @return - the notes that match the search
		 */
		public function searchNotesTitles($search, $userID){
			$results=Core::query("	SELECT 	n.id,
											n.title, 
											n.note, 
											n.originNoteID, 
											n.folderID, 
											n.userID, 
											n.dateCreated 
									FROM note n
									WHERE 	n.title LIKE ? 
										AND (n.originNoteID IS NULL OR n.id IN (SELECT MAX(id) FROM note WHERE originNoteID=n.originNoteID))
										AND (SELECT COUNT(*) FROM note WHERE originNoteID = n.id)=0
										AND userID=?
									ORDER BY n.title",
				array(sprintf("%%%s%%",$search),$userID));
			
			return self::noteListFactory($results, false);
		}
		
		/**
		 * @param search - the string to use to search
		 * @param userID - the userID to search with
		 * @return - the notes that match the search
		 */
		public function searchNotesNotes($search, $userID){
			$results=Core::query("	SELECT 	n.id,
											n.title,
											n.note,
											n.originNoteID,
											n.folderID,
											n.userID,
											n.dateCreated
									FROM note n
									WHERE 	n.note LIKE ?
										AND (n.originNoteID IS NULL OR n.id IN (SELECT MAX(id) FROM note WHERE originNoteID=n.originNoteID))
										AND (SELECT COUNT(*) FROM note WHERE originNoteID = n.id)=0
										AND userID=?
									ORDER BY n.title",
					array(sprintf("%%%s%%",$search),$userID));
				
			return self::noteListFactory($results, false);
		}
		
		/**
		 * @param search - the string to use to search
		 * @param userID - the userID to search with
		 * @return - the folders that match the search
		 */
		public function searchFolders($search, $userID){
			$returnArray = array();
			return Core::query("SELECT 	id, 
										parrentFolderID, 
										name, 
										userID 
								FROM folder 
								WHERE 	name LIKE ? 
									AND userID=? 
								ORDER BY name",array(sprintf("%%%s%%",$search), $userID));
			
			return self::folderListFactory($result);
		}
		
	//Note		
		/**
		 * Permanently deletes the note from the db
		 * @param note - the note to delete
		 */
		public function removeNote($note){
		    $id = $note->originNoteID;
            
		    if($id==null)
                $id=$note->id;
            	
    		Core::query("DELETE FROM note WHERE originNoteID=?;",array($id));//delete the history
    		Core::query("DELETE FROM note WHERE id=?;",array($id));//delete the origin note
		}
		
		/**
		 * @param note - the note to save
		 * @return - the new note
		 */
		public function saveNote($note){
			Core::query("INSERT INTO note (folderID, originNoteID,title,note,userID) VALUES(?,?,?,?,?);",
				array($note->folderID,$note->originNoteID,htmlentities($note->title),htmlentities($note->note), $note->userID));//parse out for mysql use
				
			return self::getNote(Core::getInsertID());
		}
		
		/**
		 * @param folderID - the note the folder is in
		 * @param id - the id of the note to get
		 */
		public function getNote($id){
			$result = Core::query("SELECT title, note, originNoteID, folderID, userID, dateCreated FROM note WHERE id = ?;",array($id));//get the note
			if(count($result)==1){
				$note = new \model\dataTypes\Note();
				$note->folderID=$result[0]["folderID"];;
				$note->id=$id;
				$note->title=html_entity_decode($result[0]["title"], ENT_QUOTES);
				$note->note=html_entity_decode($result[0]["note"], ENT_QUOTES);//de-scape note
				$note->originNoteID=$result[0]["originNoteID"];
				$note->userID = $result[0]["userID"];
                $note->dateCreated = $result[0]["dateCreated"];
                
				return $note;
			}
			else
				throw new \controller\ServiceException("No note found",404);
		}
		
	//Folder		
		/**
		 * delete a new folder
		 * @param folder - the folder to delete.
		 */
		public function removeFolder($folder){
			Core::query("DELETE FROM folder WHERE id=?;",array($folder->id));
		}
		
		/**
         * creates a new folder
         * @param folder - the folder to save
         * @return - the new folder
         */
		public function saveFolder($folder){
            Core::query("INSERT INTO folder(parrentFolderID, name, userID) VALUES(?,?,?)",array($folder->parrentFolderID,htmlentities($folder->name),$folder->userID));
            return $this->getFolder(Core::getInsertID());
        }
		
        /**
         * updated a folder
         * @param folder - the folder to update
         * @return - the updated folder
         */
        public function updateFolder($folder){
            Core::query("UPDATE folder SET 
                            parrentFolderID = ?, 
                            name = ?
                         WHERE id = ?", 
                         array( $folder->parrentFolderID,
                                htmlentities($folder->name),
                                $folder->id));
            return $this->getFolder($folder->id);
        }
        
		/**
		 * @param folderID - the folder to get
		 * @return - the folder content
		 */
		public function getFolder($folderID){
			$result = Core::query("SELECT id, parrentFolderID, name, userID FROM folder WHERE id = ?",array($folderID));

            if(count($result)==1){
                $folder = new \model\dataTypes\Folder();
                $folder->id = $result[0]["id"];
                $folder->parrentFolderID = $result[0]["parrentFolderID"];
                $folder->name = html_entity_decode($result[0]["name"], ENT_QUOTES);
                $folder->userID = $result[0]["userID"];
                return $folder;
            }
            else
                throw new \controller\ServiceException("Not found",404);
		}
		 
		/**
		 * @param folderID - the folder to get the childen of
         * @param userID - the usersID
		 * @return - the sub folders
		 */
		public function getSubFolders($folderID, $userID){
		    $returnArray = array();
		    if($folderID!=null)
		        $results = Core::query("SELECT id, parrentFolderID, name, userID FROM folder WHERE parrentFolderID = ? ORDER BY name", array($folderID));
            else 
                $results = Core::query("SELECT id, parrentFolderID, name, userID FROM folder WHERE parrentFolderID IS NULL AND userID=? ORDER BY name", array($userID));
            
            return self::folderListFactory($results);
		}
		 
		/**
		 * @param folderID - the folder to get the notes from
		 * @return - the notes from the folder
		 */ 
		public function getNotesInFolder($folderID, $includeNotesHTML = false){
		    if($folderID==null)//cant be null
                return;
            
			$results = Core::query(" SELECT  n.id, 
			                                 n.title, 
			                                 n.folderID,
			                                 n.note,
			                                 n.originNoteID,
			                                 n.userID,
			                                 n.dateCreated
					           		 FROM note n 
									 WHERE 	n.folderID = ? 
									   AND (n.originNoteID IS NULL OR n.id IN (SELECT MAX(id) FROM note WHERE originNoteID=n.originNoteID)) 
									   AND (SELECT COUNT(*) FROM note WHERE originNoteID = n.id)=0
									ORDER BY n.title", 
									array($folderID));//basically get notes that id is null and have not been overwritten or are the latest
			
			return self::noteListFactory($results, $includeNotesHTML);					
		}
			
	//Authentication		
		/**
		 * Create a token
		 * @param userID - the userID who the token is for
		 * @param ip - the requesters ip
		 * @param token - the token string
		 * @param issueTime - the time the token is valid from
		 * @param expireTime - the time the token is valid to
		 */
		public function createToken($userID, $ip, $token, \DateTime $issueTime, \DateTime $expireTime){
			Core::query("INSERT INTO token(userID,ip,token,issued, expires) VALUES(?,?,?,?,?)",array($userID, $ip, $token, $issueTime->format("Y-m-d H:i:s"), $expireTime->format("Y-m-d H:i:s")));//Create a token record
			return $this->getTokenFromID(Core::getInsertID());
				
		}
		
		/**
		 * Get token
		 * @param token - token string
		 * @return - return object of \model\dataTypes\Token
		 */
		public function getToken($token){
			$result = Core::query("SELECT id, userID, ip, token, issued, expires FROM token WHERE token = ?;",array($token));//get the note
			
			if(count($result)==0)
				throw new \Exception("Could not find token");
			
			if(count($result)>1)
				throw new \Exception("Found more than one token");
			
			$token = new \model\dataTypes\Token();
				$token->id = $result[0]["id"];
				$token->userID = $result[0]["userID"];
				$token->ip = $result[0]["ip"];
				$token->token = $result[0]["token"];
				$token->issued = $result[0]["issued"];
				$token->expires = $result[0]["expires"];
			
			return $token;
		}
		
		/**
		 * Get token
		 * @param id - the token id to get
		 * @return - return object of \model\dataTypes\Token
		 */
		public function getTokenFromID($id){
			$result = Core::query("SELECT id, userID, ip, token, issued, expires FROM token WHERE id = ?;",array($id));//get the note
			
			if(count($result)==0)
				throw new \Exception("Could not find token");
			
			if(count($result)>1)
				throw new \Exception("Found more than one token");
			
			$token = new \model\dataTypes\Token();
				$token->id = $result[0]["id"];
				$token->userID = $result[0]["userID"];
				$token->ip = $result[0]["ip"];
				$token->token = $result[0]["token"];
				$token->issued = $result[0]["issued"];
				$token->expires = $result[0]["expires"];
			
			return $token;
		}
		
		/**
		 * Get user
		 * @param userName - the username to get the record
		 * @return - return object of \model\dataTypes\User
		 */
		public function getUser($userName){
			$result = Core::query("SELECT id, userName, password FROM users WHERE userName = ?",array($userName));
			
			if(count($result)==0)
				throw new \Exception("Could not find user");
			
			if(count($result)>1)
				throw new \Exception("Found more than one user");
			
			$user = new \model\dataTypes\User();
				$user->id = $result[0]["id"];
				$user->userName = $result[0]["userName"];
				$user->password = $result[0]["password"];
				
			return $user;
		}
		
		/**
		 * Create a user
		 * @param \model\dataTypes\User $user - the partual user record to create
		 * @return \model\dataTypes\User - the user with db ID
		 */
		public function createUser(\model\dataTypes\User $user){
			$result = Core::query("INSERT INTO users(userName, password) VALUES(?,?);", array($user->userName, $user->password));
			$user->id = Core::getInsertID();
			return $user;
		}
		
		/**
		 * Invalidate token
		 * @param token - token string
		 */
		public function invalidateToken($token){
			Core::query("DELETE FROM token WHERE token = ?;",array($token));//get the note
		}
		
	//Authorization
		/**
		 * checks if the user owns the note
		 * @param noteID - the noteid to check and see if the user owns it
		 * @return - true if the user owns the note
		 */
		private function doesUserOwnNote($noteID, $userID){
			if($noteID==null)
				return TRUE;
				
			$note = Core::query("SELECT id FROM note WHERE id = ? AND userID = ?;",array($noteID, $userID)); 
			return count($note)==1;
		}
		
		/**
		 * checks if the user owns the folder
		 * @param folderID - the folderID to check and see if the user owns
		 * @return - true if the user owns the folderID
		 */
		private function doesUserOwnFolder($folderID, $userID){
			if($folderID==null)//it can be null. If it isnt make sure we own the folder
				throw new \Exception("doesUserOwnFolder function cannot accept a null folderID.");
				
			$ownsNewFolder=Core::query("SELECT id FROM folder WHERE id = ? AND userID = ?;",array($folderID, $userID));
				
			return count($ownsNewFolder)==1;
		}
		
	//File
		/**
		 * @param id - the id of the file to get
		 * @return  - the upload record to get
		 */
		public function getUploadFile($id){
			return Core::query("SELECT originalName, diskName, userID FROM uploads WHERE id=?;",array($id));
		}
		
		/**
		 * @param id - the id for the file
		 * @param originalName - the original name and type
		 * @param diskName - the name of the file we stored
		 * @return - the id of the inserted record
		 */
		public function uploadFile($id, $originalName, $diskName, $userID){
			Core::query("INSERT INTO uploads(id, originalName, diskName, userID) VALUES(?, ?,?,?);",array($id,$originalName,$diskName, $userID));
			return $id;
		}
	}	
?>