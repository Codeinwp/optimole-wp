<?php
namespace OptimoleWP\Preload;

class Links {
    private static $links = [];
    private static $ids = [];
    public static function add_link(array $url_data){
        if(!isset($url_data['url'])){
            return;
        }
        if(OPTML_DEBUG  ){
            do_action('optml_log', 'add preload link : ' . print_r($url_data, true));
        }
        self::$links[crc32($url_data['url'])] = $url_data;
    }

    public static function add_id(string $id, $priority = 'auto'){
        if(OPTML_DEBUG  ){
            do_action('optml_log', 'add preload id : ' . $id . ' ' . $priority);
        }
        self::$ids[$id] = $priority;
    }
    public static function is_preloaded(string $id): string{
        return self::$ids[$id] ?? false;
    }
    public static function preload_tag(string $tag, string $priority = '')  {
       // Extract src, srcset, and sizes from the tag using regexes for each  one  
       $src = '';
       $srcset = '';
       $sizes = '';

       $src_pattern = '/<img[^>]+src=["|\']([^"|\']+)["|\']/i';
       $srcset_pattern = '/<img[^>]+srcset=["|\']([^"|\']+)["|\']/i';
       $sizes_pattern = '/<img[^>]+sizes=["|\']([^"|\']+)["|\']/i';

       if(preg_match($src_pattern, $tag, $matches)){
        $src = $matches[1];
       }
       if(preg_match($srcset_pattern, $tag, $matches)){
        $srcset = $matches[1];
       }
       if(preg_match($sizes_pattern, $tag, $matches)){      
        $sizes = $matches[1];
       }
       if(OPTML_DEBUG){
        do_action('optml_log', 'preload_tag: ' . print_r([
            'url' => $src,
            'srcset' => $srcset,
            'sizes' => $sizes,
            'priority' => $priority
            ] ,true ). ' ' . $priority);
       }
       // Add the preload link to the links array
       self::add_link([
        'url' => $src,
        'srcset' => $srcset,
        'sizes' => $sizes,
        'priority' => $priority
       ]);
    }
    public static function get_links(): array{
        return self::$links;
    }

    public static function get_links_count(): int{
        return count(self::$links);
    }

    public static function get_links_html(): string{ 
        //generate image preload links for all links
        $links = [];
        foreach(self::$links as $link){
            $url = esc_url($link['url']);
            if(empty($url)){
                continue;
            }
            $preload = '<link rel="preload" media="screen" href="' . $url . '" ';
            if (isset($link['priority']) && $link['priority'] !== 'auto') {
                $preload .= 'fetchpriority="' . esc_attr($link['priority']) . '" ';
            }
            // Add imagesrcset if available
            if (isset($link['srcset']) && ! empty($link['srcset'])) {
                $preload .= 'imagesrcset="' . esc_attr($link['srcset']) . '" ';
            } 
            // Add imagesizes if available
            if (isset($link['sizes']) && ! empty($link['sizes'])) {
                $preload .= 'imagesizes="' . esc_attr($link['sizes']) . '" ';
            }
            // Complete the preload tag
            $preload .= 'as="image">';
            
            $links[] = $preload;  
        } 
        return implode("\n", $links)."\n";
    }
}

