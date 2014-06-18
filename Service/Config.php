<?php
/**
 *	Project name: OpenNote
 * 	Author: Jacob Liscom
 *	Version: 13.10.0
**/
//Notice no include here. We need this here because we want to include this without the rest of the include tree
	
	abstract class Config{
		
		/**
		 * Database details
		 */
			public static function dbConfig(){
				//Un-comment desired database type
					return self::mysqlConfig();
				//return self::sqliteConfig();
			}

				/**
				 * sql lite
				 */
				private static function sqliteConfig(){			
					//pdo
						//Path to DB. Do not put in webdirectory! If you do anyone can download your database!
						$dbName = "../phplite/OpenNote"; //relative path to sqllite db
						return new PDO(sprintf("sqlite:%s\%s",dirname(__FILE__),$dbName));
				}
				
				/**
				 * mysql
				 */
				private static function mysqlConfig(){			
					//mysql
						$dbUserName = "notebook";
						$dbPassword = "password";
						$dbServer = "127.0.0.1";
						$dbName = "notebook";
						
						return new PDO(sprintf("mysql:host=%s;dbname=%s",$dbServer,$dbName), $dbUserName, $dbPassword);
				}
				
				private static $injectedCoreConfig = null;
				
				/**
				 * Setter for config
				 * @param $config - config object to inject
				 */
				public static function setInjectedCoreConfig($config){
					self::$injectedCoreConfig = $config;
				}
				
			/**
			 * Which model to use
			 */
			public static function getModel(){
				return new \model\pdo\Model(self::dbConfig());
			}
		
		/**
		 * Upload
		 */
			public static function getUploadEnabled(){
				return true;//Default: true. Allwer users to upload files.
			}
			
			/**
			 * Get upload path relative to File class
			 */
				public static function getUploadPath(){
					return "./upload/";
				}
			
			
		/**
		 * Registration
		 */
			public static function getRegistrationEnabled(){
				return true;//Default: true. Allow users to register.
			}
			
		/**
		 * Security
		 */
			/**
			 * @return - the token life in minutes
			 */		 
		 	public static function tokenLife(){
		 		return 60;
		 	}
		 	
		/**
		 * Config
		 */
		 	/**
		 	 * @return - array to send client
		 	 */
		 	public static function getInitialConfig(){
		 		return array(
		 			"uploadEnabled" => self::getUploadEnabled(),
		 			"registrationEnabled" => self::getRegistrationEnabled()
		 		);
		 	}
		 	
		/**
		 * Update
		 */
		 	public static $checkForUpdates = false; //Default: true; Check for updates
		 	public static $updateServicePath = "http://stardrive.us/UpdateService/?appName=OpenNote"; //Path to version service
		 	public static $version = "13.12.0-1";
			public static $releaseChannel = "dev"; //Default: prod; Release channel. Prod is production level release. Dev is current deployment release.s
				
		/**
		 * Get web root
		 */
			 public static function getWebRoot(){
			 	return str_replace("\\", "/",str_replace(realpath($_SERVER["DOCUMENT_ROOT"]),"",realpath(dirname(__FILE__))))."/";
			 }
	}
?>