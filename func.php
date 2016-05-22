<?php
libxml_use_internal_errors(true);

function e($str){
	echo "<br><br>$str<br><br>";

}

function xodd($texto){
	return (float) str_replace(  array('a','x','c','t','e','o','p','z'), array('1','2','3','4','5','6','7','.'), $texto );
}



function get_string_between($string, $start, $end){
    $string = " ".$string;
    $ini = strpos($string,$start);
    if ($ini == 0) return "";
    $ini += strlen($start);
    $len = strpos($string,$end,$ini) - $ini;
    return substr($string,$ini,$len);
}


function getHashJogo($cod){
	$url="http://www.oddsportal.com/soccer/1/1/1-$cod/";
	$str=loadURL( $url, true);
	
	return urldecode( get_string_between($str, '"xhash":"', '","') );

}


function getIdLiga($pais, $liga_ano){
	$url="http://www.oddsportal.com/soccer/$pais/$liga_ano/results/";
	$out=loadURL($url, true);
	list($x, $out)=explode('({"id":"', $out);
	
	return substr($out,0,8);
}




function getOdds25Bet365($cod, $hash){


	
	$url="http://fb.oddsportal.com/feed/match/1-1-$cod-2-2-$hash.dat";
	
	$str=loadURL( $url, true);
	

	$str=substr($str, 64);
	$str=substr($str, 0,-2);
	
	$obj = json_decode($str);
	
	$o=$obj->{'d'}->{'oddsdata'}->{'back'}->{'E-2-2-0-2.5-0'}->{'odds'}->{'16'};
	
	
	return $o;


}


function getOdds1x2Bet365($cod, $hash){

	
	
	$url="http://fb.oddsportal.com/feed/match/1-1-$cod-1-2-$hash.dat";
	
	$str=loadURL( $url, true);
	

	$str=substr($str, 64);
	$str=substr($str, 0,-2);
	
	$obj = json_decode($str);
	
	$o=$obj->{'d'}->{'oddsdata'}->{'back'}->{'E-1-2-0-0-0'}->{'odds'}->{'16'};
	
	
	return $o;


}

function loadURLX($urls){
	
	$mh = curl_multi_init();
	
	$ch=array();
	for($i=0;$i<count($urls);$i++){
		$ch[$i]=curl_init();		
		
		curl_setopt($ch[$i], CURLOPT_URL, $urls[$i]);
		curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, true);		
		curl_setopt($ch[$i], CURLOPT_USERAGENT, "Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5");
		curl_setopt($ch[$i], CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch[$i], CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch[$i], CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch[$i], CURLOPT_COOKIESESSION, true);
		curl_setopt($ch[$i], CURLOPT_COOKIEJAR, "cookie.txt");
	    	curl_setopt($ch[$i], CURLOPT_COOKIEFILE, "cookie.txt");
	    	
	    	curl_multi_add_handle($mh,$ch[$i]);
	    	
	
	}
	
	$active = null;
	//execute the handles
	do {
	    $mrc = curl_multi_exec($mh, $active);
	    
	    
	} while ($mrc == CURLM_CALL_MULTI_PERFORM);
	
	while ($active && $mrc == CURLM_OK) {
	    if (curl_multi_select($mh) != -1) {
	        do {
	            $mrc = curl_multi_exec($mh, $active);
	            
	            
	        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
	    }
	    
	    
	}
	
	
	foreach($ch as $c){	
		$outputs[]=curl_multi_getcontent($c);
		curl_multi_remove_handle($mh, $c);	
	}
		
	curl_multi_close($mh);
	
	return($outputs);
	
}

	
	
		
function strToXML($str){	

	$str=str_replace('=', '="',$str);
	$str=str_replace(';', '" ',$str);
	
	$arr=explode('|',$str);	
	
	unset($arr[ count($arr) -1] );
	unset($arr[0]);
	
	$saida[]='<MATCH>';
	foreach($arr as $a){
		//$stats=explode(';', $a);
		
		//foreach($stats as $s){
		//	$saida[]=$s;
		
		//}
		
		
		$inicio_tag='<' .substr($a,0,2);
		
		if( in_array($inicio_tag,array('<MA', '<SC'))  ){
			$saida[]=$inicio_tag  . substr($a,3) .'>' . "\n";
		}
		else{
			$saida[]=$inicio_tag  . substr($a,3) .'/>' . "\n";
		}
		
		
		
	}
	//$saida=str_replace("\n<_timeF/>",'',$saida);
	$saida[]="</MATCH>";
	
	
	
	$str='';
	$tag_ANT='';
	for($i=0; $i<count($saida); $i++ ){
		
		$tag=substr($saida[$i],1,2);
		if( ( $tag_ANT=='PA') && ($tag!='PA') ) $saida[$i]="</MA>\n" . $saida[$i];
		if( ( $tag_ANT=='SL') && ($tag!='SL') ) $saida[$i]="</SC>\n" . $saida[$i];

		
		
		$tag_ANT=$tag;
		
		
		
		if( $tag=='CO')  $saida[$i]='';
		$str.=$saida[$i];
		
		
	}
	


	$str=str_replace("\n<_timeF/>",'',$str);
	$xmlDoc = new DOMDocument('1.0','utf-8');
	try {
		$xmlDoc->loadXML($str );
		
	} catch (Exception $e) {
		echo $str;
		$xmlDoc=false;
	}
	return( $xmlDoc );
	

}




