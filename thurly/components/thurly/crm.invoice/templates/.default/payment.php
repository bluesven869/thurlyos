<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

global $APPLICATION;

$APPLICATION->IncludeComponent(
	'thurly:crm.invoice.payment',
	'',
	array(
		'ORDER_ID' => $arResult['VARIABLES']['invoice_id']
	),
	$component
);
