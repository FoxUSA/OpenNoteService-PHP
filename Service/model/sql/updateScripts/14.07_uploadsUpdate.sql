USE test;

START TRANSACTION;

	-- Change notes to new id
		UPDATE			note n-- yes I do mean to do a cartesian join
			SET n.note = REPLACE (n.note,  CONCAT('?uploadID=',''), newID)
		WHERE n.note LIKE '%?uploadID=%';

ROLLBACK;