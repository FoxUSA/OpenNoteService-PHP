<?php
/**
 *	Project name: OpenNote
 * 	Author: Jacob Liscom
 *	Version: 13.11.0
**/
namespace controller;
abstract class File{
	
	/**
	 * Upload a file
	 * @param model - model to use
	 * @param tokenServer - the validated token
	 */
	public static function startUpload(\model\IModel $model, $tokenServer){
		$url="";
		
		$diskName = self::createGUID();//the name we are going to store it under
		$originalName = $_FILES["upload"]["name"];//the name they sent us
		
		$message="";
		
		 //extensive suitability check before doing anything with the file...
		    if (($_FILES["upload"] == "none") OR (empty($_FILES["upload"]["name"])))
		       $message = "No file uploaded. Try checking the php upload and post limits.";
		    else 
		    	if ($_FILES["upload"]["size"] == 0)
		      		$message = "File is blank";
		    	else 
			    	if (!is_uploaded_file($_FILES["upload"]["tmp_name"]))
			       		$message="File is not a uploaded file";
				    else {
				      	if(!move_uploaded_file($_FILES["upload"]["tmp_name"], sprintf("%s%s",\Config::getUploadPath(),$diskName)))
				         	$message = "Error moving uploaded file. Check the script is granted Read/Write/Modify permissions.";
						
						if($message=="")
							$url = sprintf("//%s%s%s%s",$_SERVER["SERVER_NAME"],\Config::getWebRoot() ,"service.php/file/", $model->uploadFile(self::createGUID(),$originalName, $diskName, $tokenServer->userID));
		    		}
		 
		$funcNum = $_GET["CKEditorFuncNum"];
		echo "<script type\"text/javascript\">window.parent.CKEDITOR.tools.callFunction($funcNum, \"$url\", \"$message\");</script>";
	}
	
	/**
	 * Download a file
	 * @param model - model to use
	 * @param id - id of the file to download
	 * @param tokenServer - the validated token
	 */
	public static function startDownload(\model\IModel $model, $id, $tokenServer){	
		$result = $model->getUploadFile($id);
	
		if(count($result)!=1)			
			throw new \controller\ServiceException("File record not found",410);

		if($result[0]["userID"]!=$tokenServer->userID) //make sure they own the file
			throw new \controller\ServiceException("Not authorized", 401); 
		
		$originalName = $result[0]["originalName"];
		$diskName = sprintf("%s%s",\Config::getUploadPath(),$result[0]["diskName"]);
	
		if (file_exists($diskName)) {
			header(sprintf("Content-Disposition:  attachment; filename=\"%s\";",$originalName)); //let them know what we are sending
			header("Content-Transfer-Encoding:  binary");
				
			header(sprintf("Content-Length:  %d",filesize($diskName))); //and how big it is
			ob_clean();
			flush();
			
			ob_end_flush();

			set_time_limit(0);
			$file = @fopen($diskName,"rb");
			while(!feof($file))
			{
				print(@fread($file, 1024*8));
				ob_flush();
				flush();
			}
			exit;
		}
		else
			throw new \controller\ServiceException("File not found",404);
	}
	
	/**
	 * Crate a GUID. Not super random but good enough for our purposes
	 * @return - GUID
	 */
	private static function createGUID(){
		if (function_exists("com_create_guid")){
			return com_create_guid();
		}else{
			mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
			$charid = strtoupper(md5(uniqid(rand(), true)));
			$hyphen = chr(45);// "-"
			return substr($charid, 0, 8).$hyphen
			.substr($charid, 8, 4).$hyphen
			.substr($charid,12, 4).$hyphen
			.substr($charid,16, 4).$hyphen
			.substr($charid,20,12);
		}
	}
}
?>
