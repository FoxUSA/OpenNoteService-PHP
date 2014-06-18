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
										$_SESSION=$_POST;
										echo "Connected successfully";
										return;
									}
							}
						}
						
						$_SESSION["errors"] = $errors;
						echo "<script>window.location.href='?step=2-mysql'</script>";
						break;
					
				}
		?>
	</body>
</html>