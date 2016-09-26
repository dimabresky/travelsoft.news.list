<?php

/* 
 * Extantion for news.list component
 */


class  TravelsoftNewListComponent extends CBitrixComponent {
    
    public $dr;
    
    // include component_prolog.php
    public function includeComponentProlog()
    {
        $file = "component_prolog.php";

        $template_name = $this->GetTemplateName();

        if ($template_name == "")
            $template_name = ".default";

        $relative_path = $this->GetRelativePath();

        $this->dr = Bitrix\Main\Application::getDocumentRoot();

        $file_path = $this->dr . SITE_TEMPLATE_PATH . "/components" . $relative_path . "/" . $template_name . "/" . $file;

        $arParams = &$this->arParams;

        if(file_exists($file_path))
            require $file_path;
        else {

            $file_path = $this->dr . "/bitrix/templates/.default/components" . $relative_path . "/" . $template_name . "/" . $file;

            if(file_exists($file_path))
                require $file_path;
            else {
                $file_path = $this->dr . $this->__path . "/templates/" . $template_name . "/" . $file;
                if(file_exists($file_path))
                    require $file_path;
                else {

                    $file_path = $this->dr . "/local/components" . $relative_path . "/templates/" . $template_name . "/" . $file;

                    if(file_exists($file_path))
                        require $file_path;
                }

            }
        }
    }
    
    // make filter additional parameters
    public function makeFilterAdditionalParameters () {

        $prefixParam = "AFP_"; $minPrefix = "MIN_"; $maxPrefix = "MAX_";
        
        foreach ($this->arParams as $k => $v) {
            
            if (strpos($k, $prefixParam) === 0 && !empty($v)) {

                  $k = substr($k, strlen($prefixParam), strlen($k));
                  if (strpos($k, $minPrefix) === 0) {
                      $k = (int)substr($k, strlen($minPrefix), strlen($k));
                      if ($k > 0) {
                          $propFilter["><PROPERTY_" . $k][0] = trim($v);
                      }
                  } elseif (strpos($k, $maxPrefix) === 0) {
                      $k = (int)substr($k, strlen($maxPrefix), strlen($k));
                      if ($k > 0) {
                          $propFilter["><PROPERTY_" . $k][1] = trim($v);
                      }
                  } elseif ($k == "ID") {
                      $propFilter["ID"] = array_map(function ($var) { return trim($var); }, explode(",", $v));
                  } elseif ($k > 0) {
                      if (is_array($v)) {
                          $v = array_filter(array_map(function ($var) { return trim($var); }, $v), function ($var) {
                              return $var != "";
                          });
                          if (!empty($v)) {
                              $propFilter["PROPERTY_" . $k] = $v;
                          }
                      } else {
                          $propFilter["PROPERTY_" . $k] = trim($v);
                      }
                  }
            }
        }
        
        if ($propFilter) {
            
            if ($this->arParams['FILTER_NAME'] == "")
                $this->arParams['FILTER_NAME'] = "arrFilter";
            
            if ($GLOBALS[$this->arParams['FILTER_NAME']])
                $GLOBALS[$this->arParams['FILTER_NAME']] = array_merge ($GLOBALS[$this->arParams['FILTER_NAME']], $propFilter);
            else
                $GLOBALS[$this->arParams['FILTER_NAME']] = $propFilter;
            
        }

    }
    
    public function executeComponent () {

        $this->makeFilterAdditionalParameters();
        
        $this->includeComponentProlog();
        
        parent::executeComponent();
        
    }
    
}