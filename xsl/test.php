<?php

   $xslDoc = new DOMDocument();
   $xslDoc->load("test.xsl");

   $xmlDoc = new DOMDocument();
   $xmlDoc->load("test.xml");

   $proc = new XSLTProcessor();
   $proc->importStylesheet($xslDoc);
   echo $proc->transformToXML($xmlDoc);

?>
