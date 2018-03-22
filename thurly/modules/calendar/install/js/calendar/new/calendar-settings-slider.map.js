{"version":3,"sources":["calendar-settings-slider.js"],"names":["window","SettingsSlider","params","this","calendar","id","uid","Math","round","random","button","zIndex","sliderId","inPersonal","util","userIsOwner","showGeneralSettings","config","perm","access","settings","SLIDER_WIDTH","SLIDER_DURATION","BX","bind","delegate","show","prototype","SidePanel","Instance","open","contentCallback","create","width","animationDuration","addCustomEvent","proxy","hide","destroy","keyHandlerEnabled","close","event","getSliderPage","getUrl","denyClose","denyAction","removeCustomEvent","promise","Promise","ajax","get","getActionUrl","action","is_personal","show_general_settings","unique_id","sessid","thurly_sessid","bx_event_calendar_request","reqId","html","fulfill","trim","initControls","save","DOM","denyBusyInvitation","showWeekNumbers","sectionSelect","crmSelect","showDeclined","showTasks","showCompletedTasks","timezoneSelect","workTimeStart","workTimeEnd","weekHolidays","yearHolidays","yearWorkdays","typeAccess","TYPE_ACCESS","accessWrap","initAccessController","code","hasOwnProperty","insertAccessRow","getAccessName","manageCalDav","syncSlider","showCalDavSyncDialog","options","length","sections","sectionController","getSectionList","meetSection","getUserOption","crmSection","i","section","selected","belongToOwner","add","Option","name","checked","showWeekNumber","value","work_time_start","work_time_end","in_array","week_holidays","year_holidays","year_workdays","userSettings","userTimezoneName","data","user_settings","user_timezone_name","push","type_access","request","type","handler","response","reload","accessControls","accessTasks","getTypeAccessTasks","accessLink","hasClass","removeClass","addClass","Access","Init","accessWrapInner","appendChild","props","className","accessTable","accessButtonWrap","accessButton","message","ShowForm","callback","provider","GetProviderName","popup","popupContainer","style","e","target","findTargetNode","srcElement","outerWrap","getAttribute","showAccessSelectorPopup","node","removeIcon","setValueCallback","valueNode","innerHTML","htmlspecialchars","title","cleanNode","rowNode","undefined","getDefaultTypeAccessTask","adjust","insertRow","titleNode","insertCell","valueCell","attrs","data-bx-calendar-access-selector","selectNode","text","data-bx-calendar-access-remove","accessPopupMenu","popupWindow","isShown","menuId","taskId","_this","menuItems","onclick","PopupMenu","closeByEsc","autoHide","offsetTop","offsetLeft","angle","BXEventCalendar"],"mappings":"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","file":""}