<?php
	session_start();
?>

<html>
	<head>
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
	</head>
	<body>
		<div class="container">
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
									<a href='?step=1' class='btn btn-default'>Start Install</a>";
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
									<p><a href='?step=2-mysql' class='btn btn-default'>MySQL(Recomended)</a></p>
									<p><a href='?step=2-sqlite' class='btn btn-default'>sqlite</a></p>";
								
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
										
										Username: <input type='text' name='username' class='form-control'><br>
										Password: <input type='password' name='password' class='form-control'><br>
										Server ip/domain: <input type='text' name='server' class='form-control'><br>
										Database name: <input type='text' name='dbname' class='form-control'><br>
										<input type='submit' value='Submit' class='form-control'>
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
							if(!unlink("./Config.template"))
								echo "<p>Was unable to delete Config.template. Please delete it manually.</p>";
							if(!unlink("./install.php"))
								echo "<p>Was unable to delete Install.php. Please delete it manually.</p>";
							break;
					}
			?>
		</div>
	</body>
</html>