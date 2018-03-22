{"version":3,"file":"cjstask.min.js","sources":["cjstask.js"],"names":["BX","CJSTask","ajaxUrl","message","sequenceId","timers","addTimeToDate","date","defaultTime","params","onlyIfEmpty","parseInt","getHours","getMinutes","h","setHours","m","setMinutes","s","setSeconds","addTimeToDateTime","datetime","toString","length","parseDate","format","convertThurlyFormat","ui","extractDefaultTimeFromDataAttribute","node","type","isDomNode","defaultHour","data","defaultMinute","getInputDateTimeValue","curDate","Date","curDayEveningTime","getFullYear","getMonth","getDate","value","selectedDate","convertToUTC","getMessagePlural","n","msgId","pluralForm","langId","fixWindow","fn","winTop","window","top","win","args","Array","prototype","slice","call","arguments","unshift","apply","this","createItem","newTaskData","columnsIds","postData","sessid","batch","operation","taskData","ID","ajax","method","dataType","url","processData","onsuccess","callbackOnSuccess","callbackOnFailure","callback","reply","status","precachedData","allowedTaskActions","allowedTaskActionsAsStrings","oTask","Item","legacyDataFormat","parseJSON","legacyHtmlTaskItem","errMessages","errorsCount","repliesCount","hasOwnProperty","errors","i","push","rawReply","taskId","cachedData","getCachedData","refreshCache","objTask","complete","startExecutionOrRenewAndStart","addElapsedTime","callbacks","elapsedTimeData","TASK_ID","MINUTES","COMMENT_TEXT","batchId","batchOperations","checklistAddItem","title","arFields","TITLE","checklistData","checklistRename","id","newTitle","itemId","checklistComplete","checklistRenew","checklistDelete","checklistMoveAfterItem","insertAfterItemId","stopWatch","startWatch","TimerManager","__private","startOrStop","start","stop","setTimerCallback","timerCodeName","milliseconds","clearInterval","setInterval","formatUsersNames","arUsersIds","userId","key","userData","replyItem","result","getGroupsData","arGroupsIds","groupId","groupData","sync","async"],"mappings":"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"}