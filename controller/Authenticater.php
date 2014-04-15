<?php
	namespace controller;
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
		 * register a user
		 * @param userName - the username to register
		 * @param password - the password fort the user
		 */
		public static function register($userName, $password){
	
			if(self::validateUsername($userName)){
				echo "Invalid username";
				return;
			}
	
			$result = Core::query("SELECT COUNT(*) AS count FROM users WHERE userName= ?",array($userName));
			
			if($result[0]["count"]!=0){
				echo "The username is taken. Please try something else.";
				return;
			}

			//Encrypting password
			$password = crypt($password);//hash password

			Core::query("INSERT INTO users(userName, password, lastLoginIP) VALUES(?,?,?);",array($userName,$password,$_SERVER['REMOTE_ADDR']));
			
			$_SESSION["userID"] = Core::getInsertID();
			echo "Thank You For Registering
				<script type=\"text/javascript\">
					document.location.href =\"../../\";
				</script>";
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
			
			$expireTime = new \DateTime("now");
			$expireTime->add(new \DateInterval("PT10H"));
			
			return $model->createToken($user->id, $ip, $token, $expireTime->format("Y-m-d H:i:s"));
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
				if($ip=$serverToken->ip)
					return $serverToken;
					
				$model->invalidateToken($userToken);
				throw new \Exception("Invalid Token");
			}
			catch(\Exception $e){
				throw new \controller\ServiceException("Not authorized",401);
			}
			
		}
	}
?>