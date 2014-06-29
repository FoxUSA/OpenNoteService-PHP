#API
OpenNote provides a REST API. All calls from the JavaScript client use this api. 
As a result, a good place to look on how these calls are used are in the default client.

##Security
Most calls are protected. I will not open calls below with the (Open) designation.

We use a token based security. OAuth did not seem a good option considering the ideals of this project.

Most calls use a `token` header. This can be aquired using the token endpoint.

The edge case is the file uploader, this uses a GET query parameter when uploading and a cookie when downloading.
This are due to the limitations of the CKEditor.


##Resources
###Note Resource

| Accepted Calls | Use						    | Parameter(s)  | Returns								    | Example Call	    | Notes	    |
| -------------- | ---------------------------- | ------------- | ----------------------------------------- | ----------------- | --------- | 
| GET			 | Get a note object 		    | Note ID	    | HTTP Return Code and JSON note object	    | /Service/note/405 | 		    |
| POST			 | Persist a note object	    | 			    | HTTP Return Code and new JSON note object | /Service/note/    | userID and id are ignored and determined by the server. Notes are insert only and are neve updated. This is how the history works. |
| DELETE		 | Delete a note and history    | Note ID	    | HTTP Return Code						    | /Service/note/405 | Deletes history |

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
Basically how this works is we look at all the notes and build a tree based on the originNoteID and pull the latest for the tree.


###Folder Resource

| Accepted Calls | Use						    | Parameter(s)  | Returns								    | Example Call	    | Notes	    |
| -------------- | ---------------------------- | ------------- | ----------------------------------------- | ----------------- | --------- | 
| GET			 | Get a note object 		    | Note ID	    | HTTP Return Code and JSON note object	    | /Service/note/405 | 		    |
| POST			 | Persist a note object	    | 			    | HTTP Return Code and new JSON note object | /Service/note/    | userID and id are ignored and determined by the server. Notes are insert only and are neve updated. This is how the history works. |
| DELETE		 | Delete a note and history    | Note ID	    | HTTP Return Code						    | /Service/note/405 | Deletes history |
