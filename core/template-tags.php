<?php

/**
 * The following functions are meant to be used directly in template files.
 * They always echo.
 */

/* = General Template Tags
-------------------------------------------------------------- */

/**
 * the_dir_categories_home
 *
 * @access public
 * @return void
 */
function the_dr_categories_home() {
	$args = array(
		'parent'       => 0,
		'orderby'      => 'name',
		'order'        => 'ASC',
		'hide_empty'   => 0,
		'hierarchical' => 1,
		'number'       => 10,
		'taxonomy'     => 'listing_category',
		'pad_counts'   => 1
	);

	$categories = get_categories( $args );

	$output = '<ul>';

	foreach( $categories as $category ) {
        $count_items = 0;

		$output .= '<li>';
		$output .= '<h2><a href="' . get_term_link( $category ) . '" title="' . sprintf( __( 'View all posts in %s', DIR_TEXT_DOMAIN ), $category->name ) . '" >' . $category->name . '</a> </h2>';

		$args = array(
			'parent'       => $category->term_id,
			'orderby'      => 'name',
			'order'        => 'ASC',
			'hide_empty'   => 0,
			'hierarchical' => 1,
			'number'       => 1,
			'taxonomy'     => 'listing_category',
			'pad_counts'   => 1
		);

		$sub_categories = get_categories( $args );

		foreach ( $sub_categories as $sub_category ) {
			$output .= '<a href="' . get_term_link( $sub_category ) . '" title="' . sprintf( __( 'View all posts in %s', DIR_TEXT_DOMAIN ), $sub_category->name ) . '" ' . '>' . $sub_category->name.'</a>(' . $sub_category->count . ')<br />';
            $count_items++;
		}


        if ( 4 > $count_items ) {
            $args = array(
                'numberposts'      => '4',
                'post_type'        => 'directory_listing',
                'listing_category'   => $category->slug
            );

            $my_posts = get_posts( $args );

            foreach( $my_posts as $post ) {
                $output .= '<a href="' . get_permalink( $post->ID ) . '" title="' . $post->post_title . '" ' . '>' . $post->post_title .'</a><br />';
                $count_items++;
                if ( 4 < $count_items ) break;
            }
        }

		$output .= '</li>';
	}

	$output .= '</ul>';

	echo $output;




}

/**
 * the_dir_categories_archive
 *
 * @access public
 * @return void
 */
function the_dr_categories_archive() {
	$args = array(
		'parent'       => get_queried_object_id(),
		'orderby'      => 'name',
		'order'        => 'ASC',
		'hide_empty'   => 0,
		'hierarchical' => 1,
		'number'       => 10,
		'taxonomy'     => 'listing_category',
		'pad_counts'   => 1
	);

	$categories = get_categories( $args );

	$i = 1;
	$output = '<table><tr><td>';

	foreach( $categories as $category ) {

		$output .= '<a href="' . get_term_link( $category ) . '" title="' . sprintf( __( 'View all posts in %s', DIR_TEXT_DOMAIN ), $category->name ) . '" >' . $category->name . '</a> (' . $category->count . ') <br />';

		if ( $i % 5 == 0 )
			$output .= '</td><td>';

		$i++;
	}

	$output .= '</td></tr></table>';

	echo $output;
}

/**
 * the_dir_breadcrumbs
 *
 * @access public
 * @return void
 */
function the_dr_breadcrumbs() {
	$category = get_queried_object();

	$category_parent_ids = get_ancestors( $category->term_id, $category->taxonomy );
	$category_parent_ids = array_reverse( $category_parent_ids );

	foreach ( $category_parent_ids as $category_parent_id ) {
		$category_parent = get_term( $category_parent_id, $category->taxonomy );

		$output .= '<a href="' . get_term_link( $category_parent ) . '" title="' . sprintf( __( 'View all posts in %s', DIR_TEXT_DOMAIN ), $category_parent->name ) . '" >' . $category_parent->name . '</a> / ';
	}

	$output .= '<a href="' . get_term_link( $category ) . '" title="' . sprintf( __( 'View all posts in %s', DIR_TEXT_DOMAIN ), $category->name ) . '" >' . $category->name . '</a>';

	echo $output;
}

