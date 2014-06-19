<?php
	session_start();
?>

<html>
	<body>
		<?php 
			include_once dirname(__FILE__)."/../vendor/autoload.php";
			include_once dirname(__FILE__)."/Config.php";
			
			//clean input
				\controller\Util::cleanPost();
				\controller\Util::cleanGets();
		
			$errors=array();
			
			
			abstract class Install{
				
				/**
				 * Generate config using find and replace
				 * @param string $pdoMethod
				 * @param string $username
				 * @param string $password
				 * @param string $server
				 * @param string $dbname
				 */
				public static function generateConfig(	$pdoMethod, 
														$username="", 
														$password="", 
														$server="",
			 											$dbname=""){
					//generate config
						$config = file_get_contents("./Config.template");
						
					//Insert db values
						$config = str_replace("@username@",$username,$config);
						$config = str_replace("@password@",$password,$config);
						$config = str_replace("@server@",$server,$config);
						$config = str_replace("@dbname@",$dbname,$config);
					
					//Setup mysql pdo as the db type
						$config = str_replace("//@pdoMethod@",$pdoMethod,$config);
					
					//Override current config
						file_put_contents("./Config.php",$config);
					
				}
			}
				
			//Step 0
				if($_GET==null){
					//Checks
						if(!is_writable("./Config.php"))
							$errors[] = "<p>Config.php is not writable</p>";
						
						if(!is_writable("./upload/"))
							$errors[] = "<p>Upload directory is not writable</p>";

					if(!count($errors)){
						echo "	<p>All checks so far look good.</p>
								<a href='?step=1'>Start Install</a>";
						return;
					}
					else
						foreach ($errors as $error) //print all the errors
							echo $error;
					return;
				}
			
			//All other steps
				switch ($_GET["step"]){
					case "1":
						echo "	<p>Please select a database type you wish to use:</p>
								<p><a href='?step=2-mysql'>MySQL</a></p>
								<p><a href='?step=2-sqlite'>sqlite(Recomended)</a></p>";
							
						break;
						
					case "2-sqlite":
						//generate config
						
							Install::generateConfig("return self::sqliteConfig();");
							
							Config::dbConfig()->exec(file_get_contents("./model/sql/notebook.sqlite.sql"));
							
							echo "<script>window.location.href='?step=cleanup'</script>";
						break;
						
					case "2-mysql":
						if(array_key_exists("errors",$_SESSION)) //any errors
							foreach ($_SESSION["errors"] as $error) //print all the errors
								echo "<p>$error</p>";
							
						$_SESSION["errors"]=null;//reset
							
						echo "	<p>Please enter MySQL information:</p>
								<form  action='?step=3-mysql' method='post'>
									
									Username: <input type='text' name='username'><br>
									Password: <input type='password' name='password'><br>
									Server ip/domain: <input type='text' name='server'><br>
									Database name: <input type='text' name='dbname'><br>
									<input type='submit' value='Submit'>
								</form> ";
						break;
						
					case "3-mysql":
						if($_POST!=null){
							if($_POST["username"]==null)
								$errors[] = "Username must not be blank";
							
							if($_POST["password"]==null)
								$errors[] = "Password must not be blank";
							
							if($_POST["server"]==null)
								$errors[] = "Server must not be blank";
							
							if($_POST["dbname"]==null)
								$errors[] = "Database name must not be blank";
									
							if(!count($errors)){
								Config::setInjectedCoreConfig(new PDO(sprintf("mysql:host=%s;dbname=%s",$_POST["server"],$_POST["dbname"]), $_POST["username"], $_POST["password"]));
								
								//Test our connection
									if(\model\pdo\Core::connect()){
										
										//create table structure
											\model\pdo\Core::query(file_get_contents("./model/sql/notebook.sql"));//execute sql
										
										Install::generateConfig("return self::mysqlConfig();",
																$_POST["username"],
																$_POST["password"],
																$_POST["server"],
																$_POST["dbname"]);
											
										echo "<script>window.location.href='?step=cleanup'</script>";
										return;
									}
									else
										$errors[] = "Could not connect to database";
							}
						}
						
						$_SESSION["errors"] = $errors;
						echo "<script>window.location.href='?step=2-mysql'</script>";
						break;
						
					case "cleanup":
						echo "<p>Install Complete</p>";
						echo "TODO";
						//TODO delete template config and one self
						break;
						
					
				}
		?>
	</body>
</html>