<?php

use Arhitector\Yandex\Client\Exception\UnauthorizedException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Request;

class CAuth extends \Arhitector\Yandex\AbstractClient
{
    /**
     * @var string  ID приложения
     */
    private $clientOauth;

    /**
     * @var string  пароль приложения
     */
    private $clientOauthSecret = null;

    /**
     * Конструктор
     *
     * @param    string $clientID Идентификатор приложения
     * @param    string $clientSecret Пароль приложения
     *
     */
    public function __construct($clientID, $clientSecret)
    {
        parent::__construct();

        $this->clientOauth = $clientID;
        $this->clientOauthSecret = $clientSecret;
    }

    /**
     * Аутентификация проходит в теле запроса при формировании.
     *
     * @param  \Psr\Http\Message\RequestInterface $request
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    protected function authentication(RequestInterface $request)
    {
        return $request;
    }

    /**
     * Возвращает ID приложения
     *
     * @return string
     */
    public function getClientOauth()
    {
       return $this->clientOauth;
    }

    /**
     * Возвращает пароль приложения
     *
     * @return string
     */
    public function getClientOauthSecret()
    {
        return $this->clientOauthSecret;
    }
    
    /**
     * Получение токена по коду подтверждения
     * 
     * @param  string $code Код подтверждения для получения токена
     * 
     * @return string
     */
    public function getToken($code) {
        $request = new Request(rtrim(self::API_BASEPATH, ' /') . '/token', 'POST');
        $request->getBody()
            ->write(http_build_query([
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'client_id'     => $this->getClientOauth(),
				'client_secret' => $this->getClientOauthSecret(),
            ]));
        
        try {
            //$response = json_decode($this->send($request)->wait()->getBody());
            $response = $this->send($request);
            if ($response->getStatusCode() == 200) {
                $response = json_decode($response->getBody(), true);
                return (string) $response['access_token'];
            }
        } catch (\Exception $exc) {
            $response = json_decode($exc->getMessage());

            if (isset($response->error_description)) {
                throw new UnauthorizedException($response->error_description);
            }

            throw $exc;
        }
    }
}