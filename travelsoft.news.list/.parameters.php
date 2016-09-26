<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */

if(!CModule::IncludeModule("iblock"))
	return;

$arTypesEx = CIBlockParameters::GetIBlockTypes(array("-"=>" "));

$arIBlocks=array();
$db_iblock = CIBlock::GetList(array("SORT"=>"ASC"), array("SITE_ID"=>$_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE"]!="-"?$arCurrentValues["IBLOCK_TYPE"]:"")));
while($arRes = $db_iblock->Fetch())
	$arIBlocks[$arRes["ID"]] = $arRes["NAME"];

$arSorts = array("ASC"=>GetMessage("T_IBLOCK_DESC_ASC"), "DESC"=>GetMessage("T_IBLOCK_DESC_DESC"));
$arSortFields = array(
		"ID"=>GetMessage("T_IBLOCK_DESC_FID"),
		"NAME"=>GetMessage("T_IBLOCK_DESC_FNAME"),
		"ACTIVE_FROM"=>GetMessage("T_IBLOCK_DESC_FACT"),
		"SORT"=>GetMessage("T_IBLOCK_DESC_FSORT"),
		"TIMESTAMP_X"=>GetMessage("T_IBLOCK_DESC_FTSAMP")
	);

$arProperty_LNS = array();
$rsProp = CIBlockProperty::GetList(array("sort"=>"asc", "name"=>"asc"), array("ACTIVE"=>"Y", "IBLOCK_ID"=>(isset($arCurrentValues["IBLOCK_ID"])?$arCurrentValues["IBLOCK_ID"]:$arCurrentValues["ID"])));

$param_prefix = 'AFP_';

$additionalParams[$param_prefix . 'ID'] = array(
			"PARENT" => "AFP",
			"NAME" => GetMessage("CP_AFP_ID"),
			"TYPE" => "STRING"
		);

while ($arr=$rsProp->Fetch())
{
    if ($arr['PROPERTY_TYPE'] == 'N') {
        
        $additionalParams[$param_prefix . "MIN_" .$arr['ID']] = array(
			"PARENT" => "AFP",
			"NAME" => $arr['NAME']. GetMessage("CP_AFP_MIN_VAL"),
			"TYPE" => "STRING"
		);
        $additionalParams[$param_prefix . "MAX_" .$arr['ID']] = array(
			"PARENT" => "AFP",
			"NAME" => $arr['NAME'].  GetMessage("CP_AFP_MAX_VAL"),
			"TYPE" => "STRING"
		);
       
    } else 
        if ($arr['PROPERTY_TYPE'] == 'E' || $arr['USER_TYPE'] == 'EList') {
            
            $values = array();
            if ($arr['LINK_IBLOCK_ID'] > 0) {
                
                if(!$obCache) {
                    $obCache = new CPHPCache(); 
                    $cdir = "/travelsoft/component-parameters";
                }
                
                if ($obCache->InitCache(36000, serialize(array($arr['ID'], $arr['LINK_IBLOCK_ID'])), $cdir)) {
                        $values = $obCache->GetVars();
                } elseif ($obCache->StartDataCache()) {
                    
                    $db_res = CIBlockElement::GetList(
                            array('NAME' => 'ASC'),
                            array('IBLOCK_ID' => $arr['LINK_IBLOCK_ID']),
                            false, false,
                            array('ID', 'NAME')
                        );
                
                    while ($res = $db_res->Fetch()) {

                        $values[$res['ID']] = $res['NAME']; 

                    }

                    if(defined("BX_COMP_MANAGED_CACHE")) {
                            global $CACHE_MANAGER;
                            $CACHE_MANAGER->StartTagCache($cdir);

                            $CACHE_MANAGER->RegisterTag("iblock_id_".$arr['LINK_IBLOCK_ID']);

                            $CACHE_MANAGER->EndTagCache();
                    }
                    
                        
                    $obCache->EndDataCache($values);
                }

                if (is_array($values) && !empty($values)) {
                    array_unshift($values, GetMessage("CP_AFP_CHOOSE"));
                    $additionalParams[$param_prefix .$arr['ID']] = array(
			"PARENT" => "AFP",
			"NAME" => $arr['NAME'],
                                        "TYPE" => "LIST",
                                        "VALUES" => $values,
                                        "MULTIPLE" => "Y"
		);
                }
                
            }
            
        } else
            if ($arr['USER_TYPE'] == 'UserID') {
                $values = array();
                if(!$obCache) {
                    $obCache = new CPHPCache(); 
                    $cdir = "/travelsoft/component-parameters";
                }
                
                if ($obCache->InitCache(36000, serialize(array($arr['ID'], "userid")), $cdir)) {
                        $values = $obCache->GetVars();
                } elseif ($obCache->StartDataCache()) {
                    
                    $db_res = CUser::GetList(($by="ID"), ($order="desc"), array(),array("SELECT"=>array("ID", "NAME")));
                    while ($res = $db_res->Fetch()) {

                        $values[$res['ID']] = $res['NAME']; 

                    }

                    if(defined("BX_COMP_MANAGED_CACHE")) {
                            global $CACHE_MANAGER;
                            $CACHE_MANAGER->StartTagCache($cdir);

                            $CACHE_MANAGER->RegisterTag("USER_CARD");

                            $CACHE_MANAGER->EndTagCache();
                    }
                    
                        
                    $obCache->EndDataCache($values);
                }
                
                if (is_array($values) && !empty($values)) {
                    array_unshift($values,  GetMessage("CP_AFP_CHOOSE"));
                    $additionalParams[$param_prefix .$arr['ID']] = array(
			"PARENT" => "AFP",
			"NAME" => $arr['NAME'],
                                        "TYPE" => "LIST",
                                        "VALUES" => $values,
                                        "MULTIPLE" => "Y"
		);
                }
                
            } else
                if ($arr['PROPERTY_TYPE'] == 'L') {
                    
                    $values = array();
                    $property_enums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("PROPERTY_ID" => $arr['ID'], "IBLOCK_ID"=>$arCurrentValues["IBLOCK_ID"]));
                    while($enum_fields = $property_enums->GetNext()) {
                      $values[$enum_fields["ID"]] = $enum_fields["VALUE"];
                    }
                    
                    if (is_array($values) && !empty($values)) {
                        array_unshift($values, GetMessage("CP_AFP_CHOOSE"));
                        $additionalParams[$param_prefix .$arr['ID']] = array(
                            "PARENT" => "AFP",
                            "NAME" => $arr['NAME'],
                            "TYPE" => "LIST",
                            "VALUES" => $values,
                            "MULTIPLE" => "Y"
                         );
                    }
                    
                }
    
    $arProperty[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
    if (in_array($arr["PROPERTY_TYPE"], array("L", "N", "S")))
    {
            $arProperty_LNS[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
    }
    
}

$arComponentParameters = array(
	"GROUPS" => array(
                    "AFP" => array(
                        "NAME" => GetMessage("CP_AFP_GROUP_NAME")
                    )
	),
	"PARAMETERS" => array(
		"AJAX_MODE" => array(),
		"IBLOCK_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_IBLOCK_DESC_LIST_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arTypesEx,
			"DEFAULT" => "news",
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_IBLOCK_DESC_LIST_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlocks,
			"DEFAULT" => '={$_REQUEST["ID"]}',
			"ADDITIONAL_VALUES" => "Y",
			"REFRESH" => "Y",
		),
		"NEWS_COUNT" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_IBLOCK_DESC_LIST_CONT"),
			"TYPE" => "STRING",
			"DEFAULT" => "20",
		),
		"SORT_BY1" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("T_IBLOCK_DESC_IBORD1"),
			"TYPE" => "LIST",
			"DEFAULT" => "ACTIVE_FROM",
			"VALUES" => $arSortFields,
			"ADDITIONAL_VALUES" => "Y",
		),
		"SORT_ORDER1" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("T_IBLOCK_DESC_IBBY1"),
			"TYPE" => "LIST",
			"DEFAULT" => "DESC",
			"VALUES" => $arSorts,
			"ADDITIONAL_VALUES" => "Y",
		),
		"SORT_BY2" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("T_IBLOCK_DESC_IBORD2"),
			"TYPE" => "LIST",
			"DEFAULT" => "SORT",
			"VALUES" => $arSortFields,
			"ADDITIONAL_VALUES" => "Y",
		),
		"SORT_ORDER2" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("T_IBLOCK_DESC_IBBY2"),
			"TYPE" => "LIST",
			"DEFAULT" => "ASC",
			"VALUES" => $arSorts,
			"ADDITIONAL_VALUES" => "Y",
		),
		"FILTER_NAME" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("T_IBLOCK_FILTER"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"FIELD_CODE" => CIBlockParameters::GetFieldCode(GetMessage("IBLOCK_FIELD"), "DATA_SOURCE"),
		"PROPERTY_CODE" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("T_IBLOCK_PROPERTY"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arProperty_LNS,
			"ADDITIONAL_VALUES" => "Y",
		),
		"CHECK_DATES" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("T_IBLOCK_DESC_CHECK_DATES"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"DETAIL_URL" => CIBlockParameters::GetPathTemplateParam(
			"DETAIL",
			"DETAIL_URL",
			GetMessage("T_IBLOCK_DESC_DETAIL_PAGE_URL"),
			"",
			"URL_TEMPLATES"
		),
		"PREVIEW_TRUNCATE_LEN" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_PREVIEW_TRUNCATE_LEN"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"ACTIVE_DATE_FORMAT" => CIBlockParameters::GetDateFormat(GetMessage("T_IBLOCK_DESC_ACTIVE_DATE_FORMAT"), "ADDITIONAL_SETTINGS"),
		"SET_TITLE" => array(),
		"SET_BROWSER_TITLE" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("CP_BNL_SET_BROWSER_TITLE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"SET_META_KEYWORDS" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("CP_BNL_SET_META_KEYWORDS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"SET_META_DESCRIPTION" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("CP_BNL_SET_META_DESCRIPTION"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"SET_LAST_MODIFIED" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("CP_BNL_SET_LAST_MODIFIED"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"INCLUDE_IBLOCK_INTO_CHAIN" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_INCLUDE_IBLOCK_INTO_CHAIN"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"ADD_SECTIONS_CHAIN" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_ADD_SECTIONS_CHAIN"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"HIDE_LINK_WHEN_NO_DETAIL" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_HIDE_LINK_WHEN_NO_DETAIL"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"PARENT_SECTION" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("IBLOCK_SECTION_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '',
		),
		"PARENT_SECTION_CODE" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("IBLOCK_SECTION_CODE"),
			"TYPE" => "STRING",
			"DEFAULT" => '',
		),
		"INCLUDE_SUBSECTIONS" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("CP_BNL_INCLUDE_SUBSECTIONS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"CACHE_TIME"  =>  array("DEFAULT"=>36000000),
		"CACHE_FILTER" => array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("IBLOCK_CACHE_FILTER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"CACHE_GROUPS" => array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("CP_BNL_CACHE_GROUPS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
	),
);

if ($additionalParams) {
    
  $arComponentParameters['PARAMETERS'] = array_merge( $arComponentParameters['PARAMETERS'], $additionalParams );

}

CIBlockParameters::AddPagerSettings(
	$arComponentParameters,
	GetMessage("T_IBLOCK_DESC_PAGER_NEWS"), //$pager_title
	true, //$bDescNumbering
	true, //$bShowAllParam
	true, //$bBaseLink
	$arCurrentValues["PAGER_BASE_LINK_ENABLE"]==="Y" //$bBaseLinkEnabled
);

CIBlockParameters::Add404Settings($arComponentParameters, $arCurrentValues);
