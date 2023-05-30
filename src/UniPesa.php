<?php


namespace Unipesa\Unipesa;


use Curl\Curl;

class UniPesa
{
    private $merchantId="cdeff82ac8398c54e3b2da6b3a1296f6af5ec3b4";
    private $publicId="cdefaa89db5dd0a47e5c1a5f89e5c6f2d4d2df70";
    private  $secretKey="c48ef6c51dd3640a6bcc7006fa4661b7edf2a7ead0af582d735b54a96fe6e3b2f19dbb6261a01a171464fb7661223306952488fd050826f7b2c9a97ebbfd6449";


    private $postData=array();

    public function __construct()
    {
        echo "Hello from unipesa";
        $this->postData['merchant_id']=$this->merchantId;
    }

    /**
     * Method pour payer avec unipesa
     * @param $paymentType => type de paiement
     * @param $phone => telephone du client.
     * @param $montant => montant
     * @param string $devise =>devise
     * @param string $country =>pays du client
     * @throws InvalidPaymentType =>au cas où le type de paiement est invalide.
     */
    public function pay($paymentType,$phone,$montant,$devise="USD",$pays="CD")
    {
        $providerId=0;

        switch($paymentType)
        {
            case "mpesa":
                $providerId=9;
                break;
            case "orange":
                $providerId=10;
                break;
            case "airtel":
                $providerId=17;
                break;
            case "africell":
                $providerId=19;
                break;
            default: throw new InvalidPaymentType("type de paiement invalide.");
        }

        $this->postData['provider_id']=$providerId;
        $this->postData['customer_id']=$phone;
        /***
         * Generer un order id.
         */
        $this->postData['order_id']=$this->generateOrderId();
        $this->postData['amount']=$montant;
        $this->postData['currency']=$devise;
        $this->postData['country']=$pays;
        $this->postData['callback_url']="";

        /**
         * Generer la signature.
         */
        $this->postData['signature']=$this->generateSignature($this->postData);

        /**
         * Lancer la requete.
         */
        $response=$this->executeRequest("","POST",$this->postData);
        echo $response; exit();

    }

    /**
     * Method pour générer un order id.
     * @return string
     */
    private function generateOrderId()
    {
        $o=rand(111,9).rand(100,555);

        return $o;
    }

    /**
     * Method pour générer  une signature.
     * @param array $data
     * @param string $secretKey
     * @param string $currentParamPrefix
     * @param int $depth
     * @param int $currentRecursionLevel
     * @return string => signature générée.
     */
    private function generateSignature(array $data, $currentParamPrefix = '',  $depth = 16,  $currentRecursionLevel = 0 )
    {
        $secretKey=$this->secretKey;

        if ($currentRecursionLevel >= $depth)
        {
            throw new Exception('Recursion level exceeded');
        }

        $stringForSignature = '';

        foreach ($data as $key => $value)
        {
            if (is_array($value))
            {
                $stringForSignature .= $this->generateSignature(
                    $value,
                    "$currentParamPrefix$key.",
                    $depth,
                    $currentRecursionLevel + 1
                );
            } else if ($key !== 'signature')
            {
                $stringForSignature .= "$currentParamPrefix$key" . $value;
            }
        }

        if ($currentRecursionLevel == 0)
        {
            return strtolower(hash_hmac('sha512', $stringForSignature, $secretKey));

        }
        else
        {
            //return $StringForSignature;
            return $stringForSignature;
        }
    }

    /**
     * Method pour éxécuter une requete http via curl.
     * @param array $headers
     * @param string $requestType
     * @param array $body
     * @return false|string => reponse de la requete.
     */
    private function executeRequest($url,array $headers,$requestType,array $body)
    {
        $curl=new Curl();

        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, 0);
        $curl->setOpt(CURLOPT_SSL_VERIFYHOST, 0);

        $curl->setOpt(CURLOPT_FOLLOWLOCATION,true);
        $curl->setOpt(CURLOPT_RETURNTRANSFER,true);

        /**
         * Set headers.
         */
        foreach($headers as $key=>$val)
        {
            $curl->setHeader($key,$val);
        }

        if($requestType=="POST")
        {
            $curl=$curl->post($url,$body);
        }
        else if($requestType=="GET")
        {
            $curl=$curl->get($url,$body);
        }

        /**
         * Executer la requete http.
         */
        $curl->exec();

        /**
         * Obtenir la reponse.
         */
        $response=$curl->getResponse();

        return $response;


    }
}