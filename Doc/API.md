#API
OpenNote provides a REST API. All calls from the JavaScript client use this api. 
As a result, a good place to look on how these calls are used are in the default client.

##Security
Most calls are protected. I will not open calls below with the (Open) designation.

We use a token based security. OAuth did not seem a good option considering the ideals of this project.

Most calls use a `token` header to authenticate the calls. This can be aquired using the token endpoint.

The edge case is the file uploader, this uses a GET query parameter when uploading and a cookie when downloading.
This are due to the limitations of the CKEditor.

Currenty the token length is controlled in the service config.
In a future release we will support a refresh mechanism.


##Resources
###Note Resource

| Accepted Calls | Use						    | Parameter(s)  | Returns								    | Example Call	    | Notes	    |
| -------------- | ---------------------------- | ------------- | ----------------------------------------- | ----------------- | --------- | 
| GET			 | Get a note object 		    | id		    | HTTP Return Code and JSON note object	    | /Service/note/405 | 		    |
| POST			 | Persist a note object	    | 			    | HTTP Return Code and new JSON note object | /Service/note/    | userID and id are ignored and determined by the server. Notes are insert only and are neve updated except when a note is moved to a new folder. All history is also moved to the new filder. |
| DELETE		 | Delete a note and history    | id		    | HTTP Return Code						    | /Service/note/405 | Deletes history |

Sample Note JSON object
```
    {
       "id": "405",
       "folderID": "3696",
       "originNoteID": null,
       "title": "Note Title",
       "note": "<p>Hello World!</p>",
       "dateCreated": "2013-09-08 01:48:23",
       "userID": "1"
    }

```

###History
We do insert only approach with notes.
We use the origin note to create a history tree.
When the originNoteID is null, then this note is the origin note
We pull notes where it is the latest in the tree.

We retrieve them with the folowing query
```
SELECT  n.id, 
        n.title, 
        n.folderID,
        n.note,
        n.originNoteID,
        n.userID,
        n.dateCreated
FROM note n 
WHERE 	n.folderID = ? 
   AND (n.originNoteID IS NULL OR n.id IN (SELECT MAX(id) FROM note WHERE originNoteID=n.originNoteID)) 
   AND (SELECT COUNT(*) FROM note WHERE originNoteID = n.id)=0
ORDER BY n.title
```
Basically how this works is we look at all the notes and build a tree based on the `originNoteID` and pull the latest for the tree.


###Folder Resource

| Accepted Calls | Use						    | Parameter(s)  								| Returns								    		| Example Call	    								| Notes	    |
| -------------- | ---------------------------- | -------------------------						| ------------------------------------------------- | -------------------------------------------------	| --------- | 
| GET			 | Get a folder object 		    | id, levels, includeNotes, includeNotesHTML 	| HTTP Return Code and JSON folder object   		| /Service/folder?id=1&includeNotes=true&levels=1 	| 		    |
| POST			 | Insert a folder object	    | 			    								| HTTP Return Code and new JSON folder object 		| /Service/folder/    								| userID, id, foldersInside and notesInside are ignored |
| PUT			 | Update a folder				|												| HTTP Return Code and updated JSON folder object 	| /Service/folder/									| foldersInside and notesInside are ignored |
| DELETE		 | Delete a folder and contents | id	    									| HTTP Return Code						    		| /Service/note/405 								| Deletes subfolders and notes |


The `GET` call's query parameters are as follows

| Column			| Description									| Default value |
| -------------		| --------------------------------------------- | ------------- |
| id 				| The id of the folder you want					| none			|
| levels 			| The levels to travel down 					| 0 			|
| includeNotes		| Toggle including notesInside in return		| false			|
| includeNotesHTML	| Toggle including note html body in return		| true			|



Sample Folder JSON object with note and subfolder included
```
    {
       "id": "1",
       "parrentFolderID": null,
       "name": "Test",
       "userID": "1",
       "foldersInside":
       [
           {
               "id": "2",
               "parrentFolderID": "1",
               "name": "Test",
               "userID": "1",
               "foldersInside": null,
               "notesInside": null
           }
       ],
       "notesInside":
       [
           {
               "id": "4",
               "folderID": "1",
               "originNoteID": "1",
               "title": "Test Note",
               "note": "<p>Hello World</p>",
               "dateCreated": "2014-06-29 13:48:49",
               "userID": "1"
           }
       ]
    }
```

##Config
//TODO
##File
//TODO
##Token
//TODO
##User
//TODO