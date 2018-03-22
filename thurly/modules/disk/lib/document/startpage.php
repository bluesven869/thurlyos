<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>

	<?php
	/** @var CMain $APPLICATION */
	use Thurly\Main\Localization\Loc;
	Loc::loadMessages(__FILE__);

	CJSCore::Init(array('ajax', 'viewer'));
	$APPLICATION->ShowHead();
	?>
</head>
<body style="height: 100%;margin: 0;padding: 0; background: #f5f5f5">
	<script type="text/javascript">
	function closeConfirm()
	{
		if (typeof(BX.PULL) != 'undefined' && typeof(BX.PULL.tryConnectDelay) == 'function') // TODO change to right code in near future (e.shelenkov)
		{
			BX.PULL.tryConnectDelay();
		}
		if(window.opener)
		{
			if((BX.browser.IsIE() || BX.browser.IsIE11() || /Edge\/12./i.test(navigator.userAgent)) && window.opener._ie_elementViewer && (window.opener._ie_elementViewer.bVisible || window.opener._ie_elementViewer.createDoc))
			{
				window.opener._ie_elementViewer.closeConfirm();
				if(window.opener._ie_elementViewer.createDoc)
					window.opener.BX.CViewer.unlockScroll();
				return true;
			}
			else if(window.elementViewer && (window.elementViewer.bVisible || window.elementViewer.createDoc))
			{
				window.elementViewer.closeConfirm();
				if(window.elementViewer.createDoc)
					window.opener.BX.CViewer.unlockScroll();
				return true;
			}
		}
		return false;
	}

	BX.ready(function(){
		window.successLoadCommitData = false;
		window.runAuthAction = false;
		window.onbeforeunload = function (e) {
			try {
				if (!window.successLoadCommitData && !window.runAuthAction) {
					closeConfirm();
				}
			} catch (e) {
			}
		};

		BX.ajax({
			'method': 'POST',
			'dataType': 'json',
			'url': '<?= CUtil::JSEscape($url) ?>',
			'data':  {
				SITE_ID: BX.message('SITE_ID'),
				sessid: BX.thurly_sessid()
			},
			'onsuccess': function(data){
				data = data || {};

				BX.hide(BX('loader'));
				if(!data.status)
				{
					BX.adjust(BX('error'), {style: {display: 'table'}});
					BX.adjust(BX('error-text'), {text: 'Unknown error.'});
					closeConfirm();
					return;
				}
				if(data.status == 'error')
				{
					BX.adjust(BX('error'), {style: {display: 'table'}});
					var messages = [];
					for (var i in data.errors) {
						if (!data.errors.hasOwnProperty(i)) {
							continue;
						}
						messages.push(data.errors[i].message);
					}
					BX.adjust(BX('error-text'), {text: messages.join('<br>')});
					closeConfirm();
					return;
				}
				if(data.authUrl)
				{
					window.runAuthAction = true;
					window.location.href = data.authUrl;
					return;
				}
				if(data.status == 'success')
				{
					if(data.link)
					{
						window.successLoadCommitData = true;
						if(window.opener) {
							if (
								(BX.browser.IsIE() || BX.browser.IsIE11() || /Edge\/12./i.test(navigator.userAgent)) &&
								window.opener._ie_elementViewer &&
								(window.opener._ie_elementViewer.createDoc || window.opener._ie_elementViewer.bVisible && window.opener._ie_elementViewer.isCurrent(window.opener.window._ie_currentElement))) {
								var link = data.link;
								var editSessionId = data.editSessionId;
								var id = data.id;
								var iframeSrc = data.iframeSrc;
								var uriToDoc = data.uriToDoc;
								var idDoc = data.idDoc;
								window.opener._ie_currentElement.setDataForCommit(
									iframeSrc,
									uriToDoc,
									idDoc,
									editSessionId,
									id,
									link
								);
							}
							else if (window.elementViewer && window.elementViewer.bVisible) {
								if (window.elementViewer.isCurrent(window.currentElement)) {
									window.currentElement.setDataForCommit(data);
								}
							}
							else if (window.opener._ie_elementViewer && window.opener._ie_elementViewer.bVisible) {
								if (window.opener._ie_elementViewer.isCurrent(window.opener.window._ie_currentElement)) {
									window.opener._ie_currentElement.setDataForCommit(data);
								}
							}
							else if (window.elementViewer && window.elementViewer.createDoc) {
								window.currentElement.setDataForCommit(data);
							}
							else if (window.opener._ie_elementViewer && window.opener._ie_elementViewer.createDoc) {
								window.opener._ie_currentElement.setDataForCommit(data);
							}
						}

						window.location.href = data.link;
					}
				}


			}
		});
	});
	</script>
	<div id="loader" style="display: table;width:  100%;height: 100%;">
		<div style="display: table-cell; vertical-align: middle;text-align: center;"><div class="bx-viewer-wrap-loading"></div><?= Loc::getMessage('DISK_START_PAGE_LOADING_DOC') ?></div>
	</div>
	<div id="error" style="display: none;width:  100%;height: 100%;">
		<div style="display: table-cell; vertical-align: middle;text-align: center;"><span id="error-text"></span></div>
	</div>

</body>
</html>