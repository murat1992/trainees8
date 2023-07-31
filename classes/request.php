<?php

session_start();

class CRequest
{
    private $action;
    private $pathResource;
    private $pathParentResource;
    private $params;
    private $type; //disk:, app: и т.д.
    private $arPath;
    
    public function __construct()
    {
        $this->action = "read";
        if (isset($_GET['action'])) {
            $this->action = $_GET['action'];
            unset($_GET['action']);
        }
        
        $resourceName = '/';
        if (isset($_GET['resource'])) {
            $resourceName = $_GET['resource'];
            unset($_GET['resource']);
        }

        $this->params = array();
        foreach ($_GET as $key => $value) {
            $this->params[$key] = $value;
        }
        foreach ($_POST as $key => $value) {
            $this->params[$key] = $value;
        }

        $this->url = parse_url($_SERVER["REQUEST_URI"]);


        $arPath = explode("/", trim($resourceName, "/"));
        $type = "disk:";
        switch ($arPath[0]) {
            case "disk:":
                $type = "disk:";
                array_splice($arPath, 0, 1);
                break;
            case "app:":
                $type = "app:";
                array_splice($arPath, 0, 1);
                break;
        }

        $count = count($arPath);
        switch ($count) {
            case 0:
                $this->pathResource = $type . "/";
                $this->pathParentResource = null;
                break;
            case 1:
                $this->pathResource = $type . "/" . $arPath[0];
                $this->pathParentResource = $type . "/";
                break;
            default:
                $path = $type;
                for ($i=0; $i < $count; $i++) {
                    $path .= "/" . $arPath[$i];
                    if ($i == $count - 2) {
                        $this->pathParentResource = $path;
                    }
                }
                $this->pathResource = $path;
        }
    }

    public function getToken()
    {
        if (isset($_SESSION['YA_TOKEN'])) {
            return $_SESSION['YA_TOKEN'];
        }
        return null;
    }

    public function setToken($token)
    {
        $_SESSION['YA_TOKEN'] = $token;
    }

    public function deleteToken() {
        unset($_SESSION['YA_TOKEN']);
    }

    public function getCode()
    {
        if (isset($_GET['code'])) {
            return $_GET['code'];
        }
        return null;
    }

    public function getParam($key)
    {
        if (isset($this->params[$key])) {
            return $this->params[$key];
        }
        return null;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getPathResource()
    {
        return $this->pathResource;
    }

    public function getParentResource()
    {
        return $this->pathParentResource;
    }

    public function getFile() {
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            return $_FILES['file'];
        }

        return null;
    }
}