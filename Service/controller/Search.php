<?php
	/**
	 *	Project name: OpenNote
	 * 	Author: Jacob Liscom
	 *	Version: 13.2.0
	**/
	
	//OO code
	namespace controller;
	abstract class Search{
		public static function searchRequest(\model\IModel $model, \model\dataTypes\Token $token, $searchRequest){
			$result = new \model\dataTypes\Folder();
			
			$searchQuery = $searchRequest->search;
			$userID = $token->userID;
			
			switch($searchRequest->type){
				case "Both":
					switch($searchRequest->field){
						case "Both":
							$result->notesInside=array_merge(	$model->searchNotesTitles($searchQuery, $userID),
																$model->searchNotesNotes($searchQuery, $userID));
							$result->foldersInside = $model->searchFolders($searchQuery, $userID);
							break;
							
						case "Title":
							$result->notesInside=$model->searchNotesTitles($searchQuery, $userID);
							$result->foldersInside = $model->searchFolders($searchQuery, $userID);
							break;
							
						case "Body":
							$result->notesInside=$model->searchNotesNotes($searchQuery,$userID);
							$result->foldersInside = $model->searchFolders($searchQuery, $userID);
							break;
					}
					break;
				
				case "Notes":
					switch($searchRequest->field){
						case "Both":
							$result->notesInside=array_merge(	$model->searchNotesTitles($searchQuery, $userID),
																$model->searchNotesNotes($searchQuery, $userID));					
							break;
								
						case "Title":
							$result->notesInside=$model->searchNotesTitles($searchQuery, $userID);
							break;
								
						case "Body":
							$result->notesInside=$model->searchNotesNotes($searchQuery, $userID);
							break;
					}
					break;
					
				case "Folders":
					$result->foldersInside = $model->searchFolders($searchQuery, $userID);
					break;
			}
			
			return $result;
		}
	}
?>