<?php
/**
 * @package WordPress
 * @subpackage Seattle Mennonite
 */

get_header(); 
global $more
?>

<div id="content">
    <?php 
    # THE WELCOME POST
    query_posts("category_name=Welcome&posts_per_page=1");
    if (have_posts()) : ?>
    	<?php while (have_posts()) : the_post(); 
            $more=1;
            ?>

    		<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
    			<h2 class="post-title"><a href="about/welcome-statement"><?php the_title(); ?></a></h2>
    			<?php the_content(); ?>
    		</div>

    	<?php endwhile; 
    	  if (next_posts_link() || previous_posts_link()): ?>
            <?php next_posts_link('&laquo; Older Entries') ?> | <?php previous_posts_link('Newer Entries &raquo;') ?>
    	<?php endif ?>
    <?php else : ?>
    	<h2 class="post-title"><a href="about/welcome-statement">Welcome to Seattle Mennonite Church</a></h2>
    <?php endif; 

    # FRONT PAGE CATEGORY POSTS
    # category 5=Sermons, 7=Current Events, 10=Front Page
    # (Alternatively, search by category_name, or lookup other IDs in the databases' wp_terms table)
    $myposts = get_posts("category_name='Front Page'&numberposts=3");
    
    foreach($myposts as $post) :
        setup_postdata($post);
        $more=0;
    ?>
    		<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
    			<h2 class="post-title"><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
    			<p class="post-byline">
    			    <?php the_time('F jS, Y') ?>
    			    &mdash; <?php the_author() ?>
    			</p>
                
    			<?php
                if(seattlemennonite_should_use_the_excerpt(get_the_excerpt(),get_the_content(),1200)) : 
                    the_excerpt(); # Post has a custom excerpt
                else :
                    the_content(); # ignores any custom excerpt (but succeeds in looking for "More" tag)
                endif; ?>
                <p class="post-postedin">
    			    <?php the_tags('Tags: ', ', ', '<br />'); ?>
    			    <?php if ( ! in_category('Uncategorized') ) : ?>
    			        Posted in <?php seattlemennonite_post_categories(10) ?>
    			    <?php endif; ?>
    			    <?php edit_post_link('Edit', '', ' | '); ?> 
    			</p>
    		</div>

    <?php endforeach; ?>
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
