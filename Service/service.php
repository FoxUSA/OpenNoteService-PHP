<?php
/**
 *	Project name: OpenNote
 * 	Author: Jacob Liscom
 *	Version: 13.7.0
 *
 * Handles the java script to php calls
**/

	//then have multple back ends.(NodeJS?)
//TODO default value

	include_once dirname(__FILE__)."/../vendor/autoload.php";
	include_once dirname(__FILE__)."/Config.php";

	//clean input
		\controller\Util::cleanPost();
		\controller\Util::cleanGets();

	$app = new \Slim\Slim();
	$app->add(new \CorsSlim\CorsSlim());
	$app->contentType("application/json");//we return json on almost everything

	/**
	 * REST scheme
	 * GET to retrieve and search data
	 * POST to add data
	 * PUT to update data
	 * DELETE to delete data
	 */
	//auth
		//check username availability
			$app->get("/user/:user", function($user) use ($app){
					$app->response->setStatus(\controller\Authenticater::checkAvailability($user, Config::getModel()));
			});

		//delete token
			$app->delete("/token/", function ()  use ($app){
				\controller\Authenticater::invalidateToken($app->request->headers->get("token"),Config::getModel());
			});

		//register
			$app->post("/user/:user&:password", function ($user, $password)  use ($app){
				$ip = $_SERVER["REMOTE_ADDR"];

				if(!\Config::getRegistrationEnabled()){//dont allow them to execute this call if it is disabled
					$app->response->setStatus(503); //return error code
					return;
				}

				try{
					$app->response->setBody(json_encode(\controller\Authenticater::register($user,$password, $ip, Config::getModel())));
				}
				catch(\controller\ServiceException $e){
					$app->response->setStatus($e->getCode()); //return error code
					return;
				}
				catch(\Exception $e){
					$app->response->setStatus(500); //return error code
				}
			});

		//login
			$app->post("/token/:user&:password", function ($user, $password)  use ($app){
				$ip = $_SERVER["REMOTE_ADDR"];

				try{
					$app->response->setBody(json_encode(\controller\Authenticater::login($user,$password, $ip, Config::getModel())));
				}
				catch(\controller\ServiceException $e){
                    $app->response->setStatus($e->getCode()); //return error code
                    return;
                }
                catch(\Exception $e){
                    $app->response->setStatus(500); //return error code
                }
			});

	//notes
		//get note
			$app->get("/note/:id", function ($id) use ($app) {
				try{
				    $token = $app->request->headers->get("token");
					$tokenServer = \controller\Authenticater::validateToken($token, $_SERVER["REMOTE_ADDR"], Config::getModel()); //replace token with validated one

					$note = \controller\NoteBook::getNote(Config::getModel(), $tokenServer, $id); //get note
					$app->response->setBody(json_encode($note)); //return it
				}
				catch(\controller\ServiceException $e){
					$app->response->setStatus($e->getCode()); //return error code
					return;
				}
				catch(\Exception $e){
					$app->response->setStatus(500); //return error code
					return;
				}
			});

        //delete note
            $app->delete("/note/:id", function ($id) use ($app){
                try{
                    $token = $app->request->headers->get("token");
                    $tokenServer = \controller\Authenticater::validateToken($token, $_SERVER["REMOTE_ADDR"], Config::getModel()); //replace token with validated one

                    $note = \controller\NoteBook::getNote(Config::getModel(), $tokenServer, $id); //get note
                    $note = \controller\NoteBook::removeNote(Config::getModel(), $tokenServer, $note);
                }
                catch(\controller\ServiceException $e){
                    $app->response->setStatus($e->getCode()); //return error code
                    return;
                }
                catch(\Exception $e){
                    $app->response->setStatus(500); //return error code
                    return;
                }

            });

	//Folder
        //Get Folder
            $app->get("/folder/", function () use ($app) {
                try{
                	//get query
	                	$id = $app->request()->get("id");
	                	$levels = $app->request()->get("levels") != null ? $app->request()->get("levels") : 0; //default
	                	$includeNotes = $app->request()->get("includeNotes")=="true" ? true : false;
	                	$includeNotesHTML =  $app->request()->get("includeNotesHTML")=="true" ? true : false;

                    $token = $app->request->headers->get("token");
                    $tokenServer = \controller\Authenticater::validateToken($token, $_SERVER["REMOTE_ADDR"], Config::getModel()); //replace token with validated one

                    $folder = \controller\NoteBook::getFolder(Config::getModel(), $tokenServer, $id, $levels, $includeNotes, $includeNotesHTML); //get folder

                    $app->response->setBody(json_encode($folder)); //return it
                }
                catch(\controller\ServiceException $e){
                    $app->response->setStatus($e->getCode()); //return error code
                    return;
                }
                catch(\Exception $e){
                    $app->response->setStatus(500); //return error code
                    return;
                }
            });


        //Delete folder
          $app->delete("/folder/:id", function ($id) use ($app) {
                try{
                    $token = $app->request->headers->get("token");
                    $tokenServer = \controller\Authenticater::validateToken($token, $_SERVER["REMOTE_ADDR"], Config::getModel()); //replace token with validated one

                    $folder = \controller\NoteBook::getFolder(Config::getModel(), $tokenServer, $id); //get note
                    \controller\NoteBook::removeFolder(Config::getModel(), $tokenServer, $folder); //remove folder
                }
                catch(\controller\ServiceException $e){
                    $app->response->setStatus($e->getCode()); //return error code
                    return;
                }
                catch(\Exception $e){
                    $app->response->setStatus(500); //return error code
                    return;
                }
            });
	//Config
		//Get config values
          	$app->get("/config/", function () use ($app) {
          		try{
          			$app->response->setBody(json_encode(Config::getInitialConfig()));
          		}
          		catch(\controller\ServiceException $e){
          			$app->response->setStatus($e->getCode()); //return error code
          			return;
          		}
          		catch(\Exception $e){
          			$app->response->setStatus(500); //return error code
          			return;
          		}
          	});

   	//File
   		//Upload
	    	$app->post("/file/", function () use ($app) {
	    		try{
	    			if(!Config::getUploadEnabled()){//Check to see if this is allowed
	    				$app->response->setStatus(503); //return error code
	    				return;
	    			}

	    			$tokenServer = \controller\Authenticater::validateToken($app->request()->get("token"), $_SERVER["REMOTE_ADDR"], Config::getModel());

	    			$app->contentType("text/html");//Override other calls
	    			$app->response->setBody(\controller\File::startUpload(Config::getModel(),$tokenServer));
	    		}
	    		catch(\controller\ServiceException $e){
	    			$app->response->setStatus($e->getCode()); //return error code
	    			return;
	    		}
	    		catch(\Exception $e){
	    			$app->response->setStatus(500); //return error code
	    			return;
	    		}

	    	});

	    //Download
	    	$app->get("/file/:id", function ($id) use ($app) {
	    		try{
	    			ob_start();//buffer response
	    			$tokenServer = \controller\Authenticater::validateToken($_COOKIE["token"], $_SERVER["REMOTE_ADDR"], Config::getModel());
	    			$app->contentType("application/octet-stream");//Override other calls
	    			\controller\File::startDownload(Config::getModel(), $id, $tokenServer);
	    		}
	    		catch(\controller\ServiceException $e){
	    			$app->response->setBody($e->getMessage());
	    			$app->response->setStatus($e->getCode()); //return error code
	    			return;
	    		}
	    		catch(\Exception $e){
	    			$app->response->setStatus(500); //return error code
	    			return;
	    		}
			});

    //Search
    	$app->post("/search/", function () use ($app) {
    		try{
    			$token = $app->request->headers->get("token");
    			$tokenServer = \controller\Authenticater::validateToken($token, $_SERVER["REMOTE_ADDR"], Config::getModel()); //replace token with validated one

    			//Send back the results
    			$app->response->setBody(
    					json_encode(\controller\Search::searchRequest(	Config::getModel(),
																		$tokenServer,
    																	json_decode($app->request->getBody())))); //return it
    		}
    		catch(\controller\ServiceException $e){
    			$app->response->setStatus($e->getCode()); //return error code
    			return;
    		}
    		catch(\Exception $e){
    			$app->response->setStatus(500); //return error code
    			return;
    		}
    	});

	$app->run();
?>	
