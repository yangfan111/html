<?php

/*
 * 生成guid
 */
function create_guid() {
	return trim(_create_guid_(), '{}');
}

function _create_guid_() {
	if (function_exists('com_create_guid'))
		return com_create_guid();

	$charid = strtoupper(md5(uniqid(mt_rand(), true)));
	$hyphen = chr(45);// "-"
	$uuid = chr(123)// "{"
	.substr($charid, 0, 8).$hyphen
	.substr($charid, 8, 4).$hyphen
	.substr($charid,12, 4).$hyphen
	.substr($charid,16, 4).$hyphen
	.substr($charid,20,12)
	.chr(125);// "}"
	return $uuid;
}
