<?php

namespace App;

use Illuminate\Support\Facades\Log;
use League\Flysystem\Exception;

class AgentWhitelistedDomains
{
    public static function get_domain($url)
    {
        $pieces = parse_url($url);

        Log::info($pieces);

        $scheme = isset($pieces['scheme']) ? $pieces['scheme'] : null;
        $domain = isset($pieces['host']) ? $pieces['host'] : $pieces['path'];

        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
            $domain = $regs['domain'];
        }

        if (isset($domain) && isset($scheme)) {
            return $scheme . '://' . $domain;
        }
        else return null;
    }

    public static function whitelistUniqueExternalLinks($urls, $agent_id) {
        try {
            if ($urls) {
                $agent = Agents::find($agent_id);

                if (isset($agent)) {

                    $page_access_token = $agent->fb_access_token;

                    if (!$page_access_token) {
                        throw new Exception("Cannot add external urls unless your bot is connected to your facebook page");
                    }

                    $domains = [];

                    foreach($urls as $url) {
                        $domain = AgentWhitelistedDomains::get_domain($url);
                        if (isset($domain) && in_array($domain, $domains) != true) {
                            array_push($domains, $domain);
                        }
                    }

                    $requiredList = [];
                    $white_list_links = json_decode($agent->white_list_links, true);

                    if ($domains) {

                        if (count($domains) > 5) {
                            throw new Exception("You cannot whitelist more than FIVE different domains! Please use resources restricted to FIVE domains");
                        }

                        if ($white_list_links) {
                            foreach($domains as $domain) {
                                if (in_array($domain, $white_list_links) != true) {
                                    array_push($requiredList, $domain);
                                }
                            }

                            if (count($white_list_links) + count($requiredList) > 5) {
                                throw new Exception("You cannot whitelist more than FIVE different domains! Please use resources restricted to FIVE domains");
                            }
                        }
                        else {
                            $requiredList = $domains;
                        }
                    }

//                    Log::info("Domain urls");
//                    Log::info(print_r($domains, true));

                    if($requiredList) {
//                        Log::info("Selected urls");
//                        Log::info(print_r($requiredList, true));

                        $response = FacebookAPI::manageAgentDomainsInWhitelisting('add', $domains, $page_access_token);

//                        Log::info("Whitelisting response:");
//                        Log::info($response);

                        if (isset($response['error']['message'])) {
                            throw new Exception($response['error']['message']);
                        }
                        else {
                            if ($white_list_links) {
                                $newDomainList = array_merge($white_list_links, $requiredList);
                                $agent->white_list_links = json_encode($newDomainList);
                            }
                            else {
                                $agent->white_list_links = json_encode($requiredList);
                            }
                            $agent->save();
                        }
                    }
                }
                else {
                    throw new Exception("Invalid agent_id");
                }
            }

        }catch(Exception $e ){
            throw $e;
        }
    }
}
