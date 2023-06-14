<?php
/**
 * get post meta key. Also takes into account whether or not this post is a landing page
 * each regular meta key from the editor has the associated meta key for the landing page constructed by appending a "_{template_name}" after the key
 *
 * @param int $post_id
 * @param string $meta_key
 */
function tve_get_post_meta( $post_id, $meta_key, $single = true ) {}

/**
 * update a post meta key. Also takes into account whether or not this post is a landing page
 * each regular meta key from the editor has the associated meta key for the landing page constructed by appending a "_{template_name}" after the key
 *
 * @param $post_id
 * @param $meta_key
 * @param $value
 */
function tve_update_post_meta( $post_id, $meta_key, $meta_value ) {}
