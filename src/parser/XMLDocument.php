<?php


namespace Snono\StreamParser\parser;


use Illuminate\Support\Facades\Log;

class XMLDocument
{
    private $tagstack;
    private  $xmlvarArrPos;
    private $xmlvals;
    private $data;

    function __construct($data)  // constructor to intialize the stack and val array
    {
        Log::info($data);
        $this->data = $data;
        $this->tagstack = array();   // contain the open tags till now
        $this->xmlvals = array();
        $this->xmlvarArrPos = $this->xmlvals;  // temporary variable to hold the current tag position
    }

    public function parser(): array
    {
        // read the XML database

        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, $this->data, $values, $tags);
        xml_parser_free($parser);
        $tagStackPointer = 0;
        foreach($values as $key => $val)  //
        {
            if($val['type'] == "open")
            {
                array_push($this->tagstack, $val['tag']);
                $this->getArrayPath();
                if(is_array($this->xmlvarArrPos) && sizeof($this->xmlvarArrPos) > 0 && (!array_key_exists(0,$this->xmlvarArrPos)))
                {
                    $temp1 = $this->xmlvarArrPos;
                    $this->xmlvarArrPos =  array();
                    $this->xmlvarArrPos[0] = $temp1;
                    array_push($this->tagstack, 1);
                }
                else if( is_array($this->xmlvarArrPos) &&  array_key_exists(0,$this->xmlvarArrPos)){
                    $opncount = sizeof($this->xmlvarArrPos);
                    array_push($this->tagstack, $opncount);
                }
                $tagStackPointer += 1;
            }else if($val['type'] == "close")
            {
                while( $val['tag'] != ($lastOpened = array_pop($this->tagstack))){}
            }else if($val['type'] ==  "complete")
            {
                $this->getArrayPath();
                if(is_array($this->xmlvarArrPos) &&  array_key_exists($val['tag'],$this->xmlvarArrPos))
                {
                    if(array_key_exists(0,$this->xmlvarArrPos[$val['tag']]))
                    {
                        $elementCount = sizeof($this->xmlvarArrPos[$val['tag']]);
                        $this->xmlvarArrPos[$val['tag']][$elementCount] = $val['value'];
                    }else
                    {
                        $temp1 = $this->xmlvarArrPos[$val['tag']];
                        $this->xmlvarArrPos[$val['tag']] =  array();
                        $this->xmlvarArrPos[$val['tag']][0] = $temp1;
                        $this->xmlvarArrPos[$val['tag']][1] = $val['value'];
                    }
                } else
                {
                    if(isset($val['value'])) $this->xmlvarArrPos[$val['tag']] = $val['value'];
                }
            }
        }
//        reset($this->xmlvals);
        return $this->xmlvals;
    }

    private function getArrayPath()
    {

        reset($this->xmlvals);
        $this->xmlvarArrPos = &$this->xmlvals;
        foreach($this->tagstack as $key)
        {
            $this->xmlvarArrPos = &$this->xmlvarArrPos[$key];

        }
    }
}
