<?php
	namespace controller;
	include_once dirname(__FILE__)."/../Config.php";
	abstract class Authenticater{	
		/**
		 * @param userName - the user name to check availability for
		 */
		public static function checkAvailability($userName, \model\IModel $model){
			if(self::validateUsername($userName)){
				echo "Invalid username";
				return;
			}

			$result = \model\pdo\Core::query("SELECT COUNT(*) AS count FROM users WHERE userName= ?",array($userName));
			
			if($result[0]["count"]==0)
				return 404;//404 not found - user is available
			else 
				return 302;//302 found - user not is available
		}
		
		/**
		 * Register a user
		 * @param userName - the username to register
		 * @param password - the password fort the user
		 * @param model - the model to use
		 */
		public static function register($userName, $password, $ip, \model\IModel $model){
	
			if(self::validateUsername($userName))
				throw new \controller\ServiceException("Invalid username", 400);
	
			try{
				$model->getUser($userName);//see if we can get it
				throw new \controller\ServiceException("Username already in use", 409);;
			}
			catch(\Exception $e){}//no user found exception
			
			$user = new \model\dataTypes\User();
				$user->userName = $userName;
				$user->password = password_hash($password, PASSWORD_DEFAULT);//hash password
			$user = $model->createUser($user);
			
			$issueTime = new \DateTime("now",new \DateTimeZone("UTC"));
			$expireTime = new \DateTime("now",new \DateTimeZone("UTC"));
			$expireTime->add(new \DateInterval(sprintf("PT%dM",\Config::tokenLife())));
			
			return $model->createToken($user->id, $ip, bin2hex(openssl_random_pseudo_bytes(16)), $issueTime, $expireTime);
		}

		/**
		 * Authenticate a user
		 * @param userName
		 * @param password
		 * @param model
		 */
		public static function login($userName, $password, $ip, \model\IModel $model){			
			$user;
			try{
				$user=$model->getUser($userName);
			}
			catch(\Exception $e){
				throw new \Exception("401");//User not found
			}
			
			if(!self::validatePassword($password, $user->password))//compare hashes
				throw new \controller\ServiceException("Not authorized", 401); 
				
			$token=bin2hex(openssl_random_pseudo_bytes(16));
			
			$issueTime = new \DateTime("now",new \DateTimeZone("UTC"));
			$expireTime = new \DateTime("now",new \DateTimeZone("UTC"));
			$expireTime->add(new \DateInterval(sprintf("PT%dM",\Config::tokenLife())));
			
			return $model->createToken($user->id, $ip, $token, $issueTime, $expireTime);
		}
		
		/**
		 * make sure the username is valid
		 * @param username - the username to validate
		 */
		private static function validateUsername($username) {
			return preg_match("/[^0-9a-z_]/i", $username);
		}
		
		/**
		 * @param password - the password the user entered
		 * @param $hashedPassword - the password that has been pre hashed
		 * @return - returns true if the password matched
		 */
		public static function validatePassword($password,$hashedPassword){
			return crypt($password, $hashedPassword)==$hashedPassword;
		}
		
		/**
		 * Validate token or invalidate it
		 * Token must match in value and from the same ip
		 * @param userToken - token string to validate
		 * @param ip - the ip of the request
		 * @param model - the model to use
		 * @return - token if valid
		 */
		public static function validateToken($userToken, $ip, \model\IModel $model){
			try{
			    if($userToken==null)
                    throw new \Exception("Invalid Token");
                
				$serverToken = $model->getToken($userToken);
				
				$evalTime = new \DateTime("now",new \DateTimeZone("UTC"));
				$issuedTime = new \DateTime($serverToken->issued, new \DateTimeZone("UTC"));
				$expiresTime = new \DateTime($serverToken->expires, new \DateTimeZone("UTC"));
				
				if($ip==$serverToken->ip && $issuedTime<=$evalTime && $evalTime<=$expiresTime)
					return $serverToken;
					
				$model->invalidateToken($userToken);
				throw new \Exception("Invalid Token");
			}
			catch(\Exception $e){
				throw new \controller\ServiceException("Not authorized",401);
			}
		}
		
		/**
		 * Invalidate a token
		 * @param $userToken - Invalidate a token
		 */
		public static function invalidateToken($userToken, \model\IModel $model){
			$model->invalidateToken($userToken);
		}
	}
?>
