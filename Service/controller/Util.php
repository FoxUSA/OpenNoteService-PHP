<?php
/**
 *	Project name: OpenNote
 * 	Author: Jacob Liscom
 *	Version: 13.3.0
**/
	namespace controller;
	abstract class Util{
		
		/**
		 * Clean post values by escaping special characters
		 */
		public static function cleanPost(){
			foreach($_POST as $key => $val) {
				if(isset($_POST[$key])&&is_String($_POST[$key])){
					$_POST[$key] =   htmlentities($val);
					$key =   htmlentities($key);//escape characters
				}
			}; //in this app were we allow html code to sent to the back end
		}
		
		/**
		 * Clean get values by escapting special characters
		 */
		public static function cleanGets(){
			foreach($_GET as $key => $val) {
				if(isset($_GET[$key]) && is_String($_GET[$key])){
					$_GET[$key] = stripslashes(strip_tags(htmlspecialchars($val, ENT_QUOTES)));
					$key = stripslashes(strip_tags(htmlspecialchars($key, ENT_QUOTES)));
				}
			};
		}
		
		/**
		 * Check to see if there is an update
		 * @return - returns
		 */
		public static function checkForOpenNoteUpdate(){
			if(Config::$checkForUpdates){
				$json=file_get_contents(sprintf("%s-%s&version=%s",Config::$updateServicePath,Config::$releaseChannel,Config::$version));
				
				if(sizeof($json)!=0){
					try{
						$update=json_decode($json);
						
						if($update!=null && Config::$version!=$update->version)
							echo sprintf("<a id=\"update\" href=\"%s\">%s</a>", $update->updateURL, $update->updateText);
					}
					catch(Exception $e){
					}
				}
			}	
		}
	}
?>
