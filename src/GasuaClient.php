<?php

namespace Livich\Gasua;


use DiDom\Document;
use DiDom\Element;
use GuzzleHttp\TransferStats;

class GasuaClient
{
    function __construct($options = [])
    {
        $defaultOptions = [
            'base_uri' => 'https://104.ua',
            'cookies' => true,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 6.2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3028.34 Safari/537.36'
            ]
        ];
        foreach ($options as $k => $v) {
            $defaultOptions[$k] = $v;
        }
        $this->http = new \GuzzleHttp\Client($defaultOptions);
    }

    public function login($username, $password)
    {
        $redirectHistory = [];
        $result = $this->http->get('https://104.ua/ua/cabinet'); //we must load login page before to get PHPSESSID
        $result = $this->http->request('POST', '/cabinet/login', [
            'form_params' => [
                '_username' => $username,
                '_password' => $password,
                '_target_path' => '',
                '_target_path_individual' => '/ua/cabinet',
                '_target_path_legal' => '/ua/cabinet-legal',
                '_remember_me' => '1'
            ],
            'on_stats' => function (TransferStats $stats) use (&$redirectHistory) {
                $redirectHistory[] = $stats;
            }
        ]);

        if(stristr((string)$result->getBody(),'Неправильный логін або пароль')){
            throw new GasuaLoginException();
        }
        return $this;
    }

    public function getAccountInfo()
    {
        $html = (string)$this->http->get('https://104.ua/ua/cabinet')->getBody();
        $document = new Document();
        $document->loadHtml($html);
        /** @var Element $infoBlock */
        $infoBlock = end($document->xpath('//span[@class="balance-date"]/..'));
        $infoElements = $infoBlock->find('p');
        $infoElements = array_merge($infoElements, $infoBlock->find('span'));
        if (!is_array($infoElements) || count($infoElements) < 8)
        {
           throw new GasuaClientException("Invalid HTML");
        }

        $results = [
            'balance_date' => new \DateTime(preg_replace('/[^0-9.\-+,]/','',$infoElements[6]->text())),
            'balance' => floatval(preg_replace('/[^0-9.\-+,]/','',$infoElements[0]->text())),
            'debt' => floatval(preg_replace('/[^0-9.\-+,]/','',$infoElements[2]->text())),
            'estimation' => floatval(preg_replace('/[^0-9.\-+,]/','',$infoElements[4]->text()))
        ];

        return $results;
    }

    function setMetersData($meter, $value, $date = null)
    {
        $result = $this->http->request('POST', '/ua/cabinet/info/method/setMetersData', [
            'form_params' => [
                'meter' => $meter,
                'value' => $value,
                'date' => date("d.m.Y", $date)
            ],
        ]);
        $result = json_decode($result->getBody(), true);
        if(empty($result['error']))
        {
            return true;
        }

        throw new \Exception($result['error']);
    }


}