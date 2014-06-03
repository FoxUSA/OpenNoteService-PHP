<?php
/**
 *	Project name: OpenNote
 * 	Author: Jacob Liscom
 *	Version: 13.11.0
**/
namespace upload;


include_once dirname(__FILE__)."/../../vendor/autoload.php";
include_once dirname(__FILE__)."/../Config.php";

abstract class Upload{
	/**
	 * Upload a file
	 */
	public static function startUpload(\model\IModel $model){
		if(Config::getUploadEnabled()){
			if($_GET["token"] == null)
				throw new \controller\ServiceException("Unauthorized",401);
			
			$tokenServer = \controller\Authenticater::validateToken($_GET["token"], $_SERVER["REMOTE_ADDR"], Config::getModel());
			
			$diskName = sprintf("%s_%d",time(),rand());//the name we are going to store it under
			$originalName = $_FILES["upload"]["name"];//the name they sent us
			
			$url = sprintf("http://%s%s?uploadID=",$_SERVER["SERVER_NAME"], self::getPath(),$diskName); //fancy way to get the Download.php path without a config
			$localPath = sprintf("./%s",$diskName);
			
			$message="";
			
			 //extensive suitability check before doing anything with the file...
			    if (($_FILES["upload"] == "none") OR (empty($_FILES["upload"]["name"])))
			       $message = "No file uploaded. Try checking the php upload and post limit.";
					
			    else 
			    	if ($_FILES["upload"]["size"] == 0)
			      		$message = "The file is of zero length.";
			    
			    	else 
				    	if (!is_uploaded_file($_FILES["upload"]["tmp_name"]))
				       		$message = "You may be attempting to hack our server. We're on to you; expect a knock on the door sometime soon.";
			    
					    else {
					      	$message = "";
							
					      	$move = move_uploaded_file($_FILES["upload"]["tmp_name"], $localPath);//alter the url to support this relative path
		
					      	if(!$move)
					         	$message ="Error moving uploaded file. Check the script is granted Read/Write/Modify permissions.";
							
							if($message=="")
								$url =$url.$model->uploadFile($originalName, $diskName, $tokenServer->userID);
			    		}
			 
			$funcNum = $_GET["CKEditorFuncNum"];
			echo "<script type\"text/javascript\">window.parent.CKEDITOR.tools.callFunction($funcNum, \"$url\", \"$message\");</script>";
		}
	}
	
	/**
	 * Download a file
	 */
	public static function startDownload(\model\IModel $model){
		ob_start();
		$result = null;
	
		if(isset($_GET["uploadID"])) //get existing note
			$result = $model->getUploadFile($_GET["uploadID"]);//syntax /?id=$id\"
		
	
		if(count($result)==0){
			echo "File not found";
			return; //no results
		}
	
		$originalName = $result[0]["originalName"];
		$diskName = $result[0]["diskName"];
	
		if (file_exists($diskName)) {
			header("Content-Type: application/octet-stream");
			header(sprintf("Content-Disposition:  attachment; filename=\"%s\";",$originalName)); //let them know what we are sending
			header("Content-Transfer-Encoding:  binary");
				
			header(sprintf("Content-Length:  %d",filesize($diskName))); //and how big it is
			ob_clean();
			flush();
			readfile($diskName); //send it away
			exit;
		}
	}
	
	/**
	 * @return  - returns the full path name of this class relative to the web root
	 */
	public static function getPath(){
		return str_replace("\\", "/",str_replace(realpath($_SERVER["DOCUMENT_ROOT"]),"",realpath(dirname(__FILE__))))."/Download.php";
	}
}
?>