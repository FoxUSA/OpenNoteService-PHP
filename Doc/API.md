
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
