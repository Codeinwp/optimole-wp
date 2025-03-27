<?php

namespace OptimoleWP\BgOptimizer;

use OptimoleWP\PageProfiler\Profile;
use OptimoleWP\Preload\Links;
use Optml_Lazyload_Replacer;

class Lazyload {
    const MARKER = '/* OPTML_VIEWPORT_BG_SELECTORS */';
    public static function get_current_personalized_css(){
        return self::get_personalized_css(Profile::get_current_profile_data());
    }
 public static function get_personalized_css($data){
    $lazyload_selectors = array_values( Optml_Lazyload_Replacer::get_background_lazyload_selectors());
    $lazyload_selectors = array_fill_keys($lazyload_selectors, true);
    $css_selectors = [];
    $preload_urls = [];
    foreach(Profile::get_active_devices() as $device){
        $personalized_selectors = $data[$device]['bg'] ?? [];
        if(OPTML_DEBUG){
            do_action('optml_log', 'personalized_selectors: '. print_r($personalized_selectors, true));
        }
        $css_selectors[$device] = [];
        $preload_urls[$device] = [];
        foreach($personalized_selectors as $selector => $above_fold_selectors){
            if( ! isset($lazyload_selectors[$selector])){
                continue;
            }
            if(empty($above_fold_selectors)){
                $css_selectors[$device][] = 'html ' . $selector;
            }else{
                foreach($above_fold_selectors as $above_fold_selector => $bg_urls){
                    $css_selectors[$device][] = 'html ' . $selector . ':not(' . $above_fold_selector . ')';
                    $preload_urls[$device] = array_merge($preload_urls[$device], $bg_urls);
                }
            }   
        }
    }
    if(OPTML_DEBUG){
        do_action('optml_log', 'BGCSS selectors: '. print_r($css_selectors, true));
        do_action('optml_log', 'BGPreload URLs: '. print_r($preload_urls, true));
    }
    //Let's include now the LCP data.
    foreach(Profile::get_active_devices() as $device){
        $lcp_data = $data[$device]['lcp'] ?? [];
        if(OPTML_DEBUG){
            do_action('optml_log', 'LCP data: ' . $device . ' ' . print_r($lcp_data, true));
        }
        if(empty($lcp_data)){
            continue;
        }
        if($lcp_data['type'] !== 'bg'){
           continue;
        }
        $css_selectors[$device][] = 'html ' . $lcp_data['bgSelector'];
        $preload_urls[$device] = array_merge($preload_urls[$device], $lcp_data['bgUrls']);  

        $css_selectors[$device] = array_unique($css_selectors[$device]);
        $preload_urls[$device] = array_unique($preload_urls[$device]);
    }
    foreach( array_intersect($preload_urls[Profile::DEVICE_TYPE_MOBILE], $preload_urls[Profile::DEVICE_TYPE_DESKTOP]) as $url){
       Links::add_link(['url' => $url, 'priority' => 'high']);
    }
    if(!empty($preload_urls[Profile::DEVICE_TYPE_MOBILE]) && ! empty($preload_urls[Profile::DEVICE_TYPE_DESKTOP])){
        //For the urls that are present on one device and not the other we should preload them with medium priority.
        foreach(array_diff($preload_urls[Profile::DEVICE_TYPE_MOBILE], $preload_urls[Profile::DEVICE_TYPE_DESKTOP]) as $url){
            // Add srcset and sizes for mobile devices (max-width: 600px)
            Links::add_link([
                'url' => $url,
                'srcset' => $url . ' 600w',
                'sizes' => '(max-width: 600px) 100vw, 600px'
            ]); 
        }
        foreach(array_diff($preload_urls[Profile::DEVICE_TYPE_DESKTOP], $preload_urls[Profile::DEVICE_TYPE_MOBILE]) as $url){
            // Add srcset and sizes for desktop devices (min-width: 601px)
            Links::add_link([
                'url' => $url,
                'srcset' => $url . ' 1200w',
                'sizes' => '(min-width: 601px) 100vw'
            ]);
        }
    }
    $hide_rule = ' { background-image: none !important; }';
    $mobile_selectors = implode(',', $css_selectors[Profile::DEVICE_TYPE_MOBILE]) ;
    $desktop_selectors = implode(',', $css_selectors[Profile::DEVICE_TYPE_DESKTOP]) ;

    if($mobile_selectors === $desktop_selectors){
        return $mobile_selectors;
    }
    //if any of those are empty, return the other one
    if(empty($mobile_selectors)){
        return $desktop_selectors . $hide_rule;
    }
    if(empty($desktop_selectors)){
        return $mobile_selectors . $hide_rule;
    }

    //generate media query for desktop and mobile
    $media_query = '@media (max-width: 600px) { ' . $mobile_selectors.$hide_rule . ' } @media (min-width: 600px) { ' . $desktop_selectors . $hide_rule . ' }';
    return $media_query;
 }
}