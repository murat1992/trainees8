<?php

class CViewer
{
    private $limit;
    private $offet;

    public function __construct($limit = 20, $offset = 0)
    {
        $this->limit = $limit;
        $this->offset = $offset;
    }

    private function getHTMLDir($resource, $path, $pathParent) {
        $result = "<table border=\"1\"><tr><th colspan=3>NAME</th></tr>";
        if ($pathParent) {
            $result .= "<tr><td colspan=3><a href=\"/?resource=" . $pathParent . "\">..</a></td></tr>";
        }
        foreach ($resource->items as $value) {
            $result .= "<tr>";

            switch ($value['type']) {
                case 'dir':
                    $result .= "<td><a href=\"\\?resource=" . $value['path'] . "\">" . $value['name'] . "</a></td>";
                    $result .= "<td></td>";
                    break;
                case 'file':
                    $result .= "<td>" . $value['name'] . "</td>";
                    //Пока не реализовано
                    $result .= "<td><a href=\"\\?resource=" . $value['path'] . "&action=download&filename=" . $value['name'] . "\">Скачать</a></td>";
                    break;
                default:
                    $result .= "<td>" . $value['type'] . "</td>";
            }
            $result .= "<td><a href=\"\\?resource=" . $value['path'] . "&action=delete\">удалить</a></td>";
            $result .= "</tr>";
        }
        $result .= "<tr><td colspan=3>";
        $result .= "<form method=\"POST\" action=\"\\?resource=" . $path . "&action=create\">";
        $result .= "<input type=\"text\" name=\"dir_name\" required>";
        $result .= "<input type=\"submit\" value=\"Добавить папку\"></form>";
        $result .= "</td></tr>";
        $result .= "</table>";

        if ($resource['total'] > $this->limit) {
            $result .= "<div class=\"navigation\">";
            
            $count = ceil($resource['total'] / $this->limit);
            $curPage = ceil($this->offset / $this->limit);
            for ($i = 0; $i < $count; $i++) {
                if ($i == $curPage) {
                    $result .= "<a>" . $i+1 . "</a>";
                } else {
                    $result .= "<a href=\"\\?resource=" . $path . "&offset=" . $i * $this->limit . "\">" 
                        . $i+1 . "</a>";
                }
                
            }

            $result .= "</div>";
        }

        $result .= "<form enctype=\"multipart/form-data\" method=\"POST\" action=\"\\?resource=" . $path . "&action=create\">";
        $result .= "<input type=\"file\" name=\"file\" required/>";
        $result .= "<input type=\"submit\"></form>";

        return $result;
    }

    public function getHeader() {
        $result = "<html><head><style>";
        $result .= "
            #left { width: 120px; }
            #left, #main {
                display: inline-block;
                vertical-align: top;
            }
            .BUTTON {
                display: block;
                background-color: red;
                color: white;
                padding: 15px;
                margin: 0 15px;
                text-align: center;
                border-radius: 10px;
            }
            form {
                margin: 15px;
            }
            td {
                padding: 5px;
            }
            td a {
                display: block;
                width: 100%;
            }
            .navigation a {
                margin: 0 5px;
            }
            ";

        $result .= "</style></head><body>";
        return $result;
    }

    public function getButtonLogin($clientID) {
        $result = "<div id=\"left\">";
        $result .= "<a class=\"BUTTON\" href=\"https://oauth.yandex.ru/authorize?client_id=$clientID&response_type=code\">";
        $result .= "Войти через яндекс</a></div>";
        
        return $result;
    }

    public function getButtonExit() {
        $result = "<div id=\"left\">";
        $result .= "<a class=\"BUTTON\" href=\"/?action=logout\">";
        $result .= "Выйти</a></div>";
        return $result;
    }

    public function getHTML($resource = null, $path = "", $pathParent = "")
    {
        $result = "<div id=\"main\">";
        if (!$resource) {
            $result .= "Пользователь не авторизован";
        } elseif ($resource->isDir()) {
            $result .= $this->getHTMLDir($resource, $path, $pathParent);
        } else {
            $result .= $resource['type'];
        }
        $result .= "</div>";

        return $result;
    }

    public function getFooter() {
        return "</body></html>";
    }
}