<?php
class XmlToArray {
	var $xml='';
	var $cdata_tag='cdata';
	var $encoding_input="UTF-8";
	var $encoding_output="UTF-8";
	
	function __construct($xml, $cdata_tag="cdata", $encoding_input="UTF-8", $encoding_output="UTF-8") {
		$this->xml = &$xml;
		$this->cdata_tag = ($cdata_tag)?$cdata_tag:$this->cdata_tag;
		$this->encoding_input = ($encoding_input)?$encoding_input:$this->encoding_input;
		$this->encoding_output = ($encoding_output)?$encoding_output:$this->encoding_output;
	}
	
	function XmlToArray($xml, $cdata_tag="cdata", $encoding_input="UTF-8", $encoding_output="UTF-8") {
		$this->__construct($xml, $cdata_tag, $encoding_input, $encoding_output);
	}

	function __destruct() {
		unset($this->xml, $this->cdata_tag, $this->encoding_input, $this->encoding_output);
	}
	
	function &get_array_element_by_keys(&$ARRAY,$KEYS=array(),$counter=0) {
		$ELEMENT=false;
		if (is_array($ARRAY)) {
			if (is_array($KEYS)&&count($KEYS)>0) {
				if ($counter+1<count($KEYS)&&isset($KEYS[$counter])) {
					if (isset($ARRAY[$KEYS[$counter]]))	$ELEMENT=&$this->get_array_element_by_keys($ARRAY[$KEYS[$counter]],$KEYS,$counter+1);
				} elseif (isset($KEYS[$counter])) $ELEMENT=&$ARRAY[$KEYS[$counter]];
				else $ELEMENT=&$ARRAY;
				} else $ELEMENT=&$ARRAY;
			}
		return $ELEMENT;
	}
	
	function struct_to_array(&$STRUCT) {
		$ARRAY=array();
		$ELEMENT=array();
		$MEMORY=array("PARENT"=>array(),"LEVEL"=>array());
		if (is_array($STRUCT)) {
			for ($counter=0; $counter < count($STRUCT); $counter++) {
				if ($STRUCT[$counter]['type']!='close') {
					if ($counter>0&&$STRUCT[$counter-1]['level']<$STRUCT[$counter]['level']) {
						$MEMORY['PARENT'][$STRUCT[$counter]['level']]=$counter-1;
					} elseif ($counter==0) $MEMORY['PARENT'][$STRUCT[$counter]['level']]=-1;
					$STRUCT[$counter]['parent']=($STRUCT[$counter]['level']>1)?$MEMORY['PARENT'][$STRUCT[$counter]['level']]:-1;
					$STRUCT[$counter]['index']=0;
					$STRUCT[$counter]['CHILDREN']="";
					$KEYS=array();
					for ($level=2; $level<=$STRUCT[$counter]['level']; $level++) {
						$KEYS[]=$STRUCT[$MEMORY['PARENT'][$level]]['tag'];
						if ( isset($STRUCT[$MEMORY['PARENT'][$level]]['parent']) && $STRUCT[$MEMORY['PARENT'][$level]]['parent']>-1 )
						if (isset($STRUCT[ $STRUCT[$MEMORY['PARENT'][$level]]['parent'] ]['CHILDREN'][$STRUCT[$MEMORY['PARENT'][$level]]['tag']]) && (int)$STRUCT[ $STRUCT[$MEMORY['PARENT'][$level]]['parent'] ]['CHILDREN'][$STRUCT[$MEMORY['PARENT'][$level]]['tag']]>1) {
							$KEYS[]=(int)$STRUCT[ $STRUCT[$MEMORY['PARENT'][$level]]['parent'] ]['CHILDREN'][$STRUCT[$MEMORY['PARENT'][$level]]['tag']]-1;
						}
					}
					$INTEGRAL="";
					if (is_array($STRUCT[$counter]['attributes'])) $INTEGRAL=$STRUCT[$counter]['attributes'];
					if (isset($STRUCT[$counter]['value'])) {
						if (is_array($INTEGRAL)) {
							if (isset($INTEGRAL[$this->cdata_tag])) {
								if (is_array($INTEGRAL[$this->cdata_tag])) $INTEGRAL[$this->cdata_tag][]=$STRUCT[$counter]['value']; else $INTEGRAL[$this->cdata_tag]=array($INTEGRAL[$this->cdata_tag],$STRUCT[$counter]['value']);
							} else $INTEGRAL[$this->cdata_tag]=$STRUCT[$counter]['value'];
						} else $INTEGRAL=$STRUCT[$counter]['value'];
					}
				

					$ELEMENT=&$this->get_array_element_by_keys($ARRAY,$KEYS);
					if ($counter>0 && isset($STRUCT[$STRUCT[$counter]['parent']]['CHILDREN'][$STRUCT[$counter]['tag']])) {
						if ($STRUCT[$STRUCT[$counter]['parent']]['CHILDREN'][$STRUCT[$counter]['tag']]>1) $ELEMENT[ $STRUCT[$counter]['tag'] ][ (int)$STRUCT[$STRUCT[$counter]['parent']]['CHILDREN'][$STRUCT[$counter]['tag']] ]=$INTEGRAL;
		 				else $ELEMENT[$STRUCT[$counter]['tag']]=array($ELEMENT[$STRUCT[$counter]['tag']],$INTEGRAL);
					} else $ELEMENT[$STRUCT[$counter]['tag']]=$INTEGRAL;
					unset($INTEGRAL);
					if ($counter>0) {
						$STRUCT[$STRUCT[$counter]['parent']]['CHILDREN'][$STRUCT[$counter]['tag']]=(isset($STRUCT[$STRUCT[$counter]['parent']]['CHILDREN'][$STRUCT[$counter]['tag']]))?(int)$STRUCT[$STRUCT[$counter]['parent']]['CHILDREN'][$STRUCT[$counter]['tag']]+1:1;
					}
				}
			}
		}
		
		unset($MEMORY);
		return $ARRAY;
	}
	
	function encoding_array (&$array) {
		if (is_array($array)) {
			reset($array);
			while(list($key,)=each($array)) {
				if(!is_array($array[$key])) {
					$array[$key]=iconv($this->encoding_input,$this->encoding_output,$array[$key]);
				} else {
					$this->encoding_array($array[$key]);
				}
			}
		}
	}
   

	function createArray() {
    	$STRUCT = array();
    	$index  = array();
    	$array  = array();
    	$parser = xml_parser_create();
    	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    	xml_parse_into_struct($parser, $this->xml, $STRUCT, $index);
		xml_parser_free($parser);
    	$ARRAY=$this->struct_to_array($STRUCT);
		unset($STRUCT);
		if ($this->encoding_input&&$this->encoding_output&&$this->encoding_input!=$this->encoding_output) $this->encoding_array($ARRAY);
    	return $ARRAY;
	}
}
?>