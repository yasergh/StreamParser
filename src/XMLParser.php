<?php

namespace Snono\StreamParser;

use App\Http\Controllers\Auth\LoginController;
use App\Models\Product\Product;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Snono\StreamParser\parser\XMLDocument;

class XMLParser  {

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
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//        Log::info($httpcode);
//        Log::info($result);
        if($httpcode != 200){
            throw new Exception('File not found or link is down Url:' .$url);
        }
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
            if(isset($this->xmlVals[key($schema)])) {
                foreach ($this->xmlVals[key($schema)] as $keyEle => $dataEle) {
                    if ($keyEle == key($row)) {
                        foreach ($dataEle as $dataFilter) {
                            $filterRow = array();
                            foreach ($row[key($row)] as $key => $value) {
                                Log::info("-------------dataFilter-------------");
//                                Log::info($dataFilter);
                                Log::info("value:".$value);
                                $filterRow[$key] = $this->arrayDotNotation($dataFilter, $value);
                            }
                            array_push($this->arrayMapping, $filterRow);
                        }
                    }
                }
            }else{
                throw new Exception("Notice: Undefined offset: ".key($schema));
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
        Log::info('-------------dddddddddddddddddddddddddd---------------');
//        Log::info($arrDotStr);
        for($i=0 ;  $i<sizeof($arrDotStr); $i++){
            $arrDotStrSub = explode(':', $arrDotStr[$i]);
            Log::info('-----------$arrDotStrSub--------');
//            Log::info($arrDotStrSub);

            if(sizeof($arrDotStrSub) == 1 && isset($array[$arrDotStr[$i]])) {
                Log::info('-----------Enter on 1--------');
                $array = $array[$arrDotStr[$i]];
            }else{
                Log::info('-----------Enter on 2--------');
                Log::info('arrDotStr[$i]: '.$arrDotStr[$i]);
                Log::info($arrDotStrSub);
                if(sizeof($arrDotStrSub) > 1 && is_array($array)){
                    Log::info('-----------Enter on 2.true--------');
                    Log::info($array);
                    $subArr = array();
                    if(is_scalar($array)) {
                        Log::info('-----------is_scalar-1-----------');
                        foreach ($array as $arr) {
                            Log::info($arr);
                            $subArrTmp = array();
                            foreach ($arr as $key => $value) {
                                Log::info('Key2:'.$key);
                                Log::info($arrDotStrSub);
                                if (in_array($key, $arrDotStrSub)) {
                                    $subArrTmp[$key] = $value;
                                }
                            }
                            array_push($subArr, $subArrTmp);
                        }
                        $array = $subArr;
                    }else{
                        Log::info('-----------is_scalar-2-----------');
                        $subArrTmp = array();
                        foreach ($array as $arrKey => $arrValue) {
                            Log::info($arrValue);
                            if(is_array($arrValue)) {
                                foreach ($arrValue as $key => $value) {
                                    Log::info('Key3:' . $key);
                                    Log::info($arrDotStrSub);

                                    foreach ($arrDotStrSub as $nameField) {
                                        $arrName = explode('>', $nameField);
                                        Log::info('$arrName[0]'.$arrName[0]);
                                        if ($key == $arrName[0] && sizeof($arrName) > 1) {
                                            $subArrTmp[$arrName[1]] = $value;
                                        } elseif ($key == $arrName[0]) {
                                            $subArrTmp[$key] = $value;
                                        }else{
                                            $arrNameSub = explode('^', $arrName[0]);
                                            if(sizeof($arrNameSub) > 1){
                                                if(is_array($value)){
                                                    foreach ($value as $sKey => $sValue){
                                                        if($sKey == $arrNameSub[1] ){
                                                            $subArrTmp[$key] = $sValue;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }else{
                                Log::info('arr value'. $arrValue);
                                foreach ($arrDotStrSub as $nameField) {
                                    $arrName = explode('>', $nameField);
                                    Log::info('$arrName[0]'.$arrName[0]);
                                    if ($arrKey == $arrName[0] && sizeof($arrName) > 1) {
                                        $subArrTmp[$arrName[1]] = $arrValue;
                                    } elseif ($arrKey == $arrName[0]) {
                                        $subArrTmp[$arrKey] = $arrValue;
                                    }
                                }
                            }
                        }

                        $array = $subArrTmp;
                    }
                }else{
                    Log::info('-----------Enter on 2.false --------');
                    Log::info('-----------Enter start 3--------');
//                    Log::info($array);
//                    Log::info($arrDotStrSub);
                    Log::info('-----------Enter end 3--------');

                    $subArr= array();
                    $arrName = explode('>', $arrDotStrSub[0]);
                    foreach ($array as $arr) {
                        $subArrTmp = array();

                        if(is_array($arr)) {
                            foreach ($arr as $key => $value) {
                                Log::info('Key4:' . $key);
                                if ($key == $arrName[0]) {
                                    $subArrTmp[$arrName[1]] = $value;
                                }
                            }
                        }
                        array_push($subArr, $subArrTmp);
                    }
                    $array = $subArr;
                }

            }
        }
        return $array;
    }

    public function load(string $model ): self
    {
        $models= array();
        if(sizeof($this->arrayMapping) == 0){
            Log::error('Error run sequence function the data not mapping');
            return $this;
        }
        $path =  current($this->getNamespace($model));
        if(strlen($path) > 0){
            $baseModel = app($path);
            foreach ($this->arrayMapping as $row){
                foreach ($row as $key => $value){
                    if(is_array($value)){
                        $path =  current($this->getNamespace($key));
                        if(strlen($path) > 0) {
                            $models[$key] = app($path);
//                            Log::info('Model:'.$key);
//                            Log::info($models[$key]->getFillable());
//                            $models[$key]->setAttribute('id_product' ,$value);
                        }
                    }
                }
            }

        }



//        Log::info($cat->getFillable());
        return $this;
    }

    private function getNamespace($model){
        $dirs = glob('../app/Models/*/*');
        return array_map(function ($dir) use ($model) {
            if (basename($dir) == $model.'.php') {
                return ucfirst(str_replace(
                    '/',
                    '\\',
                    str_replace(['../', '.php'], '', $dir)
                ));
            }
        }, array_filter($dirs, function ($dir) use ($model) {
            return basename($dir) == $model.'.php' ? 1 : 0;
        }));
    }
}



