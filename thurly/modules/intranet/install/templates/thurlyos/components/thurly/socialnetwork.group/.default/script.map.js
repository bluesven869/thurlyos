{"version":3,"sources":["script.js"],"names":["BX","namespace","window","B24SGControl","this","instance","groupId","waitPopup","waitTimeout","notifyHintPopup","notifyHintTimeout","notifyHintTime","favoritesValue","newValue","menuItem","getInstance","prototype","init","params","parseInt","addCustomEvent","delegate","setMenuItemTitle","setSubscribe","event","_this","showWait","action","hasClass","ajax","url","method","dataType","data","groupID","sessid","thurly_sessid","onsuccess","processSubscribeAJAXResponse","PreventDefault","setFavorites","lang","message","processFavoritesAJAXResponse","NAME","URL","onCustomEvent","id","name","extranet","EXTRANET","onfailure","value","innerHTML","closeWait","button","showNotifyHint","adjust","attrs","title","removeClass","addClass","length","processAJAXError","errorCode","indexOf","showError","timeout","setTimeout","PopupWindow","autoHide","lightShadow","zIndex","content","create","props","className","children","html","setBindElement","show","clearTimeout","close","el","hint_text","style","display","closeByEsc","closeIcon","offsetLeft","TEXT","setAngle","errorText","errorPopup","Math","random","SGMSetSubscribe"],"mappings":"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","file":""}