<?php

namespace App;

use Exception;
use Illuminate\Support\Facades\Log;

class RSSFeedProcessor
{


    public static function feedsFromRSS($uri) {
        try {
            $feeds = [];
            if ($uri) {
                $fileContents= file_get_contents($uri);
//                $fileContents = str_replace(array("\n", "\r", "\t"), '', $fileContents);
//                $fileContents = trim(str_replace('"', "'", $fileContents));

                $simpleXml = simplexml_load_string($fileContents, "SimpleXMLElement", LIBXML_NOCDATA);
                $json = json_encode($simpleXml);
                $feedArray = json_decode($json, true);

                // Get namespaces
                $namespaces = $simpleXml->getNamespaces(true);

                // Get Image urls
                if(isset($namespaces['media'])) {
                    $i = 0;
                    foreach($simpleXml->channel->item as $feed) {
                        $media = $feed->children($namespaces['media']);
                        if ($media) {
                            $image = $media->content->attributes()->url->__toString();
                            $feedArray['channel']['item'][$i++]['image'] = $image;
                        }
                    }
                }
                
//                Log::info("Parsed RSS:");
//                Log::info(print_r($feedArray, true));

                $feeds = $feedArray;
            }
            return $feeds;
        }catch (Exception $e) {
            throw $e;
        }
    }
}