/**
 * Prints HTML with meta information for the current post—date/time and author.
 *
 * @access public
 * @return void
 */
function the_dr_posted_on() {
	printf( __( '<span class="%1$s">Posted on</span> %2$s <span class="meta-sep">by</span> %3$s', 'directory' ),
		'meta-prep meta-prep-author',
		sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><span class="entry-date">%3$s</span></a>',
			get_permalink(),
			esc_attr( get_the_time() ),
			get_the_date()
		),
		sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s">%3$s</a></span>',
			get_author_posts_url( get_the_author_meta( 'ID' ) ),
			sprintf( esc_attr__( 'View all posts by %s', 'directory' ), get_the_author() ),
			get_the_author()
		)
	);
}

/**
 * Prints HTML with meta information for the current post (category, tags and permalink).
 *
 * @access public
 * @return void
 */
function the_dr_posted_in() {
    global $post;

    // Retrieves categories list of current post, separated by commas.
    $categories = wp_get_post_terms( $post->ID, "listing_category", "" );
    foreach ( $categories as $category )
        $categories_list[] = '<a href="' . get_term_link( $category ) . '" title="' . $category->name . '" >' . $category->name . '</a>';

    $categories_list = implode( ", ", ( array ) $categories_list );

    // Retrieves tag list of current post, separated by commas.
    $tags = wp_get_post_terms( $post->ID, "listing_tag", "" );
    foreach ( $tags as $tag )
        $tag_list[] = '<a href="' . get_term_link( $tag ) . '" title="' . $tag->name . '" >' . $tag->name . '</a>';

    $tag_list = implode( ", ", ( array ) $tag_list );


	if ( $tag_list ) {
		$posted_in = __( 'This entry was posted in %1$s and tagged %2$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'directory' );
	} elseif ( $categories_list ) {
		$posted_in = __( 'This entry was posted in %1$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'directory' );
	} else {
		$posted_in = __( 'Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'directory' );
	}


	// Prints the string, replacing the placeholders.
	printf(
		$posted_in,
		$categories_list,
		$tag_list,
		get_permalink(),
		the_title_attribute( 'echo=0' )
	);
}

/**
 * Template for comments and pingbacks.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 */
function the_dr_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;

	switch ( $comment->comment_type ) :
		case '' : ?>
			<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
				<div id="comment-<?php comment_ID(); ?>">

					<div class="comment-author vcard">
						<?php echo get_avatar( $comment, 40 ); ?>
						<?php printf( __( '%s <span class="says">says:</span>', 'directory' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
					</div><!-- .comment-author .vcard -->

					<?php if ( $comment->comment_approved == '0' ) : ?>
						<em><?php _e( 'Your review is awaiting moderation.', 'directory' ); ?></em>
						<br />
					<?php endif; ?>

					<div class="comment-meta commentmetadata"><a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
						<?php printf( __( '%1$s at %2$s', 'directory' ), get_comment_date(),  get_comment_time() ); ?></a><?php edit_comment_link( __( '(Edit)', 'directory' ), ' ' ); ?>
					</div><!-- .comment-meta .commentmetadata -->

					<?php do_action('sr_user_rating'); ?>

					<div class="comment-body"><?php comment_text(); ?></div>

					<div class="reply">
						<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
					</div><!-- .reply -->

				</div><!-- #comment-##  --> <?php

			break;

		case 'pingback'  :
		case 'trackback' : ?>
			<li class="post pingback">
				<p><?php _e( 'Pingback:', 'directory' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __('(Edit)', 'directory'), ' ' ); ?></p> <?php
			break;
	endswitch;
}