//POSTDATA=login-username=ronaldoaf&login-password=rr842135&login-submit=

function POST($url,$campos,$string=false){
	$n_campos=substr_count($campos,'='); 

	$xmlDoc = new DOMDocument();
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 5.1; rv:6.0.2) Gecko/20100101 Firefox/6.0.2");
	//curl_setopt($ch, CURLOPT_HEADER, 1);  
	//curl_setopt($ch,CURLOPT_HTTPHEADER,array('Host: www1.caixa.gov.br','Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8','Connection: keep-alive'));
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($ch, CURLOPT_COOKIESESSION, true);
	curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");
    	curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie.txt");
	curl_setopt($ch,CURLOPT_POST,$n_campos);
	curl_setopt($ch,CURLOPT_POSTFIELDS,$campos);
	$output = curl_exec($ch);
	//echo $output;
	//Se string==true retorna string sen�o retorna um Documento HTML
	if($string) return($output);
	
	//$xmlDoc->xmlEncoding='utf-8';
	@$xmlDoc->loadHTML($output);
	curl_close($ch);
	
    	return($xmlDoc);
    	
}

function loadURL($url,$string=false){

	

	$xmlDoc = new DOMDocument('1.0','utf-8');	
	
	$ch = curl_init();	
	
	curl_setopt($ch, CURLOPT_URL, $url);
	//curl_setopt($ch, CURLOPT_PROXY, '177.136.113.38:8080');    // Set CURLOPT_PROXY with proxy in $proxy variable	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);		
	
	//curl_setopt($ch, CURLOPT_USERAGENT, "spider");
	//curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 5.1; rv:6.0.2) Gecko/20100102 Firefox/18.0.2");
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5");
	//curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Linux; Android 4.2.2; en-us; SAMSUNG GT-I9195 Build/JDQ39) AppleWebKit/535.19 (KHTML, like Gecko) Version/1.0 Chrome/18.0.1025.308 Mobile Safari/535.19");
	//curl_setopt($ch, CURLOPT_HEADER, 1);  
	//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	//curl_setopt($ch, CURLOPT_VERBOSE, true);
	//curl_setopt($ch,CURLOPT_HTTPHEADER,array('Host: www1.caixa.gov.br','Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8','Connection: keep-alive'));
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($ch, CURLOPT_COOKIESESSION, true);
	curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");
    	curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie.txt");


	$output = curl_exec($ch);
	//$errmsgcurl  = curl_error($ch);

	//echo $output;
	//Se string==true retorna string sen�o retorna um Documento HTML
	if($string) return($output);
	
	@$xmlDoc->loadHTML(removeAcentos($output) );
	curl_close($ch);	
    	return($xmlDoc);    	
}



function removeAcentos($texto){

 	$a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
  	$b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
 
 	return(str_replace($a,$b,$texto));
 	//return(str_replace($a,$b,utf8_decode($texto)));
}









?>