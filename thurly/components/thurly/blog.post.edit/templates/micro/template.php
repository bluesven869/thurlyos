<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "thurly:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/thurly/components/thurly/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/thurly/components/thurly/blog/templates/.default/themes/blue/style.css');
endif;
?>
<div class="blog-post-edit blog-post-edit-micro">
<?
if(strlen($arResult["MESSAGE"])>0)
{
	?>
	<div class="blog-textinfo blog-note-box">
		<div class="blog-textinfo-text">
			<?=$arResult["MESSAGE"]?>
		</div>
	</div>
	<?
}
if(strlen($arResult["ERROR_MESSAGE"])>0)
{
	?>
	<div class="blog-errors blog-note-box blog-note-error">
		<div class="blog-error-text">
			<?=$arResult["ERROR_MESSAGE"]?>
		</div>
	</div>
	<?
}
if(strlen($arResult["FATAL_MESSAGE"])>0)
{
}
elseif(strlen($arResult["UTIL_MESSAGE"])>0)
{
	?>
	<div class="blog-textinfo blog-note-box">
		<div class="blog-textinfo-text">
			<?=$arResult["UTIL_MESSAGE"]?>
		</div>
	</div>
	<?
}
else
{
	// Frame with file input to ajax uploading in WYSIWYG editor dialog
	if($arResult["imageUploadFrame"] == "Y")
	{
		?>
		<script>
			<?if(!empty($arResult["Image"])):?>
				top.bxBlogImageId = top.arImagesId.push('<?=$arResult["Image"]["ID"]?>');
				top.arImages.push('<?=CUtil::JSEscape($arResult["Image"]["PARAMS"]["SRC"])?>');
				top.bxBlogImageIdWidth = '<?=CUtil::JSEscape($arResult["Image"]["PARAMS"]["WIDTH"])?>';
			<?elseif(strlen($arResult["ERROR_MESSAGE"]) > 0):?>
				top.bxBlogImageError = '<?=CUtil::JSEscape($arResult["ERROR_MESSAGE"])?>';
			<?endif;?>
		</script>
		<?
		die();
	}
	else
	{
		?>
		<form action="<?=POST_FORM_ACTION_URI?>" name="REPLIER" method="post" enctype="multipart/form-data" target="_self" id="POST_BLOG_FORM">
		<input type="hidden" name="microblog" value="Y">
		<input type="hidden" id="DATE_PUBLISH_DEF" name="DATE_PUBLISH_DEF" value="<?=$arResult["PostToShow"]["DATE_PUBLISH"];?>">
		<?=thurly_sessid_post();?>
		<?
		if(COption::GetOptionString("blog", "use_autosave", "Y") == "Y")
		{
			$as = new CAutoSave();
			$as->Init(false);
			?>
			<script>
			BX.ready(BlogPostAutoSave);
			</script>
			<?
		}
		?>
		<div id="blog-post-edit-micro-form" class="blog-edit-form blog-edit-post-form blog-post-edit-form" style="display:none;">
			<div class="blog-post-field blog-post-field-title blog-edit-field blog-edit-field-title">
				<input maxlength="255" size="70" tabindex="1" type="text" name="POST_TITLE" id="POST_TITLE" value="<?=$arResult["PostToShow"]["TITLE"]?>">
			</div>
			<div class="blog-post-message blog-edit-editor-area blog-edit-field-text">
				<div class="blog-post-field blog-post-field-bbcode">
					<?
					include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/lhe.php");
					?>
					<div id="blog-post-micro-lhe-hide"></div>
				</div>
				<?
//				userconsent only for once for registered early users
				if ($arParams['USER_CONSENT'] == 'Y' && !$arParams['USER_CONSENT_WAS_GIVEN'])
				{
					$APPLICATION->IncludeComponent(
						"thurly:main.userconsent.request",
						"",
						array(
							"ID" => $arParams["USER_CONSENT_ID"],
							"IS_CHECKED" => $arParams["USER_CONSENT_IS_CHECKED"],
							"AUTO_SAVE" => "Y",
							"IS_LOADED" => $arParams["USER_CONSENT_IS_LOADED"],
							"ORIGIN_ID" => "sender/sub",
							"ORIGINATOR_ID" => "",
							"REPLACE" => array(
								'button_caption' => GetMessage("B_B_MS_SEND"),
								'fields' => array('Alias', 'Personal site', 'Birthday', 'Photo')
							),
							"SUBMIT_EVENT_NAME" => "OnUCFormCheckConsent"
						)
					);
				}
				?>
				<div class="blog-post-buttons blog-edit-buttons">
					<input type="hidden" name="save" value="Y">
					<span class="blog-small-button" onclick="blogCtrlEnterHandler()">
						<span class="blog-small-button-left"></span>
						<span class="blog-small-button-text"><?=GetMessage("BLOG_SEND_MICRO_NEW")?></span>
						<span class="blog-small-button-right"></span>
					</span>
					<span id="blog-post-micro-lhe-but"></span>
					<?
					if($arResult["CAN_POST_SONET_GROUP"])
					{?>
						<script>
						BX.message({SONET_GROUP_BLOG_NO : "<?=GetMessage("BLOG_POST_GROUP_CHOOSE")?>"});
						</script>
							<?$APPLICATION->IncludeComponent(
										"thurly:socialnetwork.group.selector", ".default", array(
											"BIND_ELEMENT" => "blog-post-group-selector",
											"ON_SELECT" => "onGroupBlogSelect",
											"FEATURES_PERMS" => array("microblog", array("premoderate_post", "moderate_post", "write_post", "full_post", "view_post")),
											"SELECTED" => IntVal($_POST["SONETGROUP"])
										), null, array("HIDE_ICONS" => "Y")
									);
							?>
							<div id="blog-post-group-selector" class="blog-post-group-text"><span class="blog-post-group-value"><?=GetMessage("BLOG_POST_GROUP_CHOOSE")?></span></div>
							<input name="SONETGROUP" id="SONETGROUP" type="hidden" value="">
						<?
					}
					if($arResult["bGroupMode"] && !empty($arResult["SONET_GROUP"]))
					{
						?><div class="blog-post-group-selector-text"><?=htmlspecialcharsEx($arResult["SONET_GROUP"]["NAME"])?></div><?
					}
					?>
				</div>
			</div>
		</div>
		</form>
		<?
	}
}
?>
</div>