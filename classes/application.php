<?php

require 'request.php';
require 'auth.php';
require 'viewer.php';

class CApplication
{

    const CLIENT_ID = "8faaa6390e784e8fa5aecdd580d7acd2";
    const CLIENT_SECRET = "52d81808aaad459e971202a7da5bf70e";

    private $request;
    private $auth;

    public function handle()
    {
        $request = new CRequest();
        $token = $request->getToken();
        if (!$token) {
            $code = $request->getCode();
            if (!$code) {
                //header("Location: https://oauth.yandex.ru/authorize?client_id=" . self::CLIENT_ID . "&response_type=code");
                //exit;
            } else {
                $this->auth = new CAuth(self::CLIENT_ID, self::CLIENT_SECRET);
                $token = $this->auth->getToken($code);
                $request->setToken($token);
            }
        }
        $this->request = $request;

        switch ($request->getAction()) {
            case "read":
                $this->read();
                break;
            case "create":
                $this->create();
                break;
            case "delete":
                $this->delete();
                break;


            case "download":
                echo "###";
                $this->download();
                break;
            case "logout":
                $request->deleteToken();
                header("Location: /");
                break;
        }
    }

    private function download()
    {
        $request = $this->request;
        $path = $request->getPathResource();
        $pathParent = $request->getParentResource();

        $disk = new Arhitector\Yandex\Disk();
        $disk->setAccessToken($request->getToken());

        $tempfile = $_SERVER['DOCUMENT_ROOT'] . "\\temp";
        try {
            $resource = $disk->getResource($path);
            $resource->download($tempfile);

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($request->getParam('filename')));
            header('Content-Transfer-Encoding: binary');
            //header('Expires: 0');
            //header('Cache-Control: must-revalidate');
            //header('Pragma: public');
            header('Content-Length: ' . filesize($tempfile));
            
            ob_end_clean();
            readfile($tempfile);
        } finally {
            unlink($tempfile);
        }

        //header("Location: /?resource=" . $pathParent);
        //exit();
    }

    /**
     * CRUD 
     */

    
    private function create()
    {
        $request = $this->request;
        $path = $request->getPathResource();

        $disk = new Arhitector\Yandex\Disk();
        $disk->setAccessToken($request->getToken());

        $dir = $request->getParam('dir_name');
        $file = $request->getFile();
        if ($dir) {
            $resource = $disk->getResource(trim($path, '/') . "/" .$dir);
            $resource->create();
        } elseif ($file) {
            $resource = $disk->getResource(trim($path, '/') . "/" .$file['name']);
            $resource->upload($file['tmp_name']);
        }
        header("Location: /?resource=" . $path);
        exit;
    }

    private function read()
    {
        $request = $this->request;
        $path = $request->getPathResource();
        $pathParent = $request->getParentResource();

        $limit = 20;
        $offset = $request->getParam('offset');
        if (!$offset) {
            $offset = 0;
        }
        $view = new CViewer($limit, $offset);

        $html = $view->getHeader();
        $token = $request->getToken();
        if ($token) {
            $disk = new Arhitector\Yandex\Disk();
            $disk->setAccessToken($request->getToken());
            $resource = $disk->getResource($path, $limit, $offset);

            $html .= $view->getButtonExit();
            $html .= $view->getHTML($resource, $path, $pathParent);
        } else {
            $html .= $view->getButtonLogin(self::CLIENT_ID);
            $html .= $view->getHTML();
        }
        $html .= $view->getFooter();
        print($html);
    }

    private function update()
    {

    }

    private function delete()
    {
        $request = $this->request;
        $path = $request->getPathResource();
        $pathParent = $request->getParentResource();
        
        $disk = new Arhitector\Yandex\Disk();
        $disk->setAccessToken($request->getToken());
        $resource = $disk->getResource($path);
        $resource->delete();
        header("Location: /?resource=" . $pathParent);
        exit;
    }
}