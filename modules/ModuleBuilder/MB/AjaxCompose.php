<?php
class AjaxCompose
{
    public $sections = array();
    public $crumbs = array('Home'=>'ModuleBuilder.main("Home")',/* 'Assistant'=>'Assistant.mbAssistant.xy=Array("650, 40"); Assistant.mbAssistant.show();'*/);
    public function addSection($name, $title, $content, $action='activate')
    {
        $crumb = '';
        if ($name == 'center') {
            $crumb = $this->getBreadCrumb();
        }
        $this->sections[$name] = array('title'=>$title,'crumb'=>$crumb, 'content'=>$content, 'action'=>$action);
    }
    
    public function getJavascript()
    {
        if (!empty($this->sections['center'])) {
            if (empty($this->sections['east'])) {
                $this->addSection('east', '', '', 'deactivate');
            }
            if (empty($this->sections['east2'])) {
                $this->addSection('east2', '', '', 'deactivate');
            }
        }
        
        $json = getJSONobj();
        return $json->encode($this->sections);
    }
    
    public function addCrumb($name, $action)
    {
        $this->crumbs[$name] = $action;
    }
    
    public function getBreadCrumb()
    {
        $crumbs = '';
        $actions = array();
        $count = 0;
        $icon_back = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/></svg>';
        $icon_home = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house" viewBox="0 0 16 16"><path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.707 1.5ZM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5 5 5Z"/></svg>';
        foreach ($this->crumbs as $name=>$action) {
            if ($name == 'Home') {
                // $crumbs .= "<a onclick='$action' href='javascript:void(0)'>". getStudioIcon('home', 'home', 16, 16) . '</a>';
                $crumbs .= "<a onclick='$action' href='javascript:void(0)'>". $icon_home . '</a>';
            } else {
                if ($name=='Assistant') {
                    $crumbs .= "<a id='showassist' onclick='$action' href='javascript:void(0)'>". getStudioIcon('assistant', 'assistant', 16, 16) . '</a>';
                } else {
                    if ($count > 0) {
                        $crumbs .= '&nbsp;>&nbsp;';
                    } else {
                        $crumbs .= '&nbsp;|&nbsp;';
                    }
                    if (empty($action)) {
                        $crumbs .="<span class='crumbLink'>$name</span>";
                        $actions[] = "";
                    } else {
                        $crumbs .="<a href='javascript:void(0);' onclick='$action' class='crumbLink'>$name</a>";
                        $actions[] = $action;
                    }
                    $count++;
                }
            }
        }
        if ($count > 1 && $actions[$count-2] != "") {
            $crumbs = "<a onclick='{$actions[$count-2]}' href='javascript:void(0)'>". getStudioIcon('back', 'back', 16, 16) . '</a>&nbsp;'. $crumbs;
        }
        return $crumbs;
    }
    
    public function echoErrorStatus($labelName='')
    {
        $sections = array('failure'=>true,'failMsg'=>$labelName);
        $json = getJSONobj();
        echo $json->encode($sections);
    }
}
