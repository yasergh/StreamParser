<?php

namespace Snono\StreamParser;

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Log;
use Snono\StreamParser\parser\XMLDocument;

class XMLParser {

    private $xmlVals;
    private $data;
    private $arrayMapping = array();

    function __construct()  // constructor to intialize the stack and val array
    {
        $this->xmlVals = array();
    }

    public  function  setUrl($url)
    {
        $this->data = $this->getFileUrl($url);
        return $this;
    }

    function setFileName($filename)
    {
        $this->data = implode("", file($filename));
        return $this;
    }

    public function xmlParser(): self
    {
        $this->xmlVals = (new XMLDocument($this->data))->parser();
        reset($this->xmlVals);
        return $this;
    }

    /**
     * Get the content.
     *
     * @return mixed
     */
    public function getContent(): array
    {
        return  $this->xmlVals;
    }

    private function getFileUrl($url, $post = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        if(!empty($post)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * Parse document.
     *
     * @param  array  $schema
     *
     * @return array
     */
    public function mapping(array $schema): self
    {

        foreach ($schema as $row) {
            foreach ($this->xmlVals[key($schema)] as $keyEle => $dataEle) {
                if($keyEle == key($row)) {
                    foreach ($dataEle as $dataFilter) {
                        $filterRow = array();
                        foreach ($row[key($row)] as $key => $value) {
                              $filterRow[$key] = $this->arrayDotNotation($dataFilter, $value );
                        }
                        array_push($this->arrayMapping, $filterRow);
                    }
                }
            }
        }
        return $this;
    }

    public function toArray(): array
    {
        return $this->arrayMapping;
    }

    private function arrayDotNotation($array, $dotString){
        $arrDotStr = explode('.', $dotString);
        for($i=0 ;  $i<sizeof($arrDotStr); $i++){
            $arrDotStrSub = explode(':', $arrDotStr[$i]);

            if(sizeof($arrDotStrSub) == 1 && isset($array[$arrDotStr[$i]])) {
                $array = $array[$arrDotStr[$i]];
            }else{
                if(sizeof($arrDotStrSub) > 1 && is_array($array)){
                    $subArr = array();
                    if(is_scalar($array)) {
                        foreach ($array as $arr) {
                            $subArrTmp = array();
                            foreach ($arr as $key => $value) {
                                if (in_array($key, $arrDotStrSub)) {
                                    $subArrTmp[$key] = $value;
                                }
                            }
                            array_push($subArr, $subArrTmp);
                        }
                        $array = $subArr;
                    }else{
                        $subArrTmp = array();
                        foreach ($array as $key => $value) {
                            if (in_array($key, $arrDotStrSub)) {
                                $subArrTmp[$key] = $value;
                            }
                        }

                        $array = $subArrTmp;
                    }
                }

            }
        }
        return $array;
    }
}



