<?php
/**
 * FAT PONY SOCIAL SHARE PLUGIN
 * @author      Erica Franz | Fat Pony
 * @link        http://fatpony.me/
 * @copyright   Copyright (c) 2014, Fat Pony
 * @license     GPL-2.0+
 * @version     1.0.1
 *
 * Adapted from the Social Share Starter Plugin by KK
 * @link        http://newinternetorder.com/giveaway-heres-why-social-share-counters-suck-plus-what-i-can-give-you-that-doesnt/
 */
 
//* Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) exit;

//* INCLUDE IN GENESIS CHILD-THEME
add_action( 'genesis_after_entry', 'fatpony_post_social', 1 );
function fatpony_post_social() {

/** INCLUDE IN WORDPRESS CHILD-THEME (remove previous three lines and 
*	uncomment the next two lines)
*/
//add_filter( 'wordpress_content', 'fatpony_post_social' );
//function fatpony_post_social( $content ) {

    /** Get our Pretty Link shortened url.
	*	If you don't use Pretty Link to shorten your urls you can delete this section,
	*	and modify the next conditional statement.
	*/
    if ( is_single() ) { 
        global $prli_link;
        $myID           = get_post_meta(get_the_ID(), '_pretty-link', true);
        $prettylink = '';
            if($myID) {
                $data   = $prli_link->getOne($myID);
                $link   = get_option('siteurl')."/".$data->slug;
                $prettylink .= $link;
            }
    }
    /** I haven't enabled the option to Prettify page urls with Pretty Link and
	*	Pretty Links Pro plugin doesn't automatically prettify custom post type urls.
	*	Access the full permalink instead.
	*	If modifying the use of Pretty Link as a url shortener and using the full 
	*	permalink on all post types, remove the conditional statement surrounding 
	*	the $prettylink variable below.
	*/
    if ( is_page() || is_singular( 'jetpack-portfolio' ) ) { 
        $prettylink		= urlencode( get_the_permalink() );
    }
	
	//* Total Share Count shortcode
        $sharecount     = do_shortcode('[fatpony_sharecount]');
	//* Get the permalink in case Pretty Link is unavailable
        $permalink      = urlencode( get_the_permalink() );
	//* Get the title so we can access it in the sharing links
        $title          = get_the_title();
	//* Get the excerpt so we can access it in the sharing links
        $excerpt        = strip_tags(get_the_excerpt());
	//* Get the category so we can use them as hashtags in tweets
        $cat            = get_the_category();
	//* Get the author's twitter id and add it to the tweet
        $twitter        = get_the_author_meta( 'twitter' );
	//* Get the home url to post as the source url in LinkedIn shares
        $homeurl        = urlencode( get_home_url() );
	//* Get the post's featured image so there's no guess work
        $imageurl       = urlencode( wp_get_attachment_url( get_post_thumbnail_id(), 'full' ) );
        $content = '';
        
	/** Define which post types we're outputting to. 
	*	If displaying on all pages and posts you'd use is_page() instead.
	*/
        if( is_single() || is_page('work') || is_singular( 'jetpack-portfolio' ) ) {
		
		//* Create our opening div
            $content .= '<div class="post-social">';
			
		//* Create our Total Share Count output
            $content .= '<div class="post-social-count">' . $sharecount . '<span class="shares">shares</span></div>';
		
		//* Wrap our buttons in another div to ensure proper styling with CSS Flexbox
            $content .= '<div class="post-social-buttons">';
			
		//* Opening div for CSS Flexbox row 1
            $content .= '<div class="post-social-flex">';
			
		//* Create our Twitter Button
            $content .= '<a href="//twitter.com/home?status=' . $title . '%20' . $prettylink . '%20@' . $twitter . '';
				
            /** Create our category hashtags - plan ahead when creating your Category slugs. 
			*	Could also modify to use the Tag slugs of your post, 
			*	depending on how you utilize Categories & Tags.
			*/
            if( has_category() ) {
                foreach( $cat as $category ) {
                    $content .= '%20%23' . $category->slug . '';
                }
            }
			/** Define default hashtag(s) if the post has no Category.
			*	Originally set when I planned to use Tag slugs instead of Categories. 
			*	May not be necessary.
			*/
            if( !has_category() ) {
                $content .= '%20%23wordpress%20%23webdesign';
            }
				
		//* Finish the Twitter button
            $output .= '" class="button twitter" target="_blank" onclick="window.open(this.href,this.target,\'width=500,height=400\');return false;"><i class="fa fa-twitter"> </i>Share on Twitter</a>';
			
		//* Create our Facebook Button
            $content .= '<a href="//www.facebook.com/share.php?u=' . $permalink . '&amp;title=" class="button facebook" target="_blank" onclick="window.open(this.href,this.target,\'width=500,height=400\');return false;"><i class="fa fa-facebook"> </i>Share on Facebook</a>';
			
		//* Closing div for CSS Flexbox row 1
            $content .= '</div>';
			
		//* Opening div for CSS Flexbox row 2
            $content .= '<div class="post-social-flex">';
			
		//* Create our Google+ Button
            $content .= '<a href="//plus.google.com/share?url=' . $permalink . '" class="button google-plus" target="_blank" onclick="window.open(this.href,this.target,\'width=500,height=400\');return false;"><i class="fa fa-google-plus"> </i>Google+</a>';
			
		//* Create our LinkedIn Button
            $content .= '<a href="//www.linkedin.com/shareArticle?mini=true&amp;url=' . $permalink . '&amp;title=' . $title . '&amp;summary=' . $excerpt . '&amp;source=' . $homeurl . '" class="button linkedin" target="_blank" onclick="window.open(this.href,this.target,\'width=500,height=400\');return false;"><i class="fa fa-linkedin"> </i>LinkedIn</a>';

		//* Create our Pinterest Button
            $content .= '<a href="//pinterest.com/pin/create/button/?url=' . $permalink . '&media=' . $imageurl . '&description=' . $excerpt . '" class="button pinterest" target="_blank" onclick="window.open(this.href,this.target,\'width=500,height=400\');return false;"><i class="fa fa-pinterest"> </i>Pinterest</a>';
			
		//* Closing div for CSS Flexbox row 2
            $content .= '</div>';
			
		//* Closing div for our CSS Flexbox wrapper
            $content .= '</div>';
			
		//* Closing div for our social sharing container
            $content .= '</div>';

		//* Now echo everything we've created
            echo $content;
        }
}