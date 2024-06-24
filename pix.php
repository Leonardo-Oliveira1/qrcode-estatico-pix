<?php 

class PayloadPix {

    const PAYLOAD_FORMAT_INDICATOR = '00';
    const MERCHANT_ACCOUNT_INFORMATION = '26';
    const MERCHANT_CATEGORY_CODE = '52';
    const TRANSACTION_CURRENCY = '53';
    const COUNTRY_CODE = '58';
    const VALOR_CODE = '54';
    const MERCHANT_NAME = '59';
    const MERCHANT_CITY = '60';
    const ADDITIONAL_DATA_FIELD_TEMPLATE = '62';
    const CRC16 = '63';

    private $chave;
    private $merchant_category_code;
    private $merchant_name;
    private $merchant_city;
    private $valor;
    private $txid;
    
    public function getChave()
    {
        return $this->chave;
    }

    public function setChave($chave)
    {
        $this->chave = $chave;

        return $this;
    }

    public function getMerchant_category_code()
    {
        return $this->merchant_category_code;
    }

    public function setMerchant_category_code($merchant_category_code)
    {
        $this->merchant_category_code = $merchant_category_code;

        return $this;
    }

    public function getMerchant_name()
    {
        return $this->merchant_name;
    }

    public function setMerchant_name($merchant_name)
    {
        $this->merchant_name = $merchant_name;

        return $this;
    }

    public function getMerchant_city()
    {
        return $this->merchant_city;
    }

    public function setMerchant_city($merchant_city)
    {
        $this->merchant_city = $merchant_city;

        return $this;
    }

    public function getValor()
    {
        return $this->valor;
    }

    public function setValor($valor)
    {
        $this->valor = str_replace(",", ".", $valor);

        return $this;
    }

    public function getTxid()
    {
        return $this->txid;
    }

    public function setTxid($txid)
    {
        $this->txid = $txid;

        return $this;
    }


    private function getFieldValue($constant, $value){
        $tam =  str_pad(strlen($value), 2, '0', STR_PAD_LEFT);

        return $constant.$tam.$value;
    }

    private function getCRC16($payload) {
        $payload .= self::CRC16.'04';
  
        //DADOS DEFINIDOS PELO BACEN
        $polinomio = 0x1021;
        $resultado = 0xFFFF;
  
        if (($length = strlen($payload)) > 0) {
            for ($offset = 0; $offset < $length; $offset++) {
                $resultado ^= (ord($payload[$offset]) << 8);
                for ($bitwise = 0; $bitwise < 8; $bitwise++) {
                    if (($resultado <<= 1) & 0x10000) $resultado ^= $polinomio;
                    $resultado &= 0xFFFF;
                }
            }
        }
  
        return self::CRC16.'04'.strtoupper(dechex($resultado));
    }

    private function getMerchantAccountInformation(){
        $gui = $this->getFieldValue("00", "br.gov.bcb.pix");
        $chave = $this->getFieldValue("01", $this->getChave()); 

        return $this->getFieldValue(self::MERCHANT_ACCOUNT_INFORMATION, $gui.$chave);
    }

    private function getAdditionalDataFieldTemplate(){
        $txid = $this->getFieldValue("05", $this->getTxid());
        
        return $this->getFieldValue(self::ADDITIONAL_DATA_FIELD_TEMPLATE, $txid);
    }

    private function getPixValueField(){
        if($this->getValor() > 0){
            return $this->getFieldValue(self::VALOR_CODE, $this->getValor());
        }
    }

    public function getPayload(){
        $payload = $this->getFieldValue(self::PAYLOAD_FORMAT_INDICATOR, "01").
               $this->getMerchantAccountInformation().
               $this->getFieldValue(self::MERCHANT_CATEGORY_CODE, $this->getMerchant_category_code()).
               $this->getFieldValue(self::TRANSACTION_CURRENCY, "986").
               $this->getPixValueField().
               $this->getFieldValue(self::COUNTRY_CODE, "BR").
               $this->getFieldValue(self::MERCHANT_NAME, $this->getMerchant_name()).
               $this->getFieldValue(self::MERCHANT_CITY, $this->getMerchant_city()).
               $this->getAdditionalDataFieldTemplate();

        return $payload.$this->getCRC16($payload);
    }
}
  

?>