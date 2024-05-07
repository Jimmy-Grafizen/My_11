<?php

//print_r($_GET);die;
    $options = array('i'=>$_GET['i'],'o'=>$_GET['o']);
    $doc_cache = array();
    $doc_temp = emptyTemp();


    $use_template = array_key_exists("t", $options) ? $options["t"] : "markdown";
    $title = array_key_exists("n", $options) ? $options["n"] : "API Description";
    $base="";
    /*if ($argc < 3 || array_key_exists("h", $options)) {
        printUsage();
        die();
    }*/

	$baseUrl="";
    if (!file_exists($options["i"])) {
        die("Input file does not exist !\n");
    }
    if (!file_exists($options["o"])) {
		$fp = fopen($options["o"], 'w');
	    fwrite($fp, "New File Created.");
	    fclose($fp);
		chmod($options["o"], 0777);
    }

    $pathinfo = pathinfo(realpath($options["i"]));
    $baseUrl=$_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].str_replace($_SERVER['DOCUMENT_ROOT'],"",$pathinfo['dirname']).'/';



    $out_file = fopen($options["o"], "w");
    if (!$out_file) {
        die("Could not create output file. \n");
    }

    $src_file = fopen($options["i"], "r");
   

    if ($src_file){
        while(($line = fgets($src_file)) !== false){
           // echo $line;
            //die;
			preg_match("/\*\s+name -\s+(.*)/", $line, $name);
            if ($name){
                $doc_temp["name"] = $name[1];
                continue;
            }

            /*preg_match("/\*\s+@url:\s+(.*)/", $line, $url);
            if ($url){
                $doc_temp["url"] = $url[1];
                continue;
            }*/
            
            
            preg_match("/\*\s+Header Params -\s+(.*)/", $line, $header);
            if ($header){
                $doc_temp["headers"] = $header[1];
                continue;
            }
            
            preg_match("/\*\s+params -\s+(.*)/", $line, $param);
            if ($param){
                $doc_temp["params"] = $param[1];
                continue;
            }

			$re = '/^\$[a-z]*->(get|post|put|delete|map)\(\'\/([a-zA-Z0-9_:\/]*)\'/';
			preg_match($re, $line, $route_data, PREG_OFFSET_CAPTURE, 0);
           // preg_match("/^\\$[a-z]*->(get|post|put|delete)\(\'(.*)\'/", $line, $route_data);
           // preg_match("/^\$[a-z]*->(get|post|put|delete)\(\'\/[a-zA-Z0-9_:\/]*\'/", $line, $route_data, PREG_OFFSET_CAPTURE, 0);
           // echo "hello".PHP_EOL;

            if ($route_data) { 
				

				$doc_temp["url"] = $route_data[2][0];
				$doc_temp["method"] = $route_data[1][0];
				if($doc_temp["name"]!="-"){
					array_push($doc_cache, array(
						"doc" => $doc_temp
					));
				}
                $doc_temp = emptyTemp();
            }
        }
        //print_r($doc_cache);die;
        
        fwrite($out_file, 
            template(dirname(__FILE__) . "/templates/". $use_template .".php",
                array(
                    "title" => $title, 
                    "baseurl"=>$baseUrl,
                    "contents" => $doc_cache)));
    } else {
        die("Could not open input file");
    }

    fclose($out_file);
    fclose($src_file);


    /* Declarations */

    function template($filename, $template_data){
        ob_start();
        if (file_exists($filename)){
            include($filename);
        }

        return ob_get_clean();
    }

    function emptyTemp(){
        return array(
            "name" => "-",
            "url" => "-",
            "method" => "-",
            "params" => "-",
            "headers" => "-"
        );
    }

    function printUsage(){
        print("Slim simple documentation generator.\n");
        print("Usage: slim_doc.php -i[input_file] -o[output_file]\n");
        print("Optional Parameters: \n\t-t[template] ( default : markdown )\n");
        print("\t-n[name] ( API Name header )\n");
        print("\t-h ( show help )\n");
    }
?>